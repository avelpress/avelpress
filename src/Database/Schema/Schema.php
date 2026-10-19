<?php

namespace AvelPress\Database\Schema;

defined( 'ABSPATH' ) || exit;

class Schema {
	public static function create( string $table, callable $callback ) {
		$blueprint = new Blueprint( $table );
		$blueprint->setCreate();
		$callback( $blueprint );
		$blueprint->run();
	}

	public static function drop( string $table ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter -- schema statement: the table, column and index name comes from the migration code, not from input.
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}$table" );
	}

	public static function table( string $table, callable $callback ) {
		$blueprint = new Blueprint( $table );
		$callback( $blueprint );
		$blueprint->run();
	}
}