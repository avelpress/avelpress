<?php

namespace AvelPress\Routing;

use AvelPress\Routing\Router;
use AvelPress\Support\ServiceProvider;

defined( 'ABSPATH' ) || exit;

class RouterServiceProvider extends ServiceProvider {
	public function register() {
		$this->app->singleton( 'router', Router::class);
	}

	public function boot() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		$apiRoutesPath = $this->app->getBasePath() . '/routes/api.php';

		$routes = [ 
			$apiRoutesPath,
			...$this->app->getRouteFiles()
		];

		foreach ( $routes as $routeFile ) {
			if ( file_exists( $routeFile ) ) {
				require_once $routeFile;
			}
		}
	}
}