<?php

namespace AvelPress\Foundation;

use AvelPress\Config\ConfigServiceProvider;
use AvelPress\Database\DatabaseServiceProvider;
use AvelPress\Routing\RouterServiceProvider;

defined( 'ABSPATH' ) || exit;

class Application {

	protected $id;
	protected $instances = [];
	protected $bindings = [];
	protected $serviceProviders = [];

	protected $routeFiles = [];

	protected $migrationFolders = [];

	//**asdasd */
	protected $basePath;

	public function __construct( $id, $basePath = null ) {
		$this->id = $id;

		if ( $basePath ) {
			$this->setBasePath( $basePath );
		}

		$this->registerBaseBindings();
		$this->registerBaseServiceProviders();
		$this->registerServiceProviders();
		$this->registerCoreContainerAliases();
		$this->bootstrap();

		do_action( "{$this->id}_app_booted", $this );
	}

	public function getId() {
		return $this->id;
	}

	public function getBasePath() {
		return $this->basePath;
	}

	public function booted( \Closure $callback ) {
		add_action( "{$this->id}_app_booted", $callback );
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

	}

	public function addRouteFile( $path ) {
		$this->routeFiles[] = $path;
	}

	public function addMigrationFolder( $path ) {
		$this->migrationFolders[] = $path;
	}

	public function getRouteFiles() {
		return $this->routeFiles;
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
			}
		}

		return $this->build( $abstract );
	}

	protected function build( $concrete ) {
		if ( $concrete instanceof \Closure ) {
			return $concrete();
		}

		if ( is_string( $concrete ) ) {
			return new $concrete();
		}

		return $concrete;
	}
}