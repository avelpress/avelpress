<?php

namespace AvelPress\Config;

defined( 'ABSPATH' ) || exit;

/**
 * Config repository.
 * 
 * @since   1.0.0
 */
class ConfigRepository {

	protected $config;

	/**
	 * Merge two arrays recursively
	 * 
	 * @param array $array1
	 * @param array $array2
	 * 
	 * @return array
	 */
	private function mergeConfig( $array1, $array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				$array_keys = array_keys( $value );
				if ( isset( $array_keys[0] ) && is_string( $array_keys[0] ) ) {
					$merged[ $key ] = $this->mergeConfig( $merged[ $key ], $value );
				} else {
					$merged[ $key ] = $value;
				}
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}

	public function __construct( $defaults = [] ) {
		$base_dir = isset( $defaults['app'], $defaults['app']['base_path'] ) ? $defaults['app']['base_path'] : '';
		$app_id = isset( $defaults['app'], $defaults['app']['id'] ) ? $defaults['app']['id'] : '';
		$default_config = include "{$base_dir}src/config/app.php";

		if ( ! empty( $base_dir ) )
			$default_config['app']['base_path'] = $base_dir;

		if ( ! empty( $app_id ) )
			$default_config['app']['id'] = $app_id;

		$saved_config = get_option( "{$app_id}_settings", [] );
		$this->config = $this->mergeConfig( $default_config, $saved_config );
	}

	/**
	 * Update config
	 * 
	 * @param mixed $name
	 * @param mixed $value
	 * 
	 * @return void
	 */
	public function update( $name, $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $val ) {
				if ( is_bool( $val ) ) {
					$value[ $key ] = $val ? 'yes' : 'no';
				}
			}
		}

		$keys = explode( '.', $name );

		$update_data = count( $keys ) > 1 ? $this->addKeyValueRecursively( $keys, $value ) : [ $name => $value ];

		$settings = $this->mergeConfig( $this->config, $update_data );

		$this->config = $settings;
	}

	/**
	 * Add Recursive Key
	 *
	 * @param 	array 	$keys	
	 * @param 	mixed 	$value
	 * @param 	bool 	$create Create the key if it doesn't exist
	 * 
	 * @return 	array
	 */
	private function addKeyValueRecursively( array $keys, $value, $create = false ) {
		return array_reduce( array_reverse( $keys ), function ($carry, $key) use ($value) {
			return [ $key => $carry ?: $value ];
		}, [] );
	}

	/**
	 * Get the config
	 *
	 * @since 1.0.0
	 * 
	 * @return mixed
	 */
	public function get( $name, $default = null ) {
		$names = explode( '.', $name );
		$config = $this->config;
		foreach ( $names as $name ) {
			if ( isset( $config[ $name ] ) ) {
				$config = $config[ $name ];
			} else {
				return $default;
			}
		}
		return $config;
	}


	/**
	 * Get the config as sanitized string
	 *
	 * @since 1.0.0
	 * 
	 * @param string $name
	 * @param string $default
	 * 
	 * @return string
	 */
	public function string( $name, $default = null ) {
		return sanitize_text_field( $this->get( $name, $default ) );
	}

	/**
	 * Get the config as sanitized boolean
	 *
	 * @since 1.0.0
	 * 
	 * @param string $name
	 * @param int $default
	 * 
	 * @return int
	 */
	public function boolean( $name, $default = null ) {
		return $this->get( $name, $default ) === 'yes' ? true : false;
	}

	/**
	 * Get the config as sanitized integer
	 *
	 * @since 1.0.0
	 * 
	 * @param string $name
	 * @param int $default
	 * 
	 * @return int
	 */
	public function integer( $name, $default = null ) {
		return (int) $this->get( $name, $default );
	}

	/**
	 * Get all config
	 * 
	 * @since 1.0.0
	 * 
	 * @return mixed
	 */
	public function all() {
		return $this->config;
	}
}