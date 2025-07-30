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
			extract( $data );
			include $viewPath;
		} else {
			throw new \Exception( "View not found: {$view}" );
		}
	}

	protected function getViewPath( $view ) {
		$view = str_replace( '.', '/', $view );
		return $this->app->getBasePath() . "/resources/views/{$view}.php";
	}
}