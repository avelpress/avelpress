<?php

namespace AvelPress\Routing;

use AvelPress\Routing\RouterBuilder;
use AvelPress\Support\ServiceProvider;

defined( 'ABSPATH' ) || exit;

class RouterServiceProvider extends ServiceProvider {
	public function register() {
		$this->app->singleton( 'router', function ($app) {
			return new RouterBuilder( $app );
		} );
	}

	public function boot() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
		add_action( 'admin_menu', [ $this, 'register_admin_routes' ], 99 );
	}

	public function register_rest_routes() {
		$apiRoutesPath = $this->app->getBasePath() . '/routes/api.php';

		$routes = [ 
			$apiRoutesPath,
			...$this->app->getRestRouteFiles()
		];

		foreach ( $routes as $routeFile ) {
			if ( file_exists( $routeFile ) ) {
				require_once $routeFile;
			}
		}
	}

	public function register_admin_routes() {
		$adminRoutesPath = $this->app->getBasePath() . '/routes/admin.php';

		$routes = [ 
			$adminRoutesPath,
			...$this->app->getAdminRouteFiles()
		];

		foreach ( $routes as $routeFile ) {
			if ( file_exists( $routeFile ) ) {
				require_once $routeFile;
			}
		}
	}
}