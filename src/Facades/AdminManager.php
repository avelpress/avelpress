<?php

namespace AvelPress\Facades;

defined( 'ABSPATH' ) || exit;

/**
 * @method static \AvelPress\Admin\Page addMenu(array $args)
 * @method static \AvelPress\Admin\Page addSubMenu(array $args)
 * 
 * @see \AvelPress\Admin\AdminManager
 */
class AdminManager extends Facade {
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() {
		return 'admin.manager';
	}
}