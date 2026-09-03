<?php
/**
 * S5: streaming chat over Server-Sent Events.
 *
 * wp_remote_post() buffers the entire upstream body, so it cannot stream. For this
 * one call we drop to raw cURL with CURLOPT_WRITEFUNCTION and Anthropic's
 * "stream": true, re-emitting chunks to the browser as they arrive.
 *
 * SAFETY MODEL (this is the part that matters):
 * the customer must never see text the filter would have blocked, so we never emit a
 * chunk the moment it arrives. Text accumulates in a pending buffer and is released
 * only at a sentence boundary, and only after VA_Filter::apply() has been run on the
 * FULL cumulative answer including that segment. A hit at any point replaces the whole
 * visible message via a 'replace' event and aborts the upstream request. The judge
 * (which needs the complete text) runs at the end and can still issue a replace.
 * Exactly one log row is written, holding the final, filtered answer.
 *
 * Events emitted: delta | replace | done | error.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VA_Stream {

	/** Never release the last N characters until a boundary or end of stream. */
	const HOLD_TAIL = 60;

	/** If no sentence boundary appears within N characters, release anyway. */
	const MAX_HOLD = 240;

	/** Cumulative raw text released to the browser so far. */
	private static $emitted_raw = '';

	/** Text received but not yet released. */
	private static $pending = '';

	/** Set once a filter stage has fired; suppresses all further emission. */
	private static $blocked = null;

	/** Partial SSE line carried across cURL chunk boundaries. */
	private static $line_buf = '';

	private static $usage = array();

	/**
	 * Streaming is only possible with cURL present and the admin toggle on.
	 */
	public static function is_available() {
		return function_exists( 'curl_init' ) && (bool) get_option( 'va_streaming', 1 );
	}

	/**
	 * POST /chat/stream
	 *
	 * Returns a normal JSON 501 when streaming is unavailable, which tells widget.js
	 * to fall back to the buffered /chat endpoint. Otherwise it takes over output
	 * entirely and exits.
	 */
	public static function handle( WP_REST_Request $request ) {
		if ( ! self::is_available() ) {
			return new WP_REST_Response(
				array( 'stream' => false, 'reason' => 'streaming_unavailable' ),
				501
			);
		}

		// Every gate, limit and the history rebuild are shared with the buffered path.
		$prep = VA_REST::prepare_turn( $request );

		self::reset();
		self::open();

		// An early exit (rate limit, prescreen, kill switch, replay) already has its
		// final text and was already logged; emit it as one frame and stop.
		if ( isset( $prep['response'] ) ) {
			$data = $prep['response']->get_data();
			self::send( 'delta', array( 'text' => isset( $data['reply'] ) ? $data['reply'] : '' ) );
			self::send(
				'done',
				array(
					'filtered' => ! empty( $data['filtered'] ),
					'early'    => true,
				)
			);
			self::finish();
		}

		$ctx      = $prep['ctx'];
		$messages = $prep['messages'];

		$result = self::stream_model( $messages );

		if ( is_wp_error( $result ) ) {
			$etype    = $result->get_error_code();
			$fallback = "Sorry, I'm having trouble reaching the advisor right now. Please try again in a moment, or contact a Vac2Go rep at https://vac2go.com/contact/.";

			// Nothing safe was shown yet, so replace whatever partial text exists.
			self::send( 'replace', array( 'text' => $fallback ) );
			self::send(
				'done',
				array(
					'filtered' => false,
					'error'    => true,
					'code'     => 'VA-' . strtoupper( substr( md5( $etype ), 0, 6 ) ),
				)
			);

			VA_REST::log_turn( $ctx['session_id'], $ctx['request_id'], $ctx['message'], $fallback, null, 0, null, $etype, null, $ctx['ip_hash'], $request, $ctx['flags'] );

			if ( in_array( $etype, array( 'invalid_key', 'insufficient_credits', 'invalid_model' ), true ) ) {
				VA_RateLimit::alert( 'err_' . $etype, 'Vac2Go Advisor: API error (' . $etype . ')', 'The advisor cannot reach the model. Error type: ' . $etype );
			}
			self::finish();
		}

		$raw   = $result['raw'];
		$flags = $ctx['flags'];

		// Flush whatever is still held back through the SAME guarded path, so the
		// held-back tail is filter-checked exactly like every earlier segment.
		if ( null === self::$blocked ) {
			self::release( true );
		}

		if ( null !== self::$blocked ) {
			// A deterministic stage fired. The replace event has already been sent.
			$final           = self::$blocked['text'];
			$stage           = self::$blocked['stage'];
			$flags['reason'] = self::$blocked['reason'];
		} else {
			// Deterministic stages passed on the complete text; now the judge, which
			// needs the whole answer and so can only run here.
			$final = VA_Text::strip_em_dashes( VA_Text::normalize( $raw ) );
			$stage = null;

			$verdict = VA_Filter::judge( $final, VA_ANTHROPIC_KEY );
			if ( 'yes' === $verdict ) {
				$final           = VA_Filter::FALLBACK;
				$stage           = 'judge';
				$flags['reason'] = 'judge: commitment/pricing detected';
				self::send( 'replace', array( 'text' => $final ) );
			} elseif ( 'error' === $verdict ) {
				$flags['judge_error'] = 1;
			}
		}

		self::send( 'done', array( 'filtered' => null !== $stage ) );

		VA_REST::log_turn(
			$ctx['session_id'],
			$ctx['request_id'],
			$ctx['message'],
			$final,
			null !== $stage ? $raw : null,
			null !== $stage ? 1 : 0,
			$stage,
			null,
			self::$usage,
			$ctx['ip_hash'],
			$request,
			$flags
		);

		if ( 'canary' === $stage || 'structural' === $stage ) {
			VA_RateLimit::alert( 'canary', 'Vac2Go Advisor: prompt-leak filter fired', 'Stage: ' . $stage . '. Check the review queue.' );
		}

		self::finish();
	}

	// ---------------------------------------------------------------- transport --

	private static function reset() {
		self::$emitted_raw = '';
		self::$pending     = '';
		self::$blocked     = null;
		self::$line_buf    = '';
		self::$usage       = array();
	}

	/**
	 * Take over the response: SSE headers, every buffering layer we can reach turned
	 * off. X-Accel-Buffering covers nginx; LiteSpeed honours its own no-cache header.
	 */
	private static function open() {
		if ( ! headers_sent() ) {
			header( 'Content-Type: text/event-stream; charset=utf-8' );
			header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			header( 'Pragma: no-cache' );
			header( 'X-Accel-Buffering: no' );
			header( 'X-LiteSpeed-Cache-Control: no-cache' );
			header( 'Connection: keep-alive' );
		}
		while ( ob_get_level() > 0 ) {
			ob_end_flush();
		}
		// A 2KB comment pad defeats proxies that hold small responses.
		echo ': ' . str_repeat( ' ', 2048 ) . "\n\n";
		self::flush_out();
	}

	private static function send( $event, array $data ) {
		echo 'event: ' . $event . "\n";
		echo 'data: ' . wp_json_encode( $data ) . "\n\n";
		self::flush_out();
	}

	private static function flush_out() {
		if ( ob_get_level() > 0 ) {
			@ob_flush(); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
		flush();
	}

	private static function finish() {
		echo "event: close\ndata: {}\n\n";
		self::flush_out();
		exit;
	}

	// -------------------------------------------------------------- model call --

	/**
	 * @return array{raw:string}|WP_Error
	 */
	private static function stream_model( array $messages ) {
		$body = array(
			'model'      => VA_ADVISOR_MODEL,
			'max_tokens' => 1000,
			'system'     => VA_Knowledge::get_system_blocks(),
			'messages'   => $messages,
			'stream'     => true,
		);

		$raw         = '';
		$http_status = 0;
		$err_body    = '';

		$ch = curl_init( VA_ADVISOR_API_URL );
		curl_setopt_array(
			$ch,
			array(
				CURLOPT_POST           => true,
				CURLOPT_RETURNTRANSFER => false,
				CURLOPT_TIMEOUT        => VA_ADVISOR_API_TIMEOUT,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_HTTPHEADER     => array(
					'x-api-key: ' . VA_ANTHROPIC_KEY,
					'anthropic-version: 2023-06-01',
					'content-type: application/json',
					'accept: text/event-stream',
				),
				CURLOPT_POSTFIELDS     => wp_json_encode( $body ),
				CURLOPT_HEADERFUNCTION => function ( $c, $header ) use ( &$http_status ) {
					if ( preg_match( '#^HTTP/\S+\s+(\d{3})#', $header, $m ) ) {
						$http_status = (int) $m[1];
					}
					return strlen( $header );
				},
				CURLOPT_WRITEFUNCTION  => function ( $c, $chunk ) use ( &$raw, &$err_body, &$http_status ) {
					$len = strlen( $chunk );

					// Non-2xx: collect the JSON error body instead of parsing SSE.
					if ( $http_status && ( $http_status < 200 || $http_status >= 300 ) ) {
						$err_body .= $chunk;
						return $len;
					}

					if ( ! self::consume( $chunk, $raw ) ) {
						return 0; // aborts the transfer (filter fired)
					}
					return $len;
				},
			)
		);

		curl_exec( $ch );
		$curl_errno = curl_errno( $ch );
		// curl_close() is a deprecated no-op on PHP 8; the handle frees itself.

		// errno 23/42 are our deliberate abort from WRITEFUNCTION returning 0.
		$deliberate_abort = ( null !== self::$blocked ) && in_array( $curl_errno, array( 23, 42 ), true );

		if ( $curl_errno && ! $deliberate_abort ) {
			if ( CURLE_OPERATION_TIMEOUTED === $curl_errno ) {
				return new WP_Error( 'timeout', 'upstream timeout' );
			}
			return new WP_Error( 'network', 'network error' );
		}

		if ( $http_status && ( $http_status < 200 || $http_status >= 300 ) ) {
			return self::map_error( $http_status, $err_body );
		}

		if ( null === self::$blocked && '' === trim( $raw ) ) {
			return new WP_Error( 'upstream_empty', 'empty model response' );
		}

		return array( 'raw' => $raw );
	}

	/**
	 * Same taxonomy as the buffered path (S7).
	 */
	private static function map_error( $code, $body ) {
		$data     = json_decode( $body, true );
		$api_msg  = isset( $data['error']['message'] ) ? strtolower( (string) $data['error']['message'] ) : '';
		$api_type = isset( $data['error']['type'] ) ? strtolower( (string) $data['error']['type'] ) : '';

		if ( 401 === $code || 'authentication_error' === $api_type ) {
			return new WP_Error( 'invalid_key', 'auth error' );
		}
		if ( false !== strpos( $api_msg, 'credit balance' ) ) {
			return new WP_Error( 'insufficient_credits', 'credits exhausted' );
		}
		if ( 404 === $code || ( false !== strpos( $api_msg, 'model' ) && false !== strpos( $api_msg, 'not found' ) ) ) {
			return new WP_Error( 'invalid_model', 'model name rejected' );
		}
		if ( 429 === $code ) {
			return new WP_Error( 'upstream_429', 'rate limited upstream' );
		}
		if ( $code >= 500 ) {
			return new WP_Error( 'upstream_5xx', 'upstream server error' );
		}
		return new WP_Error( 'upstream_' . $code, 'upstream error' );
	}

	// ------------------------------------------------------------ SSE consumer --

	/**
	 * Parse one cURL chunk of Anthropic SSE into text deltas and usage.
	 *
	 * @return bool False to abort the upstream transfer.
	 */
	private static function consume( $chunk, &$raw ) {
		self::$line_buf .= $chunk;

		while ( false !== ( $pos = strpos( self::$line_buf, "\n" ) ) ) {
			$line           = rtrim( substr( self::$line_buf, 0, $pos ), "\r" );
			self::$line_buf = substr( self::$line_buf, $pos + 1 );

			if ( 0 !== strpos( $line, 'data:' ) ) {
				continue;
			}
			$payload = trim( substr( $line, 5 ) );
			if ( '' === $payload || '[DONE]' === $payload ) {
				continue;
			}
			$evt = json_decode( $payload, true );
			if ( ! is_array( $evt ) || ! isset( $evt['type'] ) ) {
				continue;
			}

			switch ( $evt['type'] ) {
				case 'message_start':
					$u = isset( $evt['message']['usage'] ) ? $evt['message']['usage'] : array();
					self::$usage['input_tokens']                = isset( $u['input_tokens'] ) ? (int) $u['input_tokens'] : null;
					self::$usage['cache_creation_input_tokens'] = isset( $u['cache_creation_input_tokens'] ) ? (int) $u['cache_creation_input_tokens'] : null;
					self::$usage['cache_read_input_tokens']     = isset( $u['cache_read_input_tokens'] ) ? (int) $u['cache_read_input_tokens'] : null;
					break;

				case 'content_block_delta':
					if ( isset( $evt['delta']['text'] ) ) {
						$raw            .= $evt['delta']['text'];
						self::$pending .= $evt['delta']['text'];
						if ( ! self::release( false ) ) {
							return false;
						}
					}
					break;

				case 'message_delta':
					if ( isset( $evt['usage']['output_tokens'] ) ) {
						self::$usage['output_tokens'] = (int) $evt['usage']['output_tokens'];
					}
					break;

				case 'error':
					return true; // surfaced by the status/body path
			}
		}

		return true;
	}

	// --------------------------------------------------------- filtered output --

	/**
	 * Release the safe portion of the pending buffer, but only after the deterministic
	 * filter stages have passed on the whole answer so far.
	 *
	 * @return bool False when a stage fired (caller must abort).
	 */
	private static function release( $force ) {
		if ( null !== self::$blocked ) {
			return false;
		}

		$cut = self::cut_point( $force );
		if ( $cut <= 0 ) {
			return true;
		}

		$segment       = substr( self::$pending, 0, $cut );
		$candidate_raw = self::$emitted_raw . $segment;

		$check = VA_Filter::apply( $candidate_raw );
		if ( $check['filtered'] ) {
			self::$blocked = array(
				'text'   => $check['text'],
				'stage'  => $check['stage'],
				'reason' => $check['reason'],
			);
			self::send( 'replace', array( 'text' => $check['text'] ) );
			return false;
		}

		self::$pending     = substr( self::$pending, $cut );
		self::$emitted_raw = $candidate_raw;

		$visible = VA_Text::strip_em_dashes( VA_Text::normalize( $segment ) );
		if ( '' !== $visible ) {
			self::send( 'delta', array( 'text' => $visible ) );
		}
		return true;
	}

	/**
	 * How many bytes of the pending buffer are safe to release: everything up to the
	 * last sentence boundary, always keeping HOLD_TAIL characters back so a pattern
	 * that straddles the boundary is still caught before the customer sees it.
	 */
	private static function cut_point( $force ) {
		$len = strlen( self::$pending );

		if ( $force ) {
			return $len;
		}
		if ( $len <= self::HOLD_TAIL ) {
			return 0;
		}

		$searchable = substr( self::$pending, 0, $len - self::HOLD_TAIL );

		if ( preg_match_all( '/[.!?\n](?=\s|$)/', $searchable, $m, PREG_OFFSET_CAPTURE ) ) {
			$last = end( $m[0] );
			return $last[1] + 1;
		}

		// No boundary in a long run: release at the last space so words stay intact.
		if ( $len > self::MAX_HOLD ) {
			$sp = strrpos( $searchable, ' ' );
			if ( false !== $sp ) {
				return $sp + 1;
			}
		}

		return 0;
	}
}
