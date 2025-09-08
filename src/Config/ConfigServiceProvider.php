<?php

namespace AvelPress\Config;

use AvelPress\Support\ServiceProvider;

defined( 'ABSPATH' ) || exit;

class ConfigServiceProvider extends ServiceProvider {
	public function register() {
		$this->app->singleton( 'config', function () {
			$configPath = $this->app->getBasePath() . '/config';
			$config = [];
			if ( is_dir( $configPath ) ) {
				foreach ( glob( $configPath . '/*.php' ) as $file ) {
					$key = basename( $file, '.php' );
					$config[ $key ] = require $file;
				}
			}
			return new Config( $config );
		} );

		$this->app->singleton( 'settings', function ($app) {
			return new SettingsRepository( $app );
		} );
	}
}