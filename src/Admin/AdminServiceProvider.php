<?php

namespace AvelPress\Admin;

use AvelPress\Admin\Menu\MenuBuilder;
use AvelPress\Facades\Config;
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
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'admin_setup' ] );

		$this->app->make( WooCommerce::class)->init();
		$this->app->make( 'admin.manager' )->init();
	}

	public function admin_setup(): void {
		$setup_class = $this->app->make( 'config' )->string( 'app.admin_setup_class' );

		if ( ! class_exists( $setup_class ) ) {
			return;
		}

		$setup = new $setup_class();
	}

	public function admin_menu() {
		$menu_class = $this->app->make( 'config' )->string( 'app.admin_menu_class' );

		if ( ! class_exists( $menu_class ) ) {
			return;
		}

		/** @var MenuBuilder $adminMenu **/
		$adminMenu = new $menu_class( $this->app );
		$adminMenu->register();
		$adminMenu->create();
	}
}