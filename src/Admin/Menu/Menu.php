<?php
namespace AvelPress\Admin\Menu;

defined( 'ABSPATH' ) || exit;

class Menu extends MenuItem {
	protected $submenus = [];

	public function addSubmenu( $title, $slug = null ) {
		$submenu = ( new Submenu( $this ) )->slug( $slug ?? $this->getSlug() )->title( $title );
		$this->submenus[] = $submenu;
		return $submenu;
	}

	public function getSubmenus() {
		return $this->submenus;
	}

	public function hasSubmenus() {
		return ! empty( $this->submenus );
	}
}