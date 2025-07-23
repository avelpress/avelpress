<?php

namespace AvelPress\Facades;

defined( 'ABSPATH' ) || exit;

/**
 * @method static \AvelPress\Routing\Router get(string $uri, array|string|callable|null $action = null)
 * @method static \AvelPress\Routing\Router prefix(string $prefix)
 * @method static \AvelPress\Routing\Router guards(array $guards)
 *
 * @see \AvelPress\Routing\Router
 */
class Route extends Facade {
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() {
		return 'router';
	}
}