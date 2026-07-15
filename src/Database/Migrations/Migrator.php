<?php
namespace AvelPress\Database\Migrations;

use AvelPress\Database\Database;
use AvelPress\Database\Schema\Blueprint;
use AvelPress\Database\Schema\Schema;
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


	protected $tableName;

	protected $oldVersion;

	public function __construct( Application $app ) {
		$this->app = $app;
		$this->prefix = $app->getIdAsUnderscore();
		$this->path = $app->getBasePath();
		$this->tableName = "{$this->prefix}_migrations";
		$this->oldVersion = get_option( "{$this->prefix}_version", $app->version() );
	}

	public function maybeCreateMigrationsTable() {
		if ( Database::tableExists( $this->tableName ) ) {
			return;
		}

		try {
			Schema::create( $this->tableName, function ( Blueprint $table ) {
				$table->id( 'id' );
				$table->string( 'name' );
				$table->string( 'file' );
				$table->timestamps();
			} );
		} catch (\Throwable $e) {
			// run() is called from a boot hook, so letting this escape would take the whole site
			// down on every request. Log it and carry on: without this table run() simply finds
			// no applied migrations, which is safe because each migration is itself idempotent.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'AvelPress: could not create migrations table %s: %s', $this->tableName, $e->getMessage() ) );
		}
	}

	public function processMigrationFile( string $file ) {
		/** @var Migration $migration */
		$migration = require $file;

		if ( ! method_exists( $migration, 'up' ) ) {
			return false;
		}

		$migration->setOldVersion( $this->oldVersion );

		try {
			$migration->up();
		} catch (\Throwable $e) {
			// Never report a migration as applied when its schema change failed. run() skips
			// anything already recorded, so recording a failure makes it permanent: the table
			// or column stays missing and is never retried, and the only symptom is writes
			// silently doing nothing.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'AvelPress: migration %s failed: %s', basename( $file, '.php' ), $e->getMessage() ) );
			return false;
		}

		return true;
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

		$this->maybeCreateMigrationsTable();

		$model = Database::table( $this->tableName );

		$migrations = $model->select( 'name' )->get()->pluck( 'name' )->toArray();
		$applied = [];

		foreach ( $files as $file ) {
			$migration_id = basename( $file, '.php' );

			if ( in_array( $migration_id, $migrations, true ) ) {
				continue;
			}

			$migrated = $this->processMigrationFile( $file );

			if ( $migrated ) {
				$migrations[] = $migration_id;
				$applied[] = $migration_id;

				Database::insert( Database::getTableName( $this->tableName ), [
					'name' => $migration_id,
					'file' => $file,
					'created_at' => current_time( 'mysql' ),
					'updated_at' => current_time( 'mysql' ),
				] );
			}
		}

		return $applied;
	}

	public function fresh() {
		$model = Database::table( $this->tableName );

		$migrations = $model->get();

		if ( ! $migrations->isEmpty() ) {
			foreach ( $migrations as $mg ) {
				if ( file_exists( $mg->file ) ) {
					$migration = require_once $mg->file;

					if ( method_exists( $migration, 'down' ) ) {
						$migration->down();
						$success[] = $mg->name;
						$model->where( [ 'id' => $mg->id ] )->delete();
					}
				}
			}
		}

		return $this->run();
	}
}