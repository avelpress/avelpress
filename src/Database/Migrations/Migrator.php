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

	public function __construct( Application $app ) {
		$this->app = $app;
		$this->prefix = $app->getIdAsUnderscore();
		$this->path = $app->getBasePath();
		$this->tableName = "{$this->prefix}_migrations";
	}

	public function maybeCreateMigrationsTable() {
		if ( Database::tableExists( $this->tableName ) ) {
			return;
		}

		Schema::create( $this->tableName, function (Blueprint $table) {
			$table->id( 'id' );
			$table->string( 'name' );
			$table->string( 'file' );
			$table->timestamps();
		} );
	}

	public function processMigrationFile( string $file ) {
		$migration = require $file;

		if ( method_exists( $migration, 'up' ) ) {
			$migration->up();
			return true;
		}

		return false;
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