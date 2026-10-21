<?php
/**
 * Tools the answer model may call. One today: find_nearest_branch (change request
 * of 2026-10-20, Change 1). The model passes the visitor's location as written; the
 * server resolves it offline (VA_Geo) and returns the branch to name.
 *
 * Definitions are byte-stable: they sit at the front of the cached prompt prefix.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VA_Tools {

	/** Most tool rounds in one answer; a second lookup covers "and what about X?". */
	const MAX_ROUNDS = 3;

	public static function definitions() {
		return array(
			array(
				'name'         => 'find_nearest_branch',
				'description'  => 'Find the Vac2Go branch closest to a place in the US, by distance. Call it whenever the customer gives a location (a ZIP code, a city, "city, state", a state, or a nickname like "DFW") and asks about a branch, whether Vac2Go serves their area, or who to call. Pass the location exactly as the customer wrote it. Never choose a branch yourself from a location.',
				'input_schema' => array(
					'type'                 => 'object',
					'properties'           => array(
						'location' => array(
							'type'        => 'string',
							'description' => 'The location as the customer wrote it, e.g. "Nashville, TN", "74103", "Houston", "Georgia".',
						),
					),
					'required'             => array( 'location' ),
					'additionalProperties' => false,
				),
			),
		);
	}

	/**
	 * Run one tool call.
	 *
	 * @return array{content:string,is_error?:bool,log:array}
	 */
	public static function run( $name, $input ) {
		if ( 'find_nearest_branch' !== $name ) {
			return array( 'content' => 'Unknown tool.', 'is_error' => true, 'log' => array() );
		}
		$location = is_array( $input ) && isset( $input['location'] ) ? substr( (string) $input['location'], 0, 190 ) : '';
		$result   = VA_Geo::nearest( $location );
		return array(
			'content' => json_encode( VA_Geo::for_model( $result ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
			'log'     => array(
				'location_query'  => $location,
				'location_result' => 'found' === $result['status'] ? $result['branch']['id'] : $result['status'],
			),
		);
	}

	/**
	 * The tool_result blocks for every tool_use block in a model turn, plus what to
	 * log. Shared by the buffered and streaming paths.
	 *
	 * @param array $content The assistant turn's content blocks.
	 * @return array{results:array,log:array}
	 */
	public static function results_for( array $content ) {
		$results = array();
		$log     = array();
		foreach ( $content as $block ) {
			if ( ! isset( $block['type'] ) || 'tool_use' !== $block['type'] ) {
				continue;
			}
			$run       = self::run( $block['name'] ?? '', $block['input'] ?? array() );
			$results[] = array_filter(
				array(
					'type'        => 'tool_result',
					'tool_use_id' => $block['id'],
					'content'     => $run['content'],
					'is_error'    => ! empty( $run['is_error'] ) ? true : null,
				),
				function ( $v ) {
					return null !== $v;
				}
			);
			if ( $run['log'] ) {
				$log = $run['log']; // the last lookup of the turn
			}
		}
		return array( 'results' => $results, 'log' => $log );
	}
}
