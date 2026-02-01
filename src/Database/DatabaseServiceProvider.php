<?php

namespace AvelPress\Database;

use AvelPress\Database\Migrations\Migrator;
use AvelPress\Support\ServiceProvider;

defined( 'ABSPATH' ) || exit;

class DatabaseServiceProvider extends ServiceProvider {
	public function register() {
		$app = $this->app;
		$app->singleton( 'database', function () use ($app) {
			return new Database( $app );
		} );

		$this->app->singleton( 'migrator', function () {
			return new Migrator( $this->app );
		} );
	}

	public function boot() {
		add_filter( 'query', [ $this, 'nulled_query_replace' ] );
	}

	public function nulled_query_replace( $query ) {
		return str_replace( [ "IS '!#####NULL#####!'", "IS NOT '!#####NULL#####!'" ], [ 'IS NULL', 'IS NOT NULL' ], $query );
	}
}