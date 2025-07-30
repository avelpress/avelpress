<?php

namespace AvelPress\Admin;

use AvelPress\Support\ServiceProvider;

defined( 'ABSPATH' ) || exit;

class AdminServiceProvider extends ServiceProvider {
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {
		$app = $this->app;
		$app->singleton( 'admin.manager', AdminManager::class);
		$app->singleton( WooCommerce::class, function () use ($app) {
			return new WooCommerce( $app );
		} );
	}

	public function boot() {
		$this->app->make( WooCommerce::class)->init();
	}
}