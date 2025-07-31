<?php

namespace AvelPress\Admin\Menu;

defined( 'ABSPATH' ) || exit;

class Submenu extends MenuItem {
	protected $parentMenu;

	public function __construct( $parentMenu = null ) {
		$this->parentMenu = $parentMenu;
	}

	public function getParentMenu() {
		return $this->parentMenu;
	}

	public function setParentMenu( $parentMenu ) {
		$this->parentMenu = $parentMenu;
		return $this;
	}

	public function getSlug() {
		return $this->slug . '&path=' . $this->getPath();
	}
}
