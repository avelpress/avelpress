<?php

namespace AvelPress\Config;

use AvelPress\Foundation\Application;

defined( 'ABSPATH' ) || exit;

/**
 * Config repository.
 * 
 * @since   1.0.0
 */
class Config {

	protected $config;

	public function __construct( $config ) {
		$this->config = $config;
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
	public function boolean( $name, $default = null ) {
		return $this->get( $name, $default ) === true;
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