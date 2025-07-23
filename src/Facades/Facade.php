<?php

namespace AvelPress\Facades;

use AvelPress\AvelPress;
use AvelPress\Support\Container;

defined( 'ABSPATH' ) || exit;

abstract class Facade {
	protected static $resolvedInstance = [];

	public static function __callStatic( $method, $args ) {
		$instance = static::getFacadeRoot();

		if ( ! $instance ) {
			throw new \RuntimeException( 'A facade root has not been set.' );
		}

		return $instance->$method( ...$args );
	}

	public static function getFacadeRoot() {
		return static::resolveFacadeInstance( static::getFacadeAccessor() );
	}

	protected static function getFacadeAccessor() {
		throw new \RuntimeException( 'Facade does not define a facade accessor.' );
	}

	protected static function resolveFacadeInstance( $name ) {
		if ( isset( static::$resolvedInstance[ $name ] ) ) {
			return static::$resolvedInstance[ $name ];
		}

		return static::$resolvedInstance[ $name ] = AvelPress::app( $name );
	}

	public static function clearResolvedInstance( $name ) {
		unset( static::$resolvedInstance[ $name ] );
	}

	public static function clearResolvedInstances() {
		static::$resolvedInstance = [];
	}
}