<?php

namespace AvelPress;

use AvelPress\Foundation\Application;
use AvelPress\Utils\Str;

defined( 'ABSPATH' ) || exit;

class AvelPress {
	/**
	 * AvelPress Application Container.
	 *
	 * @var Application[]
	 */
	private static $apps = [];

	/**
	 * Initializes the AvelPress configuration.
	 *
	 * @param string $id Required parameter.
	 * @param array $config Optional configuration.
	 * 
	 * @return Application
	 */
	public static function init( string $id, array $config = [] ) {
		if ( empty( $id ) ) {
			throw new \InvalidArgumentException( 'The "id" parameter is required.' );
		}

		if ( ! isset( $config['base_path'] ) ) {
			throw new \InvalidArgumentException( 'The "base_path" parameter is required.' );
		}

		if ( ! isset( $config['plugin_root'] ) ) {
			$config['plugin_root'] = dirname( $config['base_path'] );
		}

		if ( ! file_exists( $config['plugin_root'] . "/$id.php" ) ) {
			throw new \InvalidArgumentException( "The plugin file for '$id' does not exist in the specified plugin root, in AvelPress::init configure plugin_root." );
		}

		self::$apps[ $id ] = new Application( $id, $config );

		self::$apps[ $id ]->bootstrap();

		$appIdSnake = Str::toSnake( $id );

		do_action( "{$appIdSnake}_app_booted", self::$apps[ $id ] );

		return self::$apps[ $id ];
	}

	/**
	 * Get an app instance or a service from a specific app.
	 *
	 * @param string|null $service Service name.
	 * @param string|null $appId Application ID.
	 *
	 * @return Application|mixed
	 */
	public static function app( $service = null, $appId = null ) {

		if ( ! $appId && count( self::$apps ) > 1 && class_exists( 'AvelPress\Cli\Kernel' ) ) {
			$trace = debug_backtrace();
			foreach ( $trace as $frame ) {
				if ( isset( $frame['file'] ) ) {
					foreach ( array_keys( self::$apps ) as $id ) {
						$pluginFile = self::$apps[ $id ]->pluginRoot();
						if ( strpos( $frame['file'], $pluginFile ) !== false ) {
							$appId = $id;
							break 2;
						}
					}
				}
			}

			if ( ! $appId ) {
				foreach ( self::$apps as $id => $app ) {
					$composerJson = $app->pluginRoot() . '/composer.json';
					if ( file_exists( $composerJson ) ) {
						$data = json_decode( file_get_contents( $composerJson ), true );
						$psr4 = array_keys( $data['autoload']['psr-4'] ?? [] );
						if ( isset( $psr4[0] ) && $service && Str::startsWith( $service, $psr4[0] ) ) {
							$appId = $id;
							break;
						}
					}
				}
			}
		}

		if ( ! $appId ) {
			// fallback to first app
			$appId = array_key_first( self::$apps );
		}

		if ( ! $service ) {
			return self::$apps[ $appId ];
		}

		return self::$apps[ $appId ]->make( $service );
	}
}
