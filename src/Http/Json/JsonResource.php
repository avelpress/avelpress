<?php

namespace AvelPress\Http\Json;

use AvelPress\Database\Eloquent\Model;

defined( 'ABSPATH' ) || exit;

class JsonResource {

	/**
	 * The resource instance.
	 *
	 * @var Model
	 */
	public $resource;

	/**
	 * The options for the resource.
	 *
	 * @var array
	 */
	public $options;

	/**
	 * Create a new resource instance.
	 *
	 * @param  Model  $resource
	 */
	public function __construct( $resource, $options = [] ) {
		$this->resource = $resource;
		$this->options = $options;
	}


	public static function collection( $collection, $options = [] ) {
		return new ResourceCollection( $collection, static::class, $options );
	}

	public function toArray() {
		return [];
	}

	public function __get( $name ) {
		if ( isset( $this->resource ) && isset( $this->resource[ $name ] ) ) {
			return $this->resource[ $name ];
		}

		return null;
	}

	public function __call( $method, $arguments ) {
		if ( isset( $this->resource ) && method_exists( $this->resource, $method ) ) {
			return call_user_func_array( [ $this->resource, $method ], $arguments );
		}

		throw new \BadMethodCallException( "Method {$method} does not exist on " . get_class( $this ) . " or its resource." );
	}
}