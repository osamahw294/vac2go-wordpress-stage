<?php
/**
 * REST routes: GET /nonce, POST /chat, POST /contact, POST /correction.
 * Namespace: vac2go/v1
 *
 * Trust model: the browser supplies only session_id, request_id, the new message,
 * and bot-friction fields. Conversation history is rebuilt server-side from the log
 * table; any client-supplied history is ignored and flagged.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VA_REST {

	const NS               = 'vac2go/v1';
	const MAX_MESSAGE_CH   = 2000;
	const MAX_MESSAGE_B    = 8000;
	const HISTORY_TURNS    = 10;
	const HISTORY_CHARS    = 12000;
	const IDEMPOTENCY_SECS = 30;
	const UUID_V4          = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

	public static function is_configured() {
		return defined( 'VA_ANTHROPIC_KEY' )
			&& '' !== trim( (string) VA_ANTHROPIC_KEY )
			&& 'REPLACE_ME' !== VA_ANTHROPIC_KEY;
	}

	public static function is_enabled() {
		return (bool) get_option( 'va_enabled', 1 );
	}

	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/nonce',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'handle_nonce' ),
				'permission_callback' => '__return_true', // public by design; returns only a CSRF nonce
			)
		);

		register_rest_route(
			self::NS,
			'/chat',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_chat' ),
				'permission_callback' => array( __CLASS__, 'public_permission' ),
			)
		);

		register_rest_route(
			self::NS,
			'/contact',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_contact' ),
				'permission_callback' => array( __CLASS__, 'public_permission' ),
			)
		);

		register_rest_route(
			self::NS,
			'/correction',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_correction' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * The nonce is CSRF protection only; abuse is limited by the rate layers.
	 */
	public static function public_permission( WP_REST_Request $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error( 'va_bad_nonce', 'Invalid or missing security token.', array( 'status' => 403 ) );
		}
		return true;
	}

	private static function nocache( WP_REST_Response $response ) {
		$response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0' );
		$response->header( 'Pragma', 'no-cache' );
		$response->header( 'X-LiteSpeed-Cache-Control', 'no-cache' );
		return $response;
	}

	/**
	 * GET /nonce: a fresh REST nonce, never cached. Fixes the stale-nonce bug caused
	 * by the 7-day LiteSpeed page cache baking render-time nonces into cached HTML.
	 */
	public static function handle_nonce() {
		return self::nocache(
			new WP_REST_Response( array( 'nonce' => wp_create_nonce( 'wp_rest' ) ), 200 )
		);
	}

	/**
	 * POST /chat
	 */
	public static function handle_chat( WP_REST_Request $request ) {
		$params     = $request->get_json_params();
		$session_id = isset( $params['session_id'] ) ? sanitize_text_field( $params['session_id'] ) : '';
		$request_id = isset( $params['request_id'] ) ? sanitize_text_field( $params['request_id'] ) : '';
		$message    = isset( $params['message'] ) ? (string) $params['message'] : '';
		$honeypot   = isset( $params['website'] ) ? (string) $params['website'] : '';
		$elapsed_ms = isset( $params['elapsed_ms'] ) ? (int) $params['elapsed_ms'] : -1;

		// --- Validation (S1c) ---
		if ( ! preg_match( self::UUID_V4, $session_id ) ) {
			return self::error_response( 'Invalid session.', 400 );
		}
		if ( '' !== $request_id && ! preg_match( self::UUID_V4, $request_id ) ) {
			return self::error_response( 'Invalid request id.', 400 );
		}
		$message = VA_Text::normalize( sanitize_textarea_field( $message ) );
		if ( '' === trim( $message ) ) {
			return self::error_response( 'Empty message.', 400 );
		}
		if ( mb_strlen( $message ) > self::MAX_MESSAGE_CH || strlen( $message ) > self::MAX_MESSAGE_B ) {
			return self::error_response( 'Message too long.', 400 );
		}

		// Client-supplied history is ignored entirely; note when one was sent.
		$client_history_ignored = ( isset( $params['history'] ) && is_array( $params['history'] ) && count( $params['history'] ) > 0 ) ? 1 : 0;

		$ip_hash = VA_RateLimit::ip_hash();
		$flags   = array( 'client_history_ignored' => $client_history_ignored );

		// --- Kill switch / configuration ---
		if ( ! self::is_enabled() ) {
			return self::graceful_unavailable( $session_id, $request_id, $message, 'disabled', $ip_hash, $request, $flags );
		}
		if ( ! self::is_configured() ) {
			return self::graceful_unavailable( $session_id, $request_id, $message, 'no_key', $ip_hash, $request, $flags );
		}

		// --- Idempotency (S1b): same request replayed within the window returns the
		// stored answer without a new model call or a new row. ---
		$dup = VA_DB::find_recent_request( $session_id, $request_id, self::IDEMPOTENCY_SECS );
		if ( $dup ) {
			return self::nocache(
				new WP_REST_Response(
					array(
						'reply'    => $dup['answer'],
						'filtered' => (bool) $dup['was_filtered'],
						'replayed' => true,
					),
					200
				)
			);
		}

		$is_first_turn = ! VA_DB::session_exists( $session_id );

		// --- Bot friction on the very first message only (S6.6) ---
		if ( $is_first_turn ) {
			if ( '' !== $honeypot || ( $elapsed_ms >= 0 && $elapsed_ms < 2000 ) ) {
				self::log_turn( $session_id, $request_id, $message, self::soft_wait_message(), null, 0, null, 'bot_friction', null, $ip_hash, $request, $flags );
				return self::nocache(
					new WP_REST_Response(
						array( 'reply' => self::soft_wait_message(), 'filtered' => false ),
						200
					)
				);
			}
		}

		// --- Rate layers (S6) ---
		if ( 'over' === VA_RateLimit::daily_budget_state() ) {
			return self::graceful_unavailable( $session_id, $request_id, $message, 'daily_ceiling', $ip_hash, $request, $flags );
		}
		if ( ! VA_RateLimit::check_global() ) {
			return self::graceful_unavailable( $session_id, $request_id, $message, 'breaker', $ip_hash, $request, $flags );
		}
		if ( ! VA_RateLimit::check_session( $session_id ) ) {
			return self::limited_response( "We've reached the length limit for this conversation. For anything further, please reach a Vac2Go rep at https://vac2go.com/contact/." );
		}
		if ( ! VA_RateLimit::check_ip( $ip_hash ) ) {
			return self::limited_response( "You've sent a lot of messages in a short time. Please pause a moment, or reach a Vac2Go rep directly at https://vac2go.com/contact/." );
		}

		// --- Pre-screen before Fable (S6.5) ---
		$scripted = self::prescreen( $message );
		if ( null !== $scripted ) {
			self::log_turn( $session_id, $request_id, $message, $scripted['reply'], null, 0, 'prescreen', null, null, $ip_hash, $request, $flags );
			return self::nocache(
				new WP_REST_Response( array( 'reply' => $scripted['reply'], 'filtered' => false ), 200 )
			);
		}

		// --- Server-side conversation (S1a): rebuilt from the log, never the client. ---
		$history  = VA_DB::get_history( $session_id, self::HISTORY_TURNS, self::HISTORY_CHARS );
		$messages = $history['messages'];
		$messages[] = array( 'role' => 'user', 'content' => $message );
		if ( $history['truncated'] ) {
			$flags['history_truncated'] = 1;
		}

		// Temporary S1a verification hook: logs the exact messages array. Enabled only
		// when the VA_DEBUG_MSGS constant is defined; removed after verification.
		if ( defined( 'VA_DEBUG_MSGS' ) && VA_DEBUG_MSGS ) {
			error_log( '[va-debug-messages] ' . wp_json_encode( $messages ) );
		}

		// --- Model call with retry + error taxonomy (S7) ---
		$api = self::call_anthropic_with_retry( $messages );

		if ( is_wp_error( $api ) ) {
			$etype    = $api->get_error_code();
			$fallback = "Sorry, I'm having trouble reaching the advisor right now. Please try again in a moment, or contact a Vac2Go rep at https://vac2go.com/contact/.";
			self::log_turn( $session_id, $request_id, $message, $fallback, null, 0, null, $etype, null, $ip_hash, $request, $flags );
			// Alert on auth/credit/model errors so the admin can tell these apart.
			if ( in_array( $etype, array( 'invalid_key', 'insufficient_credits', 'invalid_model' ), true ) ) {
				VA_RateLimit::alert( 'err_' . $etype, 'Vac2Go Advisor: API error (' . $etype . ')', 'The advisor cannot reach the model. Error type: ' . $etype );
			}
			return self::nocache(
				new WP_REST_Response(
					array( 'reply' => $fallback, 'filtered' => false, 'error' => true, 'code' => 'VA-' . strtoupper( substr( md5( $etype ), 0, 6 ) ) ),
					200
				)
			);
		}

		$raw   = $api['text'];
		$usage = $api['usage'];

		// --- Output pipeline (S4): deterministic stages... ---
		$filtered = VA_Filter::apply( $raw );
		$stage    = $filtered['stage'];

		// ...then the cheap judge, only when the deterministic stages passed.
		if ( ! $filtered['filtered'] ) {
			$verdict = VA_Filter::judge( $filtered['text'], VA_ANTHROPIC_KEY );
			if ( 'yes' === $verdict ) {
				$filtered = array(
					'text'     => VA_Filter::FALLBACK,
					'filtered' => true,
					'stage'    => 'judge',
					'reason'   => 'judge: commitment/pricing detected',
					'raw'      => $raw,
				);
				$stage = 'judge';
			} elseif ( 'error' === $verdict ) {
				$flags['judge_error'] = 1;
			}
		}

		$reply = $filtered['text'];

		self::log_turn(
			$session_id,
			$request_id,
			$message,
			$reply,
			$filtered['filtered'] ? $filtered['raw'] : null,
			$filtered['filtered'] ? 1 : 0,
			$stage,
			null,
			$usage,
			$ip_hash,
			$request,
			$flags
		);

		// Canary hit is a security event worth an immediate alert.
		if ( 'canary' === $stage || 'structural' === $stage ) {
			VA_RateLimit::alert( 'canary', 'Vac2Go Advisor: prompt-leak filter fired', 'Stage: ' . $stage . '. Check the review queue.' );
		}

		return self::nocache(
			new WP_REST_Response(
				array( 'reply' => $reply, 'filtered' => (bool) $filtered['filtered'] ),
				200
			)
		);
	}

	/**
	 * Pre-screen: scripted replies for greetings/tests, and an off-topic decline for
	 * clearly irrelevant asks, without spending a Fable call. Keyword-relevant messages
	 * skip straight to the model; ambiguous ones get a cheap classifier; classifier
	 * errors fail open to the model so real customers are never blocked.
	 *
	 * @return array{reply:string}|null Null means proceed to the model.
	 */
	private static function prescreen( $message ) {
		$m = mb_strtolower( trim( $message ) );

		// Greetings / test messages: short scripted reply.
		if ( preg_match( '/^(hi|hii+|hello|hey|yo|sup|test|testing|ping|ok|okay|thanks|thank you|good (morning|afternoon|evening))[.!?\s]*$/i', $m ) ) {
			return array(
				'reply' => "Hi! Tell me about your job (what you're vacuuming, cleaning, or excavating, roughly how much, and the site conditions) and I'll point you to the right truck category.",
			);
		}

		// Domain keyword allowlist: clearly relevant, go to the model.
		$keywords = array(
			'truck', 'vac', 'pump', 'sludge', 'excavat', 'hydro', 'sewer', 'jet', 'tank',
			'debris', 'cfm', 'hg', 'filter', 'bag', 'rent', 'hv-57', 'hv57', 'hv 57',
			'gapvax', 'guzzler', 'huber', 'vactor', 'dig', 'pit', 'spill', 'clean',
			'material', 'cubic', 'yard', 'water', 'liquid', 'dry', 'wet', 'site', 'job',
			'equipment', 'unit', 'catch basin', 'daylight', 'pothole', 'restroom',
			'septic', 'grease', 'sand', 'slurry', 'ash', 'rail', 'chassis', 'cab',
			'trailer', 'roll off', 'roll-off', 'tractor', 'combo', 'lagoon', 'pond',
			'grain', 'cement', 'lime', 'coal', 'gravel', 'mud', 'soil', 'trench',
			'utility', 'pipe', 'drain', 'culvert', 'manhole', 'cdl', 'price', 'cost',
			'quote', 'avail', 'deliver', 'insur', 'lease', 'oper',
		);
		foreach ( $keywords as $kw ) {
			if ( false !== mb_strpos( $m, $kw ) ) {
				return null; // relevant: proceed to the model
			}
		}

		// Ambiguous: cheap classifier decides.
		if ( self::is_configured() ) {
			$verdict = self::classify_relevance( $message );
			if ( 'no' === $verdict ) {
				return array(
					'reply' => "That's outside what I'm set up for. I help with vacuum and hydro-excavation truck questions for Vac2Go. If you've got a job in mind, tell me what you're working with. For anything else, the team at https://vac2go.com/contact/ can help.",
				);
			}
		}
		return null; // relevant, unknown, or classifier error: fail open to the model
	}

	private static function classify_relevance( $message ) {
		$body = array(
			'model'       => VA_ADVISOR_JUDGE_MODEL,
			'max_tokens'  => 5,
			'temperature' => 0,
			'system'      => 'Answer with exactly one word: yes or no.',
			'messages'    => array(
				array(
					'role'    => 'user',
					'content' => "Is the following message plausibly about vacuum trucks, hydro-excavation, industrial cleanup jobs, equipment rental, or Vac2Go's fleet? yes or no only.\n\n---\n" . $message,
				),
			),
		);
		$response = wp_remote_post(
			VA_ADVISOR_API_URL,
			array(
				'timeout' => 10,
				'headers' => array(
					'x-api-key'         => VA_ANTHROPIC_KEY,
					'anthropic-version' => '2023-06-01',
					'content-type'      => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return 'error';
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		$out  = isset( $data['content'][0]['text'] ) ? strtolower( trim( $data['content'][0]['text'] ) ) : '';
		if ( 0 === strpos( $out, 'no' ) ) {
			return 'no';
		}
		if ( 0 === strpos( $out, 'yes' ) ) {
			return 'yes';
		}
		return 'error';
	}

	/**
	 * Anthropic call with selective retry (S7): one retry with backoff for timeout,
	 * 5xx, and 429 only. Auth, credit, and model-name errors never retry.
	 *
	 * @return array{text:string, usage:array}|WP_Error error code = error_type.
	 */
	private static function call_anthropic_with_retry( array $messages ) {
		$attempt = self::call_anthropic( $messages );
		if ( is_wp_error( $attempt ) && in_array( $attempt->get_error_code(), array( 'timeout', 'upstream_5xx', 'upstream_429' ), true ) ) {
			sleep( 2 );
			$attempt = self::call_anthropic( $messages );
		}
		return $attempt;
	}

	private static function call_anthropic( array $messages ) {
		$body = array(
			'model'      => VA_ADVISOR_MODEL,
			'max_tokens' => 1000,
			'system'     => VA_Knowledge::get_system_blocks(), // static block w/ cache_control
			'messages'   => $messages,
		);

		$response = wp_remote_post(
			VA_ADVISOR_API_URL,
			array(
				'timeout' => VA_ADVISOR_API_TIMEOUT,
				'headers' => array(
					'x-api-key'         => VA_ANTHROPIC_KEY,
					'anthropic-version' => '2023-06-01',
					'content-type'      => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( is_wp_error( $response ) ) {
			// Never log the request array (it carries the API key header); the
			// WP_Error from the transport contains no request data.
			$msg = $response->get_error_message();
			if ( false !== stripos( $msg, 'timed out' ) || false !== stripos( $msg, 'timeout' ) ) {
				return new WP_Error( 'timeout', 'upstream timeout' );
			}
			return new WP_Error( 'network', 'network error' );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code < 200 || $code >= 300 ) {
			$api_msg  = isset( $data['error']['message'] ) ? strtolower( (string) $data['error']['message'] ) : '';
			$api_type = isset( $data['error']['type'] ) ? strtolower( (string) $data['error']['type'] ) : '';
			if ( 401 === $code || 'authentication_error' === $api_type ) {
				return new WP_Error( 'invalid_key', 'auth error' );
			}
			if ( false !== strpos( $api_msg, 'credit balance' ) ) {
				return new WP_Error( 'insufficient_credits', 'credits exhausted' );
			}
			if ( 404 === $code || false !== strpos( $api_msg, 'model' ) && false !== strpos( $api_msg, 'not found' ) ) {
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

		$text = '';
		if ( is_array( $data ) && ! empty( $data['content'] ) && is_array( $data['content'] ) ) {
			foreach ( $data['content'] as $block ) {
				if ( isset( $block['type'], $block['text'] ) && 'text' === $block['type'] ) {
					$text .= $block['text'];
				}
			}
		}
		if ( '' === trim( $text ) ) {
			return new WP_Error( 'upstream_empty', 'empty model response' );
		}

		$usage = isset( $data['usage'] ) && is_array( $data['usage'] ) ? $data['usage'] : array();

		return array(
			'text'  => trim( $text ),
			'usage' => array(
				'input_tokens'                => isset( $usage['input_tokens'] ) ? (int) $usage['input_tokens'] : null,
				'output_tokens'               => isset( $usage['output_tokens'] ) ? (int) $usage['output_tokens'] : null,
				'cache_creation_input_tokens' => isset( $usage['cache_creation_input_tokens'] ) ? (int) $usage['cache_creation_input_tokens'] : null,
				'cache_read_input_tokens'     => isset( $usage['cache_read_input_tokens'] ) ? (int) $usage['cache_read_input_tokens'] : null,
			),
		);
	}

	private static function soft_wait_message() {
		return "One moment. Please give it a couple of seconds and send your message again, or reach a Vac2Go rep at https://vac2go.com/contact/.";
	}

	/**
	 * Insert a log row; never let a logging failure break the chat.
	 */
	private static function log_turn( $session_id, $request_id, $question, $answer, $raw_answer, $was_filtered, $stage, $error_type, $usage, $ip_hash, WP_REST_Request $request, array $flags = array() ) {
		$ua = $request->get_header( 'User-Agent' );
		$ua = $ua ? substr( sanitize_text_field( $ua ), 0, 255 ) : null;

		$usage = is_array( $usage ) ? $usage : array();

		$id = VA_DB::insert_turn(
			array(
				'session_id'       => $session_id,
				'request_id'       => $request_id ? $request_id : null,
				'turn_index'       => VA_DB::next_turn_index( $session_id ),
				'question'         => $question,
				'answer'           => $answer,
				'raw_model_answer' => $raw_answer,
				'was_filtered'     => $was_filtered,
				'filter_stage'     => $stage,
				'filter_reason'    => $stage ? substr( (string) ( $flags['reason'] ?? $stage ), 0, 255 ) : null,
				'error_type'       => $error_type,
				'input_tokens'     => $usage['input_tokens'] ?? null,
				'output_tokens'    => $usage['output_tokens'] ?? null,
				'cache_creation_input_tokens' => $usage['cache_creation_input_tokens'] ?? null,
				'cache_read_input_tokens'     => $usage['cache_read_input_tokens'] ?? null,
				'client_history_ignored'      => (int) ( $flags['client_history_ignored'] ?? 0 ),
				'history_truncated'           => (int) ( $flags['history_truncated'] ?? 0 ),
				'ip_hash'          => $ip_hash,
				'user_agent'       => $ua,
			)
		);

		if ( false === $id ) {
			error_log( '[vac2go-ai-advisor] Failed to log advisor turn for session ' . $session_id );
		}
		return $id;
	}

	private static function graceful_unavailable( $session_id, $request_id, $message, $reason, $ip_hash, WP_REST_Request $request, array $flags = array() ) {
		$reply = 'The equipment advisor is temporarily unavailable. In the meantime, a Vac2Go rep can help directly at https://vac2go.com/contact/.';
		self::log_turn( $session_id, $request_id, $message, $reply, null, 0, null, $reason, null, $ip_hash, $request, $flags );
		return self::nocache(
			new WP_REST_Response(
				array( 'reply' => $reply, 'filtered' => false, 'unavailable' => true ),
				503
			)
		);
	}

	private static function limited_response( $text ) {
		return self::nocache(
			new WP_REST_Response(
				array( 'reply' => $text, 'filtered' => false, 'limited' => true ),
				200
			)
		);
	}

	private static function error_response( $message, $status ) {
		return self::nocache(
			new WP_REST_Response( array( 'error' => true, 'message' => $message ), $status )
		);
	}

	/**
	 * POST /contact: attach contact details to every row of the session.
	 */
	public static function handle_contact( WP_REST_Request $request ) {
		$params     = $request->get_json_params();
		$session_id = isset( $params['session_id'] ) ? sanitize_text_field( $params['session_id'] ) : '';
		$name       = isset( $params['name'] ) ? sanitize_text_field( VA_Text::normalize( $params['name'] ) ) : '';
		$email      = isset( $params['email'] ) ? sanitize_email( $params['email'] ) : '';
		$phone      = isset( $params['phone'] ) ? sanitize_text_field( $params['phone'] ) : '';

		if ( ! preg_match( self::UUID_V4, $session_id ) ) {
			return self::error_response( 'Invalid session.', 400 );
		}
		// A session with no logged rows has nothing to attach to; rejecting also
		// blocks blind contact-spraying against arbitrary session ids.
		if ( ! VA_DB::session_exists( $session_id ) ) {
			return self::error_response( 'Unknown session.', 400 );
		}
		if ( '' !== $email && ! is_email( $email ) ) {
			return self::error_response( 'Invalid email.', 400 );
		}

		$name  = mb_substr( $name, 0, 190 );
		$email = mb_substr( $email, 0, 190 );
		$phone = mb_substr( $phone, 0, 60 );

		VA_DB::update_contact( $session_id, $name, $email, $phone );

		return self::nocache( new WP_REST_Response( array( 'ok' => true ), 200 ) );
	}

	/**
	 * POST /correction: admin marks a logged answer incorrect and writes the correction.
	 */
	public static function handle_correction( WP_REST_Request $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return self::error_response( 'Invalid security token.', 403 );
		}

		$params          = $request->get_json_params();
		$log_id          = isset( $params['log_id'] ) ? absint( $params['log_id'] ) : 0;
		$correction_text = isset( $params['correction_text'] ) ? sanitize_textarea_field( $params['correction_text'] ) : '';

		if ( ! $log_id ) {
			return self::error_response( 'Missing log id.', 400 );
		}

		$ok = VA_DB::save_correction( $log_id, $correction_text, get_current_user_id() );

		return self::nocache(
			new WP_REST_Response(
				array(
					'ok'           => false !== $ok,
					'corrected_by' => get_current_user_id(),
					'corrected_at' => current_time( 'mysql' ),
				),
				200
			)
		);
	}
}
