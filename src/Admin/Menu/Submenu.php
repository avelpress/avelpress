<?php

namespace AvelPress\Admin\Menu;

defined('ABSPATH') || exit;

class Submenu extends MenuItem
{
	protected $parentMenu;

	public $callback;

	public function __construct($parentMenu = null)
	{
		$this->parentMenu = $parentMenu;
	}

	public function getParentMenu()
	{
		return $this->parentMenu;
	}

	public function setParentMenu($parentMenu)
	{
		$this->parentMenu = $parentMenu;
		return $this;
	}

	public function setCallback($callback)
	{
		$this->callback = $callback;
		return $this;
	}

	public function getCallback()
	{
		return $this->callback;
	}

	public function getSlug()
	{
		if ($this->getPath()) {
			return $this->slug . '&path=' . $this->getPath();
		}

		return $this->slug;
	}
}
