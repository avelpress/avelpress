<?php

namespace AvelPress\Facades;

defined( 'ABSPATH' ) || exit;

/**
 * @method static \AvelPress\View\ViewServiceProvider make(string $view, array $data = [])
 *
 * @see \AvelPress\View\ViewServiceProvider
 */
class View extends Facade {
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() {
		return 'view';
	}
}