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
}