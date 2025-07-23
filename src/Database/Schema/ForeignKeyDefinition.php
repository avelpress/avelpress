<?php

namespace AvelPress\Database\Schema;

defined( 'ABSPATH' ) || exit;

class ForeignKeyDefinition {

	/**
	 * The schema builder blueprint instance.
	 *
	 * @var Blueprint
	 */
	protected $blueprint;

	/**
	 * The attributes of the foreign key.
	 *
	 * @var array
	 */
	protected $attributes;

	public function __construct( Blueprint $blueprint, $attributes = [] ) {
		$this->blueprint = $blueprint;
		$this->attributes = $attributes;
	}


	public function references( $column ) {
		$this->attributes['references'] = $column;

		return $this;
	}

	public function on( $table ) {
		$this->attributes['on'] = $table;
		return $this;
	}

	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * Specify the action to take when the referenced row is deleted.
	 *
	 * @param  string  $action 'cascade', 'set null', 'restrict', etc.
	 * 
	 * @return $this
	 */
	public function onDelete( $action ) {
		$this->attributes['onDelete'] = $action;
		return $this;
	}

	public function getForeignKeySql() {
		global $wpdb;

		$column = $this->attributes['name'] ?? null;
		$references = $this->attributes['references'] ?? null;
		$table = $this->attributes['on'] ?? null;
		$onDelete = $this->attributes['onDelete'] ?? null;

		if ( ! $column || ! $references || ! $table ) {
			return '';
		}

		$constraintName = sprintf(
			'%s_%s_foreign',
			$wpdb->prefix . $this->blueprint->getTable(),
			$column
		);

		$sql = sprintf(
			'CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s(%s)',
			$constraintName,
			$column,
			$wpdb->prefix . $table,
			$references
		);

		if ( $onDelete ) {
			$sql .= ' ON DELETE ' . strtoupper( $onDelete );
		}

		return $sql;
	}
}