<?php

namespace AvelPress\Admin\Menu;

use AvelPress\Foundation\Application;

defined('ABSPATH') || exit;

abstract class MenuBuilder
{
	protected $menus = [];

	protected $groupCallback;

	protected $currentMenu;

	/**
	 * @var Application
	 */
	protected $app;

	abstract public function register();

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	public function add($slug, $title)
	{
		$menu = (new Menu())->slug($slug)->title($title);
		$this->menus[] = $menu;
		$this->currentMenu = $menu;
		return $menu;
	}

	public function addSubmenu($parentSlug, $slug, $title, $position = 99)
	{
		$submenu = (new Submenu($parentSlug))->slug($slug)->title($title);

		add_action('admin_menu', function () use ($submenu) {
			add_submenu_page(
				$submenu->getParentMenu(),
				$submenu->getTitle(),
				$submenu->getTitle(),
				$submenu->getCapability(),
				$submenu->getSlug(),
				$submenu->getCallback()
			);
		}, $position);

		return $submenu;
	}

	public function create()
	{
		foreach ($this->menus as $menu) {
			add_menu_page(
				$menu->getTitle(),
				$menu->getTitle(),
				$menu->getCapability(),
				$menu->getSlug(),
				function () use ($menu) {
					// Slince, this menu is rendered in routing, we don't need to render it here.
				},
				$menu->getIcon(),
				$menu->getPosition()
			);

			foreach ($menu->getSubmenus() as $submenu) {
				add_submenu_page(
					$menu->getSlug(),
					$submenu->getTitle(),
					$submenu->getTitle(),
					$submenu->getCapability(),
					$submenu->getSlug(),
					function () use ($submenu) {
						// Slince, this submenu is rendered in routing, we don't need to render it here.
					}
				);
			}

			remove_submenu_page($menu->getSlug(), $menu->getSlug());
		}
	}
}
