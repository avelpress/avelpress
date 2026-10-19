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
		$viewPath = $this->getViewPath( $view );
		if ( file_exists( $viewPath ) ) {
			// Views read their data as variables, as load_template() does; EXTR_SKIP keeps $this, $view, $data and
			// $viewPath from being replaced by a key of the same name.
			extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			include $viewPath;
		} else {
			throw new \Exception( esc_html( "View not found: {$view}" ) );
		}
	}

	protected function getViewPath( $view ) {
		$view = str_replace( '.', '/', $view );
		return $this->app->getBasePath() . "/resources/views/{$view}.php";
	}
}