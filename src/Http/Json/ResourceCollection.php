<?php

namespace AvelPress\Http\Json;

use AvelPress\Support\Collection;
use AvelPress\Support\Paginator;

defined( 'ABSPATH' ) || exit;

class ResourceCollection {

	/**
	 * The resource that this resource collects.
	 *
	 * @var string|null
	 */
	public $collects = null;

	/**
	 * The collection of resources.
	 *
	 * @var Collection
	 */
	public $collection;

	/**
	 * Additional metadata for the resource collection.
	 *
	 * @var array
	 */
	protected $meta = [];

	/**
	 * If true, meta attributes will be merged at the top level of the array.
	 *
	 * @var bool
	 */
	public $mergeMeta = false;

	/**
	 * Constructs a new resource collection.
	 *
	 * @param Collection|Paginator $collection 
	 */
	public function __construct( $collection, $collects = null, $options = [] ) {

		if ( $collects )
			$this->collects = $collects;

		if ( $collection instanceof Paginator ) {
			$this->collection = $collection->getCollection();
			$this->meta = $collection->getAttributes();
			$this->mergeMeta = isset( $options['mergeMeta'] ) ? $options['mergeMeta'] : true;
		} else {
			$this->collection = $collection;
		}

		if ( isset( $options['mergeMeta'] ) && is_bool( $options['mergeMeta'] ) ) {
			$this->mergeMeta = $options['mergeMeta'];
		}
	}

	/**
	 * Get the collection of resources.
	 *
	 * @return array
	 */
	public function collection() {
		if ( $this->collects ) {
			$resourceClass = $this->collects;
			$resources = $this->collection->map( function ($item) use ($resourceClass) {
				return ( new $resourceClass( $item ) )->toArray();
			} );
		} else {
			$resources = $this->collection->toArray();
		}

		return $resources;
	}

	public function getMeta() {
		return $this->meta;
	}

	public function appendMeta( $data ) {
		if ( ! empty( $this->meta ) ) {
			if ( $this->mergeMeta ) {
				$data = array_merge( $data, $this->meta );
			} else {
				$data['meta'] = $this->meta;
			}
		}

		return $data;
	}

	/**
	 * Transform the resource collection into an array.
	 *
	 * @return array
	 */
	public function toArray() {
		$data = $this->appendMeta( [ 
			'data' => $this->collection(),
		] );

		return $data;
	}

	public function __get( $name ) {
		if ( $this->resource->keyExists( $name ) ) {
			return $this->resource[ $name ];
		}

		return null;
	}
}