<?php

namespace AvelPress;

use AvelPress\Foundation\Application;

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
			throw new \InvalidArgumentException( 'O parâmetro "id" é obrigatório.' );
		}

		if ( ! isset( $config['base_path'] ) ) {
			throw new \InvalidArgumentException( 'O parâmetro "base_path" é obrigatório.' );
		}

		self::$app = new Application( $id, $config['base_path'] );

		return self::$app;
	}

	public static function app( $service = null ) {

		if ( ! $service ) {
			return self::$app;
		}

		return self::$app->make( $service );
	}
}
