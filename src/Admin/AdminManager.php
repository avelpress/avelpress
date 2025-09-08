<?php

namespace AvelPress\Admin;

use AvelPress\Facades\Config;

defined( 'ABSPATH' ) || exit;

class AdminManager {

	private $hiddenPages = [];

	public function __construct() {

	}

	public function init() {
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

	public function maybeHideNotices() {
		if ( ! isset( $_GET['page'] ) ) {
			return false;
		}
		$current_page = $_GET['page'];
		foreach ( $this->hiddenPages as $page ) {
			if ( $current_page === $page ) {
				return true;
			}
		}
		return false;
	}

	public function addHiddenNoticesPage( $id ) {
		$this->hiddenPages[] = $id;
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


}