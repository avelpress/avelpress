<?php

namespace AvelPress\Support;

use AvelPress\Foundation\Application;

defined( 'ABSPATH' ) || exit;

class ServiceProvider {

	/**
	 * The application instance.
	 *
	 * @var Application
	 */
	public $app;

	public function __construct( Application $app ) {
		$this->app = $app;
	}


	public function register() {

	}

	public function boot() {

	}

	public function loadRoutesFrom( $path ) {
		$this->app->addRouteFile( $path );
	}

	public function loadMigrationsFrom( $path ) {
		$this->app->addMigrationFolder( $path );
	}
}