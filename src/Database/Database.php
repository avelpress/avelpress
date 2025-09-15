<?php
namespace AvelPress\Database;

use AvelPress\AvelPress;
use AvelPress\Database\Migrations\Migrate;
use AvelPress\Foundation\Application;
use AvelPress\Support\Container;

defined( 'ABSPATH' ) || exit;

/**
 * Database class
 * 
 * This class is responsible for handling all database operations
 * 
 * @since 1.0.0
 */
class Database {

	/**
	 * Wordpress database object
	 *
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * The migrator instance.
	 *
	 * @var \AvelPress\Database\Migrations\Migrator
	 */
	protected $migrator;

	public function __construct( Application $app ) {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->migrator = $app->make( 'migrator' );
	}

	public function migrator() {
		return $this->migrator;
	}

	public static function getTableName( $table_name, $prefix = '' ) {
		global $wpdb;
		return sprintf( '%s%s%s', $wpdb->prefix, $prefix, $table_name );
	}

	/**
	 * Create or update a table in the database
	 * 
	 * Useful for creating new tables and updating existing tables to a new structure.
	 *
	 * @since 1.0.0
	 * 
	 * @param string $table_name
	 * @param array $columns
	 * 
	 * @return void
	 */
	public static function createOrUpdateTable( $table_name, $columns ) {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$full_table_name = self::getTableName( $table_name );

		$generated_columns = array_map( function ($column_name, $column_type) {
			return "$column_name $column_type";
		}, array_keys( $columns ), $columns );

		$sql = "CREATE TABLE $full_table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			" . implode( ",\n", $generated_columns ) . ",
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}


	public static function tableExists( $tableName ) {
		global $wpdb;

		$exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $tableName ) );

		return $exists !== null;
	}

	/**
	 * Prepare a query
	 *
	 * @param string $query
	 * @param mixed $args
	 * @return string
	 */
	public function prepare( $query, ...$args ) {
		return $this->wpdb->prepare( $query, $args );
	}



	public static function table( $table ) {
		$model = new DynamicModel( [], self::getTableName( $table ) );
		return $model->getQueryBuilder();
	}

	/**
	 * Run a query
	 *
	 * @since 1.0.0
	 * 
	 * @param string $query
	 * 
	 * @return bool|int
	 */
	public function query( $query ) {
		return $this->wpdb->query( $query );
	}

	/**
	 * Get rows from the database.
	 *
	 * @since 1.0.0
	 * 
	 * @param string $query
	 * 
	 * @return object|null
	 */
	public function get_results( $query ) {
		return $this->wpdb->get_results( $query );
	}

	/**
	 * Get a single row from the database.
	 *
	 * @since 1.0.0
	 * 
	 * @param string $query
	 * 
	 * @return string|null
	 */
	public function get_var( $query ) {
		return $this->wpdb->get_var( $query );
	}

	/**
	 * Delete row(s) from the database.
	 *
	 * @since 1.0.0
	 * @param string $table
	 * @param array $where_values
	 * @param array $where_format
	 * 
	 * @return bool|int
	 */
	public function delete( $table, $where_values, $where_format = null ) {
		return $this->wpdb->delete( $table, $where_values, $where_format );
	}

	/**
	 * Insert data into a table.
	 *
	 * @since 1.0.0
	 * 
	 * @param string $table
	 * @param array $data
	 * 
	 * @return int|bool
	 */
	public static function insert( $table, $data ) {
		global $wpdb;
		$inserted = $wpdb->insert( $table, $data );
		if ( ! $inserted ) {
			return false;
		}
		return $wpdb->insert_id;
	}

	/**
	 * Update data into a table.
	 *
	 * @since 1.0.0
	 * 
	 * @param string $table
	 * @param array $data
	 * @param array $where_values
	 * 
	 * @return int|bool
	 */
	public function update( $table, $data, $where_values ) {
		$updated = $this->wpdb->update( $table, $data, $where_values );
		if ( ! $updated ) {
			return false;
		}
		return $updated;
	}

	/**
	 * Insert multiple rows into a table.
	 *
	 * @since 1.0.0
	 * 
	 * @param string $table_name
	 * @param array $data
	 * 
	 * @return bool|int Boolean true for CREATE, ALTER, TRUNCATE and DROP queries. Number of rows
	 *                  affected/selected for all other queries. Boolean false on error.
	 */
	function insert_multiple( $table_name, $data ) {
		if ( empty( $data ) ) {
			return false;
		}

		$first_item = $data[0];
		$columns = array_keys( $first_item );
		$columns_sql = implode( ', ', $columns );

		$values = [];
		$placeholders = [];

		foreach ( $data as $item ) {
			foreach ( $item as $value ) {
				$values[] = $value;
			}
			$placeholders[] = '(' . implode( ', ', array_fill( 0, count( $item ), '%s' ) ) . ')';
		}

		$values_sql = implode( ', ', $placeholders );

		$sql = "INSERT INTO $table_name ($columns_sql) VALUES $values_sql";

		return $this->wpdb->query( $this->wpdb->prepare( $sql, $values ) );
	}

}
