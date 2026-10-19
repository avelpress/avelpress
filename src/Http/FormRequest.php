<?php

namespace AvelPress\Http;

use AvelPress\Support\Validator;


defined( 'ABSPATH' ) || exit;

class FormRequest extends Validator {
	/**
	 * Validator constructor
	 * 
	 * @param \WP_REST_Request $request
	 */
	public function __construct( $request ) {
		$this->data = $request->get_params();
	}

	public function isMethod( $method ) {
		$current = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';

		return strtolower( $method ) === strtolower( $current );
	}
}