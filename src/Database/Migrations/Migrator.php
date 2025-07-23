<?php
namespace AvelPress\Database\Migrations;

use AvelPress\Foundation\Application;

defined( 'ABSPATH' ) || exit;
class Migrator {
	/**
	 * The id of the migration.
	 *
	 * @var string
	 */
	protected $prefix;

	protected $path;

	/**
	 * The application instance.
	 *
	 * @var Application
	 */
	protected $app;

	public function __construct( Application $app ) {
		$this->app = $app;
		$this->prefix = $app->getId();
		$this->path = $app->getBasePath();
	}

	public function run() {
		$files = glob( "{$this->path}/database/migrations/*.php" );

		if ( ! empty( $this->app->getMigrationFolders() ) && is_array( $this->app->getMigrationFolders() ) ) {
			foreach ( $this->app->getMigrationFolders() as $folder ) {
				$extraFiles = glob( "{$folder}/*.php" );
				if ( $extraFiles ) {
					$files = array_merge( $files, $extraFiles );
				}
			}
		}

		if ( ! $files ) {
			return;
		}

		$option_key = "_{$this->prefix}_migrations";
		$applied = get_option( $option_key, [] );

		foreach ( $files as $file ) {
			$migration = require_once $file;

			$migration_id = basename( $file, '.php' );

			if ( in_array( $migration_id, $applied, true ) ) {
				continue;
			}

			if ( method_exists( $migration, 'up' ) ) {
				$migration->up();
				$applied[] = $migration_id;
				update_option( $option_key, $applied );
			}
		}
	}
}