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
	 * Create a new resource instance.
	 *
	 * @param  Model  $resource
	 */
	public function __construct( $resource ) {
		$this->resource = $resource;
	}


	public static function collection( $collection, $options = [] ) {
		return new ResourceCollection( $collection, static::class, $options );
	}

	public function toArray() {
		return [];
	}

	public function __get( $name ) {
		if ( isset( $this->resource ) && $this->resource[ $name ] ) {
			return $this->resource[ $name ];
		}

		return null;
	}
}