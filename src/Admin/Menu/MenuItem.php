<?php

namespace AvelPress\Admin\Menu;

defined( 'ABSPATH' ) || exit;

abstract class MenuItem {
	protected $slug;
	protected $title;
	protected $capability = 'manage_options';
	protected $icon;
	protected $path;

	protected $view;

	protected $scripts = [];

	public function slug( $slug ) {
		$this->slug = $slug;
		return $this;
	}

	public function title( $title ) {
		$this->title = $title;
		return $this;
	}

	public function capability( $capability ) {
		$this->capability = $capability;
		return $this;
	}

	public function icon( $icon ) {
		$this->icon = $icon;
		return $this;
	}

	public function path( $path ) {
		$this->path = $path;
		return $this;
	}

	public function getSlug() {
		return $this->slug;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getCapability() {
		return $this->capability;
	}

	public function getIcon() {
		return $this->icon;
	}

	public function getPath() {
		return $this->path;
	}

	/**
	 * Registred scripts to be enqueued for this menu item.
	 * 
	 * @param array $scripts
	 * 
	 * @return static
	 */
	public function scripts( $scripts ) {
		$this->scripts = $scripts;
		return $this;
	}

	public function toArray() {
		return [ 
			'slug' => $this->slug,
			'title' => $this->title,
			'capability' => $this->capability,
			'icon' => $this->icon,
			'view' => $this->view,
		];
	}
}
