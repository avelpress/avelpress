<?php

namespace AvelPress\Facades;

defined( 'ABSPATH' ) || exit;

/**
 * @method static \AvelPress\Routing\RouterBuilder get(string $uri, array|string|callable|null $action = null)
 * @method static \AvelPress\Routing\RouterBuilder post(string $uri, array|string|callable|null $action = null)
 * @method static \AvelPress\Routing\RouterBuilder put(string $uri, array|string|callable|null $action = null)
 * @method static \AvelPress\Routing\RouterBuilder patch(string $uri, array|string|callable|null $action = null)
 * @method static \AvelPress\Routing\RouterBuilder prefix(string $prefix)
 * @method static \AvelPress\Routing\RouterBuilder guards(array $guards)
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