<?php

namespace AvelPress\Config;

use AvelPress\Foundation\Application;

defined( 'ABSPATH' ) || exit;

/**
 * Settings repository.
 * 
 * @since   1.0.0
 */
class SettingsRepository {

	protected $config;

	/**
	 * App Instance
	 * 
	 * @var Application
	 */
	protected $app;

	public function __construct( Application $app, $defaults = [] ) {
		$this->app = $app;

		$saved_config = get_option( "{$this->app->getIdAsUnderscore()}_settings", [] );
		$this->config = $this->mergeConfig( $defaults, $saved_config );
	}

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

	/**
	 * Update config
	 * 
	 * @param mixed $name
	 * @param mixed $value
	 * 
	 * @return bool
	 */
	public function update( $name, $value ) {
		$processed_value = $this->processValue( $value );

		$keys = explode( '.', $name );

		$update_data = count( $keys ) > 1 ? $this->addKeyValueRecursively( $keys, $processed_value ) : [ $name => $processed_value ];

		$settings = $this->mergeConfig( $this->config, $update_data );

		$this->config = $settings;

		return $this->save();
	}

	/**
	 * Process value for storage
	 * 
	 * @param mixed $value
	 * 
	 * @return mixed
	 */
	private function processValue( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $val ) {
				$value[ $key ] = $this->processValue( $val );
			}
		} elseif ( is_bool( $value ) ) {
			return $value ? 'yes' : 'no';
		}

		return $value;
	}

	/**
	 * Save config to database
	 * 
	 * @return bool
	 */
	public function save() {
		return update_option( "{$this->app->getIdAsUnderscore()}_settings", $this->config );
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
	 * @param bool $default
	 * 
	 * @return bool
	 */
	public function boolean( $name, $default = false ) {
		$value = $this->get( $name, $default );

		if ( is_bool( $value ) ) {
			return $value;
		}

		return $value === 'yes' || $value === '1' || $value === 1 || $value === true;
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
	 * @return array
	 */
	public function all() {
		return $this->config;
	}

	/**
	 * Delete a config key
	 * 
	 * @param string $name
	 * 
	 * @return bool
	 */
	public function delete( $name ) {
		$keys = explode( '.', $name );
		$config = &$this->config;

		for ( $i = 0; $i < count( $keys ) - 1; $i++ ) {
			if ( ! isset( $config[ $keys[ $i ] ] ) || ! is_array( $config[ $keys[ $i ] ] ) ) {
				return false;
			}
			$config = &$config[ $keys[ $i ] ];
		}

		$lastKey = end( $keys );
		if ( isset( $config[ $lastKey ] ) ) {
			unset( $config[ $lastKey ] );
			return $this->save();
		}

		return false;
	}

	/**
	 * Check if config key exists
	 * 
	 * @param string $name
	 * 
	 * @return bool
	 */
	public function has( $name ) {
		$names = explode( '.', $name );
		$config = $this->config;

		foreach ( $names as $key ) {
			if ( ! isset( $config[ $key ] ) ) {
				return false;
			}
			$config = $config[ $key ];
		}

		return true;
	}
}