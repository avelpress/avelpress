<?php

namespace AvelPress\Config;

use AvelPress\AvelPress;
use AvelPress\Support\ServiceProvider;

defined( 'ABSPATH' ) || exit;

class ConfigServiceProvider extends ServiceProvider {
	public function register() {
		$this->app->singleton( 'config', function () {
			return new ConfigRepository( [ 
				'app' => [ 
					'id' => $this->app->getId(),
					'base_path' => $this->app->getBasePath(),
				],
			] );
		} );
	}
}