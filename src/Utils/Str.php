<?php
namespace AvelPress\Utils;

defined( 'ABSPATH' ) || exit;

class Str {
	/**
	 * Get nested value from array using dot notation
	 * 
	 * @param array $data The data array
	 * @param string $key The key (supports dot notation like 'customer.email')
	 * @param mixed $default Default value if key not found
	 * @return mixed
	 */
	public static function getNestedValue( array $data, string $key, $default = null ) {
		if ( strpos( $key, '.' ) === false ) {
			return $data[ $key ] ?? $default;
		}

		$keys = explode( '.', $key );
		$value = $data;

		foreach ( $keys as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return $default;
			}
			$value = $value[ $segment ];
		}

		return $value;
	}

	/**
	 * Set nested value in array using dot notation
	 * 
	 * @param array &$data The data array (passed by reference)
	 * @param string $key The key (supports dot notation like 'customer.email')
	 * @param mixed $value The value to set
	 * @return void
	 */
	public static function setNestedValue( array &$data, string $key, $value ) {
		if ( strpos( $key, '.' ) === false ) {
			$data[ $key ] = $value;
			return;
		}

		$keys = explode( '.', $key );
		$current = &$data;

		foreach ( $keys as $i => $segment ) {
			if ( $i === count( $keys ) - 1 ) {
				$current[ $segment ] = $value;
			} else {
				if ( ! isset( $current[ $segment ] ) || ! is_array( $current[ $segment ] ) ) {
					$current[ $segment ] = [];
				}
				$current = &$current[ $segment ];
			}
		}
	}

	public static function toSnake( string $string ): string {
		return str_replace( '-', '_', $string );
	}

	public static function startsWith( string $haystack, string $needle ): bool {
		return strncmp( $haystack, $needle, strlen( $needle ) ) === 0;
	}
}