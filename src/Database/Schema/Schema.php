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
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %s', $table ) );
	}

	public static function table( string $table, callable $callback ) {
		$blueprint = new Blueprint( $table );
		$callback( $blueprint );
		$blueprint->run();
	}
}