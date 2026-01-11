<?php

namespace AvelPress\Database\Eloquent\Casts;

use AvelPress\Contracts\Database\Eloquent\CastsAttributes;

class ArrayCast implements CastsAttributes {

	public function get( $model, string $key, $value, array $attributes ) {
		return json_decode( $value, true );
	}

	public function set( $model, string $key, $value, array $attributes ) {
		return wp_json_encode( $value );
	}
}
