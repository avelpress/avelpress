<?php
namespace AvelPress\Admin;

defined( 'ABSPATH' ) || exit;

class SubMenu extends Menu {
	protected $parent = null;

	/**
	 * Adds a submenu page.
	 *
	 * @param array $args [
	 *   'title' => (string) Submenu title (required),
	 *   'capability' => (string) Capability (required),
	 *   'slug' => (string) Menu slug (required),
	 *   'callback' => (callable) Callback function to render the submenu page (optional)
	 * ]
	 */
	public function __construct( string $parent, array $args ) {
		$this->parent = $parent;
		parent::__construct( $args );
	}

}