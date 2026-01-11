<?php

namespace AvelPress\Database\Eloquent;

use AvelPress\AvelPress;
use AvelPress\Contracts\Database\Eloquent\CastsAttributes;
use AvelPress\Database\Database;
use AvelPress\Database\Eloquent\QueryBuilder;
use AvelPress\Database\Eloquent\SoftDeletes;
use AvelPress\Database\Eloquent\Relations\BelongsTo;
use AvelPress\Database\Eloquent\Relations\HasMany;
use AvelPress\Database\Eloquent\Relations\HasOne;
use AvelPress\Database\Eloquent\Casts\Attribute;
use AvelPress\Support\Collection;
use AvelPress\Support\Paginator;
use AvelPress\Utils\Reflection;

defined( 'ABSPATH' ) || exit;

abstract class Model implements \ArrayAccess {
	private static $instances = [];

	protected $primaryKey = 'id';

	protected $fillable = [];

	/**
	 * Prefix
	 * 
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $table;

	protected $foregin_key;

	protected $data = [];

	private $was_retrieved = false;

	public $timestamps = false;

	protected $casts = [];

	private $update_data = [];

	/**
	 * Database instance
	 * 
	 * @var Database
	 */
	protected $db;
	/**
	 * Database instance
	 * 
	 * @return Model
	 */
	public static function getInstance() {
		$class = get_called_class();
		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class( [] );
		}
		return self::$instances[ $class ];
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * 
	 * @param array $data
	 * @param string|null $table
	 */
	public function __construct( $data = [], $table = null ) {
		$this->db = AvelPress::app( 'database' );
		$this->table = $table ?? self::getFullTableName();
		$this->foregin_key = $this->modelToForeign( get_called_class() );

		$this->data = $data;

		foreach ( $data as $key => $value ) {
			$this->data[ $key ] = $this->getAttributeValue( $key, $value );
		}
	}

	public static function getFullTableName() {
		$class = get_called_class();
		$defaultTableName = Reflection::getDefaultValue( $class, 'table' );

		if ( ! isset( $defaultTableName ) || empty( $defaultTableName ) ) {
			return Database::getTableName( self::modelToTable( get_called_class() ), self::getPrefix() );
		} else {
			return Database::getTableName( $defaultTableName, self::getPrefix() );
		}
	}

	public static function modelToTable( $model ) {
		$reflect = new \ReflectionClass( $model );
		$table_name_underscored = preg_replace( '/(?<!^)([A-Z])/', '_$1', $reflect->getShortName() );
		return strtolower( $table_name_underscored ) . 's';
	}

	private function modelToForeign( $model ) {
		$reflect = new \ReflectionClass( $model );
		$table_name_underscored = preg_replace( '/(?<!^)([A-Z])/', '_$1', $reflect->getShortName() );
		return strtolower( $table_name_underscored );
	}

	public function getForeignKey() {
		return $this->foregin_key;
	}

	public static function getForeignKeyStatic() {
		$reflect = new \ReflectionClass( get_called_class() );
		$table_name_underscored = preg_replace( '/(?<!^)([A-Z])/', '_$1', $reflect->getShortName() );
		return strtolower( $table_name_underscored ) . '_id';
	}

	/**
	 * Query
	 *
	 * @since 1.0.0
	 * 
	 * @return QueryBuilder
	 */
	public static function query() {
		$instance = self::getInstance();
		$queryBuilder = new QueryBuilder( $instance );
		return $queryBuilder;
	}


	public function getQueryBuilder() {
		$queryBuilder = new QueryBuilder( $this );
		return $queryBuilder;
	}

	/**
	 * Add relations to a QueryBuilder
	 *
	 * @param string|array $relation_name
	 * @return QueryBuilder
	 */
	public static function with( $relation_name ) {
		return self::query()->with( $relation_name );
	}


	/**
	 * Find a record by id
	 * 
	 * @param int $id
	 * 
	 * @return Model|null
	 */
	public static function find( $id ) {
		return self::query()->find( $id );
	}

	/**
	 * Get all records from the database
	 * 
	 * @since 1.0.0
	 * 
	 * @return Collection
	 */
	public static function all() {
		$instance = self::getInstance();
		$builder = new QueryBuilder( $instance );
		$result = $builder->get();
		return $result;
	}

	/**
	 * Get count for all records from the table
	 * 
	 * @since 1.0.3
	 * 
	 * @return int Database query results.
	 */
	public static function count() {
		$instance = self::getInstance();
		$builder = new QueryBuilder( $instance );
		$result = $builder->count();
		return $result;
	}

