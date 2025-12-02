<?php

namespace AvelPress\Support;

use AvelPress\Database\Eloquent\Model;

defined( 'ABSPATH' ) || exit;

/**
 * Collection class
 * 
 * This class is responsible for handling collections
 * 
 * @since 1.0.0
 */
class Collection implements \ArrayAccess, \IteratorAggregate {

	public $items = [];

	/**
	 * Collection constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct( $items = [] ) {
		$this->items = $items;
	}

	/**
	 * Count the number of items in the collection.
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->items );
	}

	/**
	 * Push one or more items onto the end of the collection.
	 *
	 * @param  mixed  ...$values
	 * @return $this
	 */
	public function push( $values ) {

		if ( is_array( $values ) ) {
			foreach ( $values as $value ) {
				$this->items[] = $value;
			}
		} else {
			$this->items[] = $values;
		}

		return $this;
	}

	public function slice( $offset, $length = null ) {
		$slicedArray = array_slice( $this->items, $offset, $length );
		return new self( $slicedArray );
	}

	public function toArray() {
		$items = [];

		foreach ( $this->items as $key => $item ) {
			if ( $item instanceof Model ) {
				$items[ $key ] = $item->toArray();
			} else {
				$items[ $key ] = $item;
			}
		}

		return $items;
	}

	public function all() {
		return $this->items;
	}

	public function pluck( $value, $key = null ) {
		$results = [];

		foreach ( $this->items as $item ) {
			$itemValue = $this->data_get( $item, $value );

			if ( is_null( $key ) ) {
				$results[] = $itemValue;
			} else {
				$itemKey = $this->data_get( $item, $key );
				$results[ $itemKey ] = $itemValue;
			}
		}

		return new self( $results );
	}

	/**
	 * Map the collection using a callback.
	 *
	 * @param  callable(Model)  $callback
	 *
	 * @return array
	 */
	public function map( $callback ) {
		$keys = array_keys( $this->items );
		$items = array_map( $callback, $this->items, $keys );
		return array_combine( $keys, $items );
	}

	public function filter( $callback = null ) {
		$items = array_filter( $this->items, $callback );
		return new self( $items );
	}


	/**
	 * Merge the collection with the given items.
	 *
	 * @param  Collection|array $collection
	 * 
	 * @return Collection
	 */
	public function merge( $collection ) {
		if ( $collection instanceof self ) {
			$this->items = array_merge( $this->items, $collection->all() );
		} elseif ( is_array( $collection ) ) {
			$this->items = array_merge( $this->items, $collection );
		}
		return $this;
	}

	public function unique( $key ) {
		$uniqueItems = [];
		$seenKeys = [];

		foreach ( $this->items as $item ) {
			$itemKey = $this->data_get( $item, $key );

			if ( ! in_array( $itemKey, $seenKeys, true ) ) {
				$seenKeys[] = $itemKey;
				$uniqueItems[] = $item;
			}
		}

		return new self( $uniqueItems );
	}

	/**
	 * Get the sum of a given key.
	 *
	 * @param  string|callable  $key
	 * @return float|int
	 */
	public function sum( $key ) {
		$total = 0;

		foreach ( $this->items as $item ) {
			if ( is_callable( $key ) ) {
				$value = $key( $item );
			} else {
				$value = $this->data_get( $item, $key );
			}

			if ( is_numeric( $value ) ) {
				$total += $value;
			}
		}

		return $total;
	}

	public function data_get( $target, $key, $default = null ) {
		if ( is_null( $key ) ) {
			return $target;
		}

		foreach ( explode( '.', $key ) as $segment ) {
			if ( is_array( $target ) ) {
				if ( ! array_key_exists( $segment, $target ) ) {
					return $default;
				}

				$target = $target[ $segment ];
			} elseif ( $target instanceof \ArrayAccess ) {
				if ( ! isset( $target[ $segment ] ) ) {
					return $default;
				}

				$target = $target[ $segment ];
			} elseif ( is_object( $target ) ) {
				if ( ! isset( $target->{$segment} ) ) {
					return $default;
				}

				$target = $target->{$segment};
			} else {
				return $default;
			}
		}

		return $target;
	}


	public function firstWhere( $key, $value ) {
		foreach ( $this->items as $item ) {
			if ( $item->$key === $value ) {
				return $item;
			}
		}

		return null;
	}

	public function where( $key, $value ) {
		$items = [];

		foreach ( $this->items as $item ) {
			if ( isset( $item->$key ) && $item->$key === $value ) {
				$items[] = $item;
			}
		}

		return $items;
	}

	public function isEmpty() {
		return empty( $this->items );
	}

	public function first() {
		return $this->items[0] ?? null;
	}

	public function contains( $key, $value ) {
		foreach ( $this->items as $item ) {
			if ( isset( $item->$key ) && $item->$key === $value ) {
				return true;
			}
		}

		return false;
	}

	public function sortByDesc( $key ) {
		$items = $this->items;

		usort( $items, function ( $a, $b ) use ( $key ) {
			return $b->$key <=> $a->$key;
		} );

		return new self( $items );
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @param  mixed  $key
	 * @return bool
	 */
	public function offsetExists( $key ): bool {
		return isset( $this->items[ $key ] );
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @param  mixed  $key
	 * @return mixed
	 */
	#[\ReturnTypeWillChange ]
	public function offsetGet( $key ) {
		return $this->items[ $key ];
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @param  mixed|null  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function offsetSet( $key, $value ): void {
		if ( $key === null ) {
			$this->items[] = $value;
		} else {
			$this->items[ $key ] = $value;
		}
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param  mixed  $key
	 * @return void
	 */
	public function offsetUnset( $key ): void {
		unset( $this->items[ $key ] );
	}

	/**
	 * Get an iterator for the items.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator(): \ArrayIterator {
		return new \ArrayIterator( $this->items );
	}
}