<?php

namespace AvelPress\Facades;

defined( 'ABSPATH' ) || exit;

/**
 * @method static \AvelPress\Database\Migrations\Migrator migrator()
 * @method static \AvelPress\Database\Eloquent\QueryBuilder table( string $table )
 *
 * @see \AvelPress\Database\Database
 */
class DB extends Facade {

	protected static function getFacadeAccessor() {
		return 'database';
	}
}