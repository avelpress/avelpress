<?php

namespace AvelPress\View;

defined( 'ABSPATH' ) || exit;

class View {

	/**
	 * The application instance.
	 *
	 * @var \AvelPress\Foundation\Application
	 */
	protected $app;

	public function __construct( $app ) {
		$this->app = $app;
	}

	public function make( $view, $data = [] ) {
		$__avelpress_view_path = $this->getViewPath( $view );
		if ( file_exists( $__avelpress_view_path ) ) {
			// Views read their data as variables, as load_template() does. A `this` key would throw "Cannot re-assign
			// $this" and a key named like the path variable would change which file is included, so both are dropped.
			unset( $data['this'], $data['__avelpress_view_path'] );
			extract( $data ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			include $__avelpress_view_path;
		} else {
			throw new \Exception( esc_html( "View not found: {$view}" ) );
		}
	}

	protected function getViewPath( $view ) {
		$view = str_replace( '.', '/', $view );
		return $this->app->getBasePath() . "/resources/views/{$view}.php";
	}
}