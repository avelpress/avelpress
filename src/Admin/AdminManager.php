<?php

namespace AvelPress\Admin;

defined( 'ABSPATH' ) || exit;

class AdminManager {

	/**
	 * @var Menu[]
	 */
	private $menuPages = [];

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'in_admin_header', [ $this, 'hide_notices' ], 99 );
	}

	/**
	 * Adds an admin menu page.
	 * @param array $args [
	 *   'title' => (string) Menu title (required),
	 *   'id' => (string) Menu slug (optional),
	 *   'icon' => (string) Icon (optional),
	 *   'capability' => (string) Capability (required),
	 *   'position' => (int) Position (optional)
	 *   'hide_notices' => (bool) Whether to hide notices on this page (optional, default: false)
	 * ]
	 */
	public function addMenu( array $args ) {
		$page = new Menu( $args );
		$this->menuPages[] = $page;
		return $page;
	}


	public function maybeHideNotices() {
		if ( ! isset( $_GET['page'] ) ) {
			return false;
		}
		$current_page = $_GET['page'];
		foreach ( $this->menuPages as $page ) {
			if (
				$page->getId() === $current_page &&
				$page->getHideNotices()
			) {
				return true;
			}
		}
		return false;
	}

	public function hide_notices() {
		if ( ! $this->maybeHideNotices() ) {
			return;
		}
		remove_all_actions( 'user_admin_notices' );
		remove_all_actions( 'admin_notices' );
	}

	public function silence_render() {
	}

	public function admin_menu() {
		foreach ( $this->menuPages as $menu ) {
			add_menu_page(
				$menu->getTitle(),
				$menu->getTitle(),
				$menu->getCapability(),
				$menu->getId(),
				[ $this, 'silence_render' ],
				$menu->getIcon() ?? 'dashicons-admin-generic',
				$menu->getPosition() ?? 60
			);

			foreach ( $menu->getSubmenus() as $submenu ) {
				add_submenu_page(
					$menu->getId(),
					$submenu->getTitle(),
					$submenu->getTitle(),
					$submenu->getCapability(),
					$submenu->getId(),
					[ $this, 'silence_render' ]
				);
			}

			if ( $menu->isFuse() ) {
				remove_submenu_page( $menu->getId(), $menu->getId() );
			}
		}
	}
}