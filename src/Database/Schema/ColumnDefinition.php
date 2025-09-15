<?php

namespace AvelPress\Database\Schema;

defined( 'ABSPATH' ) || exit;

class ColumnDefinition {

	/**
	 * Indicates if the column is nullable.
	 *
	 * @var bool
	 */
	protected $nullable = false;

	/**
	 * The name of the column.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The type of the column.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Indicates if the column is auto-incrementing.
	 *
	 * @var bool
	 */
	protected $auto_increment = false;

	/**
	 * Indicates if the column is unsigned.
	 *
	 * @var bool
	 */
	protected $unsigned = false;

	protected $primary = false;

	protected $unique = false;

	/**
	 * The default value of the column.
	 *
	 * @var mixed
	 */
	protected $default = null;

	/**
	 * The column that this column should be placed after.
	 *
	 * @var string|null
	 */
	protected $after = null;

	/**
	 * Create a new column definition instance.
	 *
	 * @param  array  $data
	 */
	public function __construct( $data ) {
		if ( empty( $data['type'] ) ) {
			throw new \InvalidArgumentException( 'Column type is required' );
		}

		if ( empty( $data['name'] ) ) {
			throw new \InvalidArgumentException( 'Column name is required' );
		}

		$this->auto_increment = $data['autoIncrement'] ?? false;
		$this->unsigned = $data['unsigned'] ?? false;
		$this->type = $data['type'];
		$this->name = $data['name'];
	}

	/**
	 * Check if the column is nullable.
	 *
	 * @return bool
	 */
	public function isNullable() {
		return $this->nullable;
	}

	public function nullable() {
		$this->nullable = true;

		return $this;
	}



	public function isUnsigned() {
		return $this->unsigned;
	}

	public function unsigned() {
		$this->unsigned = true;

		return $this;
	}

	public function isAutoIncrement() {
		return $this->auto_increment;
	}

	/**
	 * Check if the column is a primary key.
	 *
	 * @return bool
	 */
	public function isPrimary() {
		return $this->primary;
	}

	public function primary() {
		$this->primary = true;

		return $this;
	}

	public function default( $value ) {
		$this->default = $value;

		return $this;
	}

	public function unique() {
		$this->unique = true;

		return $this;
	}

	public function isUnique() {
		return $this->unique;
	}

	/**
	 * Place the column after another column.
	 *
	 * @param  string  $column
	 * @return $this
	 */
	public function after( $column ) {
		$this->after = $column;

		return $this;
	}

	/**
	 * Get the column that this column should be placed after.
	 *
	 * @return string|null
	 */
	public function getAfter() {
		return $this->after;
	}

	/**
	 * Get the type of the column.
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Get the name of the column.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get the default value of the column.
	 *
	 * @return mixed
	 */
	public function getDefault() {
		return $this->default;
	}

	public function getAttributes() {
		return [ 
			'type' => $this->getType(),
			'name' => $this->getName(),
			'nullable' => $this->isNullable(),
			'autoIncrement' => $this->isAutoIncrement(),
			'unsigned' => $this->isUnsigned(),
			'primary' => $this->isPrimary(),
			'after' => $this->getAfter(),
		];
	}
}