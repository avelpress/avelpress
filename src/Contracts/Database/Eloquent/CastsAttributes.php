<?php

namespace AvelPress\Contracts\Database\Eloquent;

use AvelPress\Database\Eloquent\Model;

interface CastsAttributes {
	/**
	 * Transform the attribute from the underlying model values.
	 *
	 * @param  Model  $model
	 * @param  string  $key
	 * @param  mixed  $value
	 * @param  array<string, mixed>  $attributes
	 * 
	 * @return mixed
	 */
	public function get( Model $model, string $key, mixed $value, array $attributes );

	/**
	 * Transform the attribute to its underlying model values.
	 *
	 * @param  Model  $model
	 * @param  string  $key
	 * @param  mixed|null  $value
	 * @param  array<string, mixed>  $attributes
	 * 
	 * @return mixed
	 */
	public function set( Model $model, string $key, mixed $value, array $attributes );
}