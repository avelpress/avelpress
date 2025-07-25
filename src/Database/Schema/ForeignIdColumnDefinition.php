<?php

namespace AvelPress\Database\Schema;

defined( 'ABSPATH' ) || exit;

class ForeignIdColumnDefinition extends ColumnDefinition {
	/**
	 * The schema builder blueprint instance.
	 *
	 * @var Blueprint
	 */
	protected $blueprint;

	/**
	 * Create a new foreign ID column definition.
	 *
	 * @param  Blueprint  $blueprint
	 * @param  array  $attributes
	 */
	public function __construct( Blueprint $blueprint, $attributes = [] ) {
		parent::__construct( $attributes );

		$this->blueprint = $blueprint;
	}

	/**
	 * Create a foreign key constraint on this column referencing the "id" column of the conventionally related table.
	 *
	 * @param  string|null  $table
	 * @param  string|null  $column
	 * @param  string|null  $indexName
	 * @return ForeignKeyDefinition
	 */
	public function constrained( $table = null, $column = null, $indexName = null ) {
		$table ??= $this->getTableByColumn( $this->name );
		$column ??= 'id';

		return $this->references( $column, $indexName )->on( $table );
	}

	public function getTableByColumn( $column ) {
		$parts = explode( '_', $column );

		return "{$parts[0]}s";
	}

	/**
	 * Specify which column this foreign ID references on another table.
	 *
	 * @param  string  $column
	 * @param  string  $indexName
	 * @return ForeignKeyDefinition
	 */
	public function references( $column, $indexName = null ) {
		return $this->blueprint->foreign( $this->name, $indexName )->references( $column );
	}
}