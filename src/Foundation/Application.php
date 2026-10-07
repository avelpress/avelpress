<?php

namespace AvelPress\Foundation;

use AvelPress\Admin\AdminServiceProvider;
use AvelPress\Config\Config;
use AvelPress\Config\ConfigServiceProvider;
use AvelPress\Config\SettingsRepository;
use AvelPress\Database\DatabaseServiceProvider;
use AvelPress\Routing\RouterServiceProvider;
use AvelPress\Utils\Str;
use AvelPress\View\ViewServiceProvider;

defined( 'ABSPATH' ) || exit;

class Application {

	protected $id;

	protected $snake_id;
	protected $instances = [];
	protected $bindings = [];
	protected $serviceProviders = [];

	protected $routeFiles = [];

	protected $migrationFolders = [];
	protected $basePath;

	protected $pluginRoot;

	protected $config = [];

	public function __construct( $id, $config = [] ) {
		$this->id = $id;
		$this->config = $config;
		$this->snake_id = Str::toSnake( $id );

		if ( isset( $config['base_path'] ) ) {
			$this->setBasePath( $config['base_path'] );
		}

		if ( isset( $config['plugin_root'] ) ) {
			$this->pluginRoot = rtrim( $config['plugin_root'], '/' );
		}


		$this->registerBaseBindings();
		$this->registerBaseServiceProviders();
		$this->registerServiceProviders();
		$this->registerCoreContainerAliases();
	}

	public function getId() {
		return $this->id;
	}

	/**
	 * Get id with underscore, ex: my-app-id => my_app_id
	 * 
	 * @return array|string
	 */
	public function getIdAsUnderscore() {
		return str_replace( '-', '_', $this->id );
	}

	public function getBasePath() {
		return $this->basePath;
	}

	public function pluginRoot() {
		return $this->pluginRoot;
	}

	public function getPluginFile() {
		return "{$this->pluginRoot()}/{$this->getId()}.php";
	}

	public function booted( \Closure $callback ) {
		add_action( "{$this->snake_id}_app_booted", $callback );
	}

	protected function setBasePath( $basePath ) {
		$this->basePath = rtrim( $basePath, '/' );
	}

	protected function registerBaseBindings() {

	}
	protected function registerBaseServiceProviders() {
		$this->register( new ConfigServiceProvider( $this ) );
		$this->register( new RouterServiceProvider( $this ) );
		$this->register( new DatabaseServiceProvider( $this ) );
		$this->register( new ViewServiceProvider( $this ) );
		$this->register( new AdminServiceProvider( $this ) );
	}

	protected function registerServiceProviders() {
		$providersFile = $this->getBasePath() . '/bootstrap/providers.php';
		if ( file_exists( $providersFile ) ) {
			$providers = include $providersFile;
			if ( is_array( $providers ) ) {
				foreach ( $providers as $provider ) {
					$this->register( $provider );
				}
			}
		}
	}

	protected function registerCoreContainerAliases() {

	}

	public function register( $provider ) {
		$class = is_string( $provider ) ? $provider : get_class( $provider );
		if ( isset( $this->serviceProviders[ $class ] ) ) {
			return $this->serviceProviders[ $class ];
		}

		if ( is_string( $provider ) ) {
			$provider = new $provider( $this );
		}

		$this->serviceProviders[ $class ] = $provider;
		if ( method_exists( $provider, 'register' ) ) {
			$provider->register();
		}
		return $provider;
	}

	public function bootstrap() {
		foreach ( $this->serviceProviders as $provider ) {
			if ( method_exists( $provider, 'boot' ) ) {
				$provider->boot();
			}
		}

		$this->bootUpdater();
	}

	/**
	 * Hands the `updater` config to the avelpress/updater package.
	 *
	 * The updater is a separate package, so plugins published on wordpress.org do
	 * not ship code that changes where updates come from. A plugin that declares
	 * `updater` without requiring the package keeps working, without updates,
	 * and administrators are told why.
	 *
	 * @since 1.3.0
	 * @since 2.0.0 Delegates to avelpress/updater.
	 */
	protected function bootUpdater() {
		if ( empty( $this->config['updater'] ) ) {
			return;
		}

		if ( ! class_exists( \AvelPress\Update\UpdaterBootstrapper::class ) ) {
			$this->warnUpdaterMissing();
			return;
		}

		\AvelPress\Update\UpdaterBootstrapper::boot( $this, $this->config['updater'] );
	}

	/**
	 * Reports an `updater` config that has no package to run it.
	 *
	 * @since 2.0.0
	 */
	protected function warnUpdaterMissing() {
		$message = sprintf(
			'%s configures automatic updates, but the avelpress/updater package is not installed, so new versions will not be offered. Run "composer require avelpress/updater" and rebuild the plugin.',
			$this->id
		);

		if ( function_exists( '_doing_it_wrong' ) ) {
			_doing_it_wrong( __METHOD__, esc_html( $message ), '2.0.0' );
		}

		add_action( 'admin_notices', function () use ( $message ) {
			if ( ! current_user_can( 'update_plugins' ) ) {
				return;
			}

			printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_html( $message ) );
		} );
	}

	public function addRouteFile( $path, $type = 'api' ) {
		$this->routeFiles[] = [
			'type' => $type,
			'path' => $path
		];
	}

	public function addMigrationFolder( $path ) {
		$this->migrationFolders[] = $path;
	}

	public function getRestRouteFiles() {
		return array_map(
			fn( $route ) => $route['path'],
			array_filter( $this->routeFiles, fn( $route ) => $route['type'] === 'api' )
		);
	}

	public function getAdminRouteFiles() {
		return array_map(
			fn( $route ) => $route['path'],
			array_filter( $this->routeFiles, fn( $route ) => $route['type'] === 'admin' )
		);
	}

	public function getMigrationFolders() {
		return $this->migrationFolders;
	}

	public function instance( $abstract, $instance = null ) {
		if ( $instance === null ) {
			return $this->instances[ $abstract ] ?? null;
		}

		$this->instances[ $abstract ] = $instance;
	}

	public function singleton( $abstract, $concrete = null ) {
		$this->bindings[ $abstract ] = [
			'concrete' => $concrete ?: $abstract,
			'shared' => true,
		];
	}

	public function bind( $abstract, $concrete = null ) {
		$this->bindings[ $abstract ] = [
			'concrete' => $concrete ?: $abstract,
			'shared' => false,
		];
	}

	public function make( $abstract ) {
		if ( isset( $this->instances[ $abstract ] ) ) {
			return $this->instances[ $abstract ];
		}

		if ( isset( $this->bindings[ $abstract ] ) ) {
			$binding = $this->bindings[ $abstract ];

			if ( $binding['shared'] ) {
				$instance = $this->build( $binding['concrete'] );
				$this->instances[ $abstract ] = $instance;
				return $instance;
			} else {
				return $this->build( $binding['concrete'] );
			}
		}

		return $this->build( $abstract );
	}

	public function version() {
		$plugin_file = $this->getPluginFile();

		$data = get_file_data(
			$plugin_file,
			[ 'Version' => 'Version' ]
		);

		return $data['Version'] ?? '1.0.0';
	}

	protected function build( $concrete ) {
		if ( $concrete instanceof \Closure ) {
			$reflection = new \ReflectionFunction( $concrete );
			if ( $reflection->getNumberOfParameters() > 0 ) {
				return $concrete( $this );
			}
			return $concrete();
		}

		if ( is_string( $concrete ) ) {
			return new $concrete();
		}

		return $concrete;
	}

	/**
	 * Config Service
	 * 
	 * @return Config
	 */
	public function config() {
		return $this->make( 'config' );
	}

	public function getTranslations() {
		$base = $this->getBasePath();
		$path = "{$base}/resources/lang/translations.php";
		return include $path;
	}


	/**
	 * Settings Service
	 * 
	 * @return SettingsRepository
	 */
	public function settings() {
		return $this->make( 'settings' );
	}
}