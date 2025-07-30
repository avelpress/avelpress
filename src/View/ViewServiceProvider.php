<?php

namespace AvelPress\View;

use AvelPress\Support\ServiceProvider;

defined( 'ABSPATH' ) || exit;

class ViewServiceProvider extends ServiceProvider {

	public function register() {
		$this->app->singleton( 'view', function () {
			return new View( $this->app );
		} );
	}
}