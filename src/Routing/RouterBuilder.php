<?php

namespace AvelPress\Routing;


defined( 'ABSPATH' ) || exit;

class RouterBuilder {
	protected $instances = [];

	protected $groupDepth = 0;

	/**
	 * Get instance
	 *
	 * @return Router
	 */
	protected function getInstance() {
		if ( $this->groupDepth > 0 && ! empty( $this->instances ) ) {
			return end( $this->instances );
		} else {
			$instance = new Router( $this );
			$this->instances[] = $instance;
			return $instance;
		}
	}

	public function prefix( $prefix ) {
		$instance = $this->getInstance();
		$instance->prefix( $prefix );

		return $instance;
	}


	public function page( $id, $options = [] ) {
		$instance = $this->getInstance();

		$instance->page( $id, $options );

		return $instance;
	}

	public function get( $uri, $action = null ) {
		$instance = $this->getInstance();
		return $instance->addRoute( 'GET', $uri, $action );
	}

	public function post( $uri, $action = null ) {
		$instance = $this->getInstance();
		return $instance->addRoute( 'POST', $uri, $action );
	}

	public function put( $uri, $action = null ) {
		$instance = $this->getInstance();
		return $instance->addRoute( 'PUT', $uri, $action );
	}

	public function patch( $uri, $action = null ) {
		$instance = $this->getInstance();
		return $instance->addRoute( 'PATCH', $uri, $action );
	}

	public function delete( $uri, $action = null ) {
		$instance = $this->getInstance();
		return $instance->addRoute( 'DELETE', $uri, $action );
	}

	public function increaseGroupDepth() {
		$this->groupDepth++;
	}

	public function decreaseGroupDepth() {
		$this->groupDepth--;
	}

}