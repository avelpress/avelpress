<?php

namespace AvelPress\Utils;

defined( 'ABSPATH' ) || exit;

class Sanitizer {

	/**
	 * Sanitize a boolean value.
	 *
	 * Accepts true, false, 1, 0, 'true', 'false', '1', '0', 'yes', 'no' etc.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public static function boolean( $value ): bool {
		return rest_sanitize_boolean( $value );
	}

	/**
	 * Sanitize a text string (strips tags, extra whitespace).
	 *
	 * @param mixed $value
	 * @param string $default
	 * @return string
	 */
	public static function string( $value, string $default = '' ): string {
		if ( $value === null ) {
			return $default;
		}
		return sanitize_text_field( wp_unslash( (string) $value ) );
	}

	/**
	 * Sanitize a textarea value (preserves newlines).
	 *
	 * @param mixed $value
	 * @param string $default
	 * @return string
	 */
	public static function textarea( $value, string $default = '' ): string {
		if ( $value === null ) {
			return $default;
		}
		return sanitize_textarea_field( wp_unslash( (string) $value ) );
	}

	/**
	 * Sanitize an integer value.
	 *
	 * @param mixed $value
	 * @param int $default
	 * @return int
	 */
	public static function integer( $value, int $default = 0 ): int {
		if ( $value === null || $value === '' ) {
			return $default;
		}
		return (int) $value;
	}

	/**
	 * Sanitize a float value.
	 *
	 * @param mixed $value
	 * @param float $default
	 * @return float
	 */
	public static function float( $value, float $default = 0.0 ): float {
		if ( $value === null || $value === '' ) {
			return $default;
		}
		return (float) $value;
	}

	/**
	 * Sanitize an email address.
	 *
	 * @param mixed $value
	 * @param string $default
	 * @return string
	 */
	public static function email( $value, string $default = '' ): string {
		$sanitized = sanitize_email( (string) $value );
		return $sanitized !== '' ? $sanitized : $default;
	}

	/**
	 * Sanitize a URL for use in database or redirects (does not escape for HTML output).
	 *
	 * @param mixed $value
	 * @param string $default
	 * @return string
	 */
	public static function url( $value, string $default = '' ): string {
		$sanitized = esc_url_raw( (string) $value );
		return $sanitized !== '' ? $sanitized : $default;
	}

	/**
	 * Sanitize each element of an array as a text string.
	 *
	 * @param mixed $value
	 * @return array
	 */
	public static function arrayOfStrings( $value ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}
		return array_map( fn( $item ) => sanitize_text_field( wp_unslash( (string) $item ) ), $value );
	}

	/**
	 * Sanitize a value from a request param, applying the given type.
	 *
	 * @param mixed  $value
	 * @param string $type  'string' | 'boolean' | 'integer' | 'float' | 'email' | 'url' | 'textarea'
	 * @param mixed  $default
	 * @return mixed
	 */
	public static function cast( $value, string $type, $default = null ) {
		switch ( $type ) {
			case 'boolean':
				return static::boolean( $value );
			case 'integer':
				return static::integer( $value, (int) $default );
			case 'float':
				return static::float( $value, (float) $default );
			case 'email':
				return static::email( $value, (string) $default );
			case 'url':
				return static::url( $value, (string) $default );
			case 'textarea':
				return static::textarea( $value, (string) $default );
			case 'string':
			default:
				return static::string( $value, (string) $default );
		}
	}
}
