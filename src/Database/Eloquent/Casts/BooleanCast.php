<?php

namespace AvelPress\Database\Eloquent\Casts;

use AvelPress\Contracts\Database\Eloquent\CastsAttributes;

class BooleanCast implements CastsAttributes {

	public function get( $model, string $key, $value, array $attributes ) {
		return (bool) $value;
	}

	public function set( $model, string $key, $value, array $attributes ) {
		return (int) $value;
	}
}