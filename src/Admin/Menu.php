<?php
namespace AvelPress\Admin;

defined( 'ABSPATH' ) || exit;

class Menu {

	/**
	 * The page ID.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The page title.
	 * @var string
	 */
	protected $title;

	/**
	 * The page capability.
	 * @var string
	 */
	protected $capability;

	/**
	 * The page icon.
	 * @var string|null
	 */
	protected $icon;

	/**
	 * The page position.
	 * @var int|null
	 */
	protected $position;

	/**
	 * Whether to hide notices on this page.
	 * @var bool
	 */
	protected $hide_notices = false;

	protected $fuse = false;

	protected $submenus = [];

	/**
	 * Adds an admin menu page.
	 * @param array $args [
	 *   'title' => (string) Menu title (required),
	 *   'id' => (string) Menu slug (optional),
	 *   'icon' => (string) Icon (optional),
	 *   'capability' => (string) Capability (required),
	 *   'position' => (int) Position (optional)
	 *   'hide_notices' => (bool) Whether to hide notices on this page (optional, default: false)
	 *   'fuse' => (bool) Whether to fuse the submenu with the parent menu (optional, default: false)
	 * ]
	 */
	public function __construct( array $args ) {
		if ( empty( $args['title'] ) || empty( $args['capability'] ) ) {
			throw new \InvalidArgumentException( 'Title and capability are required' );
		}
		$args['id'] = ! empty( $args['id'] ) ? $args['id'] : sanitize_title( $args['title'] );

		$this->id = $args['id'];
		$this->title = $args['title'];
		$this->capability = $args['capability'];
		$this->icon = $args['icon'] ?? null;
		$this->position = $args['position'] ?? null;
		$this->hide_notices = ! empty( $args['hide_notices'] );
		$this->fuse = ! empty( $args['fuse'] );
	}

	/**
	 * Adds a submenu page.
	 *
	 * @param array $args [
	 *   'title' => (string) Submenu title (required),
	 *   'capability' => (string) Capability (required),
	 *   'slug' => (string) Menu slug (required),
	 *   'callback' => (callable) Callback function to render the submenu page (optional)
	 *   'fuse' => (bool) Whether to fuse the submenu with the parent menu (optional, default: false)
	 * ]
	 */
	public function addSubmenu( $args ) {
		$submenu = new SubMenu( $this->id, $args );
		$this->submenus[] = $submenu;
		return $submenu;
	}


	/**
	 * Get the submenu pages.
	 *
	 * @return SubMenu[]
	 */
	public function getSubmenus() {
		return $this->submenus;
	}

	/**
	 * Get the page ID.
	 *
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get the page title.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Get the page capability.
	 *
	 * @return string
	 */
	public function getCapability() {
		return $this->capability;
	}

	/**
	 * Get the page icon.
	 *
	 * @return string|null
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * Get the page position.
	 *
	 * @return int|null
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * Check if notices should be hidden on this page.
	 *
	 * @return bool
	 */
	public function getHideNotices() {
		return $this->hide_notices;
	}

	public function isFuse() {
		return $this->fuse;
	}

	/**
	 * Convert the page object to an array.
	 *
	 * @return array
	 */
	public function toArray() {
		return [ 
			'id' => $this->id,
			'title' => $this->title,
			'capability' => $this->capability,
			'icon' => $this->icon,
			'position' => $this->position,
			'hide_notices' => $this->hide_notices,
		];
	}

	/**
	 * Magic getter for dynamic property access.
	 *
	 * @param string $name
	 * @return mixed|null
	 */
	public function __get( $name ) {
		return $this->$name ?? null;
	}

}