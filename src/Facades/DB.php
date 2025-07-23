<?php

namespace AvelPress\Facades;

defined( 'ABSPATH' ) || exit;

/**
 * @method static \AvelPress\Database\Migrations\Migrator migrator()
 *
 * @see \AvelPress\Database\Database
 */
class DB extends Facade {

	protected static function getFacadeAccessor() {
		return 'database';
	}
}