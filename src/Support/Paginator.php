<?php

namespace AvelPress\Support;

use AvelPress\Support\Collection;

defined( 'ABSPATH' ) || exit;

class Paginator {

	/**
	 * The total number of items before slicing.
	 *
	 * @var int
	 */
	protected $total;

	/**
	 * The last available page.
	 *
	 * @var int
	 */
	protected $lastPage;

	/**
	 * The number of items per page.
	 *
	 * @var int
	 */
	protected $perPage;

	/**
	 * The current page.
	 *
	 * @var int
	 */
	protected $currentPage;

	/**
	 * The items for the current page.
	 *
	 * @var Collection
	 */
	protected $items;

	/**
	 * Paginator constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct( $items, $total, $perPage, $currentPage = null, $options = [] ) {
		$this->options = $options;

		foreach ( $options as $key => $value ) {
			$this->{$key} = $value;
		}

		$this->total = $total;
		$this->perPage = (int) $perPage;
		$this->lastPage = max( (int) ceil( $total / $perPage ), 1 );
		$this->currentPage = $this->setCurrentPage( $currentPage );
		$this->items = $items instanceof Collection ? $items : new Collection( $items );
	}

	/**
	 * Get the current page for the request.
	 *
	 * @param  int  $currentPage
	 * @return int
	 */
	protected function setCurrentPage( $currentPage ) {
		return $this->isValidPageNumber( $currentPage ) ? (int) $currentPage : 1;
	}


	/**
	 * Determine if the given value is a valid page number.
	 *
	 * @param  int  $page
	 * @return bool
	 */
	protected function isValidPageNumber( $page ) {
		return $page >= 1 && filter_var( $page, FILTER_VALIDATE_INT ) !== false;
	}

	public function getCollection() {
		return $this->items;
	}

	public function getAttributes() {
		return [ 
			'total' => $this->total,
			'per_page' => $this->perPage,
			'current_page' => $this->currentPage,
			'last_page' => $this->lastPage,
		];
	}

	public function toArray() {
		return array_merge(
			$this->getAttributes(),
			[ 
				'data' => $this->items->toArray(),
			]
		);
	}
}