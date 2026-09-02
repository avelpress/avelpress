<?php

namespace AvelPress\Update;

use AvelPress\Update\Contracts\UpdateProvider;

defined( 'ABSPATH' ) || exit;

/**
 * Reads the current release from an HTTP endpoint.
 *
 * Covers both shapes in use: a public GET that answers by slug, and a POST that
 * only answers once it has seen a valid licence. The difference is the `auth`
 * callback — when there is one, its data is sent as the request body and the
 * method becomes POST.
 *
 * @since 1.3.0
 */
class HttpUpdateProvider implements UpdateProvider {

	/**
	 * Endpoint URL. May contain {slug}.
	 *
	 * @var string
	 */
	protected $endpoint;

	/**
	 * Returns the credentials to send, when the endpoint requires them.
	 *
	 * @var callable|null
	 */
	protected $auth;

	/**
	 * Extra request headers.
	 *
	 * @var array
	 */
	protected $headers;

	/**
	 * Request timeout in seconds.
	 *
	 * @var int
	 */
	protected $timeout;

	/**
	 * @param string $endpoint Endpoint URL, optionally containing {slug}.
	 * @param array  $options  auth (callable), headers (array), timeout (int).
	 */
	public function __construct( $endpoint, $options = [] ) {
		$this->endpoint = $endpoint;
		$this->auth = isset( $options['auth'] ) && is_callable( $options['auth'] ) ? $options['auth'] : null;
		$this->headers = isset( $options['headers'] ) ? (array) $options['headers'] : [];
		$this->timeout = isset( $options['timeout'] ) ? (int) $options['timeout'] : 15;
	}

	/**
	 * Asks the endpoint for the current release.
	 *
	 * @param string $slug Plugin slug.
	 * @return array|\WP_Error
	 */
	public function fetch( $slug ) {
		$url = str_replace( '{slug}', rawurlencode( $slug ), $this->endpoint );

		$args = [ 
			'timeout' => $this->timeout,
			'headers' => array_merge( [ 'Referer' => home_url() ], $this->headers ),
		];

		if ( $this->auth ) {
			$body = array_merge( [ 'plugin_slug' => $slug ], (array) call_user_func( $this->auth ) );

			$args['headers']['Content-Type'] = 'application/json';
			$args['body'] = wp_json_encode( $body );

			$response = wp_remote_post( $url, $args );
		} else {
			$response = wp_remote_get( $url, $args );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $code || ! is_array( $data ) ) {
			$message = isset( $data['message'] ) ? $data['message'] : 'Could not check for updates.';

			return new \WP_Error( 'avelpress_update_failed', $message, [ 'status' => $code ] );
		}

		return $data;
	}
}