	/**
	 * Where
	 *
	 * @since 1.0.0
	 * @since 1.0.2
	 * 
	 * @param mixed $column Column name
	 * @param mixed $operator Value or Operator
	 * @param mixed $value Valor or null
	 * 
	 * @return QueryBuilder
	 */
	public static function where( $column, $operator = null, $value = null ) {
		$instance = self::getInstance();
		$builder = new QueryBuilder( $instance );
		$builder->where( $column, $operator, $value );
		return $builder;
	}

	/**
	 * Where Has
	 *
	 * @since 1.0.0
	 * 
	 * @param string $relation
	 * @param callable(QueryBuilder $query) $callback
	 * 
	 * @return QueryBuilder
	 */
	public static function whereHas( $relation, $callback ) {
		$instance = self::getInstance();
		$builder = new QueryBuilder( $instance );
		$builder->whereHas( $relation, $callback );
		return $builder;
	}

	/**
	 * Select
	 *
	 * @since 1.0.0
	 * 
	 * @param string|array $columns
	 * 
	 * @return QueryBuilder
	 */
	public static function select( $columns ) {
		$instance = self::getInstance();
		$builder = new QueryBuilder( $instance );
		$builder->select( $columns );
		return $builder;
	}

	/**
	 * Where In
	 *
	 * @since 1.0.2
	 * 
	 * @param string $column Name of the column.
	 * @param array $value Array values of the column.
	 * 
	 * @return QueryBuilder
	 */
	public static function whereIn( $column, $values = [] ) {
		$instance = self::getInstance();
		$builder = new QueryBuilder( $instance );
		$builder->whereIn( $column, $values );
		return $builder;
	}

	/**
	 * Get Prefix
	 * 
	 * Add Compatibility with PHP 7.4
	 * PHP >= 8 use  getDefaultValue
	 *
	 * @since 1.0.0
	 * 
	 * @return string
	 */
	public static function getPrefix() {
		$class = get_called_class();
		return Reflection::getDefaultValue( $class, 'prefix', '' );
	}

	public static function usesTimestamps() {
		$class = get_called_class();
		return Reflection::getDefaultValue( $class, 'timestamps', false );
	}

	/**
	 * Create a record
	 *
	 * @param array $columns_values
	 * @return Model|false
	 */
	public static function create( $columns_values ) {
		$instance = self::getInstance();

		$tableName = $instance->getTableName();

		foreach ( $columns_values as $key => $value ) {
			$columns_values[ $key ] = $instance->setAttributeValue( $key, $value, $columns_values );
		}

		if ( self::usesTimestamps() ) {
			$columns_values['created_at'] = current_time( 'mysql' );
			$columns_values['updated_at'] = current_time( 'mysql' );
		}

		$inserted = Database::insert( $tableName, $columns_values );
		if ( $inserted ) {
			$class = get_called_class();
			$newClass = new $class(
				array_merge( $columns_values, [ 'id' => $inserted ] )
			);

			$newClass->setWasRetrieved( true );

			return $newClass;
		}
		return false;
	}


	/**
	 * Update
	 *
	 * @since 1.0.0
	 * 
	 * @param array $columns_values
	 * @param array $where_values
	 * 
	 * @return int|bool	
	 */
	public static function update( array $columns_values, array $where_values ) {
		$instance = self::getInstance();
		return $instance->db->update( $instance->table, $columns_values, $where_values );
	}

	public static function updateOrCreate( array $where_values, array $columns_values ) {
		$instance = self::getInstance();
		$existing = $instance->where( $where_values )->first();

		if ( $existing ) {
			return $existing->update( $columns_values, $where_values );
		} else {
			$data = array_merge( $where_values, $columns_values );
			return self::create( $data );
		}
	}

	public function fill( array $data ) {
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, $this->fillable ) || empty( $this->fillable ) ) {
				$this->update_data[ $key ] = $value;
			}
		}
	}

	/**
	 * Save the model to the database.
	 *
	 * @return false|int
	 */
	public function save() {
		if ( $this->wasRetrieved() ) {
			$data = $this->update_data;
			$id = $this->data[ $this->primaryKey ];
			$queryBuilder = new QueryBuilder( $this );
			$result = $queryBuilder->where( $this->primaryKey, $id )->update( $data );
			if ( $result ) {
				$this->data = array_merge( $this->data, $this->update_data );
			}
			return $result;
		} else {
			$result = $this->db->insert( $this->table, $this->data );
			if ( $result ) {
				$this->data[ $this->primaryKey ] = $result;
			}
			return $result;
		}
	}

	/**
	 * Delete the model from the database.
	 *
	 * @return bool|int
	 */
	public function delete() {
		if ( $this->wasRetrieved() ) {
			$data = $this->data;
			$id = $data[ $this->primaryKey ];
			$queryBuilder = new QueryBuilder( $this );
			return $queryBuilder->where( $this->primaryKey, $id )->delete();
		}
		return false;
	}

	public function relationLoaded( $relation ) {
		return isset( $this->data[ $relation ] );
	}

	public function setAttribute( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Create Many
	 *
	 * @since 1.0.0
	 * @param array $columns_values
	 * @return int|bool	
	 */
	public static function createMany( $columns_values ) {
		$instance = self::getInstance();
		return $instance->db->insert_multiple( $instance->table, $columns_values );
	}

	/**
	 * One to one relationship
	 *
	 * @since 1.0.0
	 * 
	 * @param string $related_class
	 * 
	 * @return HasOne
	 */
	public function hasOne( string $related_class ): HasOne {
		//TODO: See has Many fix it
		$foreignKey = $this->modelToForeign( $related_class );
		return new HasOne( $this, $related_class, "{$foreignKey}_id", "id" );
	}

	/**
	 * Belongs to relationship
	 *
	 * @since 1.0.0
	 * @param string $related_class
	 * 
	 * @return BelongsTo
	 */
	public function belongsTo( $related_class ): BelongsTo {
		//TODO: See has Many fix it
		$foreignKey = $this->modelToForeign( $related_class );
		return new BelongsTo( $this, $related_class, "{$foreignKey}_id", "id" );
	}

	/**
	 * HasMany to relationship
	 *
	 * @since 1.0.2
	 * @param string $related_class
	 * 
	 * @return HasMany
	 */
	public function hasMany( $related_class ): HasMany {
		//$foreignKey = $this->modelToForeign( $related_class );
		return new HasMany( $this, $related_class, "{$this->foregin_key}_id", "id" );
	}


	/**
	 * Get table name
	 *
	 * @param int $id
	 * @return string
	 */
	public function getTableName() {
		return $this->table;
	}

	/**
	 * Get primary key
	 *
	 * @since 1.0.2
	 * 
	 * @return string
	 */
	public function getPrimaryKey() {
		return $this->primaryKey;
	}


	/**
	 * Get table name
	 *
	 * @param int $id
	 * @return string
	 */
	static public function getTable() {
		$instance = self::getInstance();
		return $instance->table;
	}

	/**
	 * Get database
	 *
	 * @param int $id
	 * @return Database
	 */
	public function getDatabase() {
		return $this->db;
	}


	public function trashed() {
		return in_array( SoftDeletes::class, class_uses( $this ) );
	}

	public static function isTrashed() {
		return in_array( SoftDeletes::class, class_uses( get_called_class() ) );
	}

	public function toArray() {
		$result = [];
		foreach ( $this->data as $key => $value ) {
			if ( $value instanceof Collection ) {
				$result[ $key ] = $value->map( function ( $item ) {
					return $item instanceof Model ? $item->toArray() : $item;
				} );
			} elseif ( $value instanceof Model ) {
				$result[ $key ] = $value->toArray();
			} else {
				$result[ $key ] = $value;
			}
		}
		return $result;
	}

	public function keyExists( $key ) {
		return array_key_exists( $key, $this->data );
	}

	/**
	 * Get the attribute method name for a given attribute.
	 *
	 * @param string $key
	 * @return string|null
	 */
	protected function getAttributeMethod( $key ) {
		// Convert snake_case to camelCase for method name
		$method = lcfirst( str_replace( '_', '', ucwords( $key, '_' ) ) );
		return method_exists( $this, $method ) ? $method : null;
	}

	/**
	 * Get attribute value using Attribute accessor if available
	 *
	 * @param string $key
	 * @return mixed
	 */
	private function getAttributeValue( $key, $value = null ) {

		$attributeMethod = $this->getAttributeMethod( $key );
		if ( $attributeMethod && method_exists( $this, $attributeMethod ) ) {
			$attribute = $this->$attributeMethod();
			if ( $attribute instanceof Attribute && $attribute->get ) {
				return call_user_func( $attribute->get, $value !== null ? $value : ( $this->data[ $key ] ?? null ), $this->data );
			}
		}


		$casts = $this->casts();
		if ( array_key_exists( $key, $casts ) ) {
			$cast = $casts[ $key ];
			if ( is_string( $cast ) ) {
				switch ( $cast ) {
					case 'boolean':
					case 'bool':
						$cast = \AvelPress\Database\Eloquent\Casts\BooleanCast::class;
						break;
					case 'array':
						$cast = \AvelPress\Database\Eloquent\Casts\ArrayCast::class;
						break;
				}

				$cast = new $cast;
			}
			if ( $cast instanceof CastsAttributes ) {
				return $cast->get( $this, $key, $value, $this->data );
			}
		}

		return $value;
	}

	/**
	 * Set attribute value using Attribute mutator if available
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 */
	private function setAttributeValue( $key, $value, $data = [] ) {
		$attributeMethod = $this->getAttributeMethod( $key );

		if ( $attributeMethod && method_exists( $this, $attributeMethod ) ) {
			$attribute = $this->$attributeMethod();
			if ( $attribute instanceof Attribute && $attribute->set ) {
				return call_user_func( $attribute->set, $value, array_merge( $data, $this->data ) );
			}
		}

		$casts = $this->casts();

		if ( array_key_exists( $key, $casts ) ) {
			$cast = $casts[ $key ];
			if ( is_string( $cast ) ) {
				switch ( $cast ) {
					case 'boolean':
					case 'bool':
						$cast = \AvelPress\Database\Eloquent\Casts\BooleanCast::class;
						break;
					case 'array':
						$cast = \AvelPress\Database\Eloquent\Casts\ArrayCast::class;
						break;
				}
				$cast = new $cast;
			}
			if ( $cast instanceof CastsAttributes ) {
				return $cast->set( $this, $key, $value, $this->data );
			}
		}

		return $value;
	}

	protected function casts(): array {
		return $this->casts ?? [];
	}

	public function __get( $name ) {
		if ( $this->keyExists( $name ) ) {
			return $this->data[ $name ];
		}

		$value = $this->getAttributeValue( $name );

		if ( $value !== null ) {
			return $value;
		}

		return $this->$name;
	}

	/**
	 * Dynamically set an attribute on the model.
	 *
	 * @param  string  $name
	 * @param  mixed  $value
	 * 
	 * @return void
	 */
	public function __set( $name, $value ) {
		$value = $this->setAttributeValue( $name, $value );

		if ( $this->wasRetrieved() ) {
			$this->update_data[ $name ] = $value;
		} else {
			$this->data[ $name ] = $value;
		}
	}

	public function __isset( $name ) {
		return isset( $this->data[ $name ] );
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @param  mixed  $key
	 * @return bool
	 */
	public function offsetExists( $key ): bool {
		return isset( $this->data[ $key ] );
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @param  mixed  $key
	 * @return mixed
	 */
	#[\ReturnTypeWillChange ]
	public function offsetGet( $key ) {
		if ( $this->keyExists( $key ) ) {
			return $this->data[ $key ];
		}

		$value = $this->getAttributeValue( $key );

		if ( $value !== null ) {
			return $value;
		}

		return null;
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @param  mixed|null  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function offsetSet( $key, $value ): void {
		if ( $key !== null ) {
			$value = $this->setAttributeValue( $key, $value );
		}

		if ( $this->wasRetrieved() ) {
			$this->update_data[ $key ] = $value;
		} else {
			if ( $key === null ) {
				$this->data[] = $value;
			} else {
				$this->data[ $key ] = $value;
			}
		}
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param  mixed  $key
	 * @return void
	 */
	public function offsetUnset( $key ): void {
		unset( $this->data[ $key ] );
	}

	public function wasRetrieved() {
		return $this->was_retrieved;
	}

	public function setWasRetrieved( $was_retrieved ) {
		$this->was_retrieved = $was_retrieved;
	}

	/**
	 * Execute a callback if the condition is true, otherwise return a default value.
	 *
	 * @param mixed $condition
	 * @param callable(QueryBuilder $query) $callback
	 * 
	 * @return QueryBuilder|mixed
	 */
	public static function when( $condition, $callback ) {
		if ( isset( $condition ) && ! empty( $condition ) && $condition !== false ) {
			return $callback( self::query(), $condition );
		}
		return self::query();
	}

	/**
	 * Paginate the results.
	 * 
	 * @param mixed $per_page
	 * 
	 * @return Paginator
	 */
	public static function paginate( $per_page ) {
		$builder = self::query();
		return $builder->paginate( $per_page );
	}
}