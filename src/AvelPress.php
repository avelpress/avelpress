<?php

namespace AvelPress;

use AvelPress\Foundation\Application;
use AvelPress\Utils\Str;

defined( 'ABSPATH' ) || exit;

class AvelPress {
	/**
	 * AvelPress Application Container.
	 *
	 * @var Application
	 */
	private static $app;

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

		self::$app = new Application( $id, $config );

		self::$app->bootstrap();

		$appIdSnake = Str::toSnake( $id );

		do_action( "{$appIdSnake}_app_booted", self::$app );

		return self::$app;
	}

	public static function app( $service = null ) {

		if ( ! $service ) {
			return self::$app;
		}

		return self::$app->make( $service );
	}
}
