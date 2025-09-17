<?php

namespace AvelPress\Facades;

defined( 'ABSPATH' ) || exit;

/**
 * @method static \AvelPress\Routing\Router get(string $uri, array|string|callable|null $action = null)
 * @method static \AvelPress\Routing\Router post(string $uri, array|string|callable|null $action = null)
 * @method static \AvelPress\Routing\Router put(string $uri, array|string|callable|null $action = null)
 * @method static \AvelPress\Routing\Router delete(string $uri, array|string|callable|null $action = null)
 * @method static \AvelPress\Routing\Router patch(string $uri, array|string|callable|null $action = null)
 * @method static \AvelPress\Routing\Router prefix(string $prefix)
 * @method static \AvelPress\Routing\Router guards(array $guards)
 * @method static \AvelPress\Routing\Router page(string $id, $options = [])
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