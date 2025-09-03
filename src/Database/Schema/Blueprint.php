<?php

namespace AvelPress\Database\Schema;

use AvelPress\Support\Collection;

defined( 'ABSPATH' ) || exit;

class Blueprint {

	/**
	 * The table the blueprint describes.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The columns that should be added to the table.
	 *
	 * @var ColumnDefinition[]
	 */
	protected $columns = [];

	/**
	 * The command that should be executed on the table.
	 *
	 * @var array
	 */
	protected $command = 'alter';

	/**
	 * The commands that should be executed on the table.
	 *
	 * @var array
	 */
	protected $commands = [];

	public function __construct( $table ) {
		$this->table = $table;
	}

	/**
	 * Create a new auto-incrementing big integer (8-byte) column on the table.
	 *
	 * @param  string  $column
	 * 
	 * @return ColumnDefinition
	 */
	public function id( $column = 'id' ) {
		return $this->bigIncrements( $column )->primary();
	}

	/**
	 * Indicate that the table needs to be created.
	 *
	 */
	public function setCreate() {
		$this->command = 'create';
	}

	/**
	 * 
	 * @param ColumnDefinition  $column
	 *
	 */
	private function generateSingleColumnSql( $column ) {
		$type = $column->getType();
		$name = $column->getName();
		$sql = "`$name`";

		switch ( $type ) {
			case 'bigInteger':
				$sql .= ' bigint(20)';
				break;
			case 'integer':
				$sql .= ' int(11)';
				break;
			case 'boolean':
				$sql .= ' tinyint(1)';
				break;
			case 'string':
				$length = $column->length ?? 255;
				$sql .= " varchar($length)";
				break;
			case 'timestamp':
				$sql .= ' timestamp';
				// Fractional seconds support can be added here if needed
				break;
			case 'dateTime':
				$sql .= ' datetime';
				// Fractional seconds support can be added here if needed
				break;
			case 'text':
				$sql .= ' text';
				break;
			default:
				$sql .= ' text';
		}

		if ( in_array( $type, [ 'integer', 'bigInteger' ], true ) && $column->isUnsigned() ) {
			$sql .= ' unsigned';
		}

		$sql .= $column->isNullable() ? ' DEFAULT NULL' : " NOT NULL";

		if ( ! $column->isNullable() && $column->getDefault() !== null ) {
			$sql .= " DEFAULT '" . esc_sql( $column->getDefault() ) . "'";
		}

		if ( $column->isAutoIncrement() && in_array( $type, [ 'bigInteger', 'unsignedBigInteger', 'bigIncrements' ], true ) ) {
			$sql .= ' AUTO_INCREMENT';
		}

		return $sql;
	}

	private function prepareColumns() {
		$columnsSql = [];
		$primaryKey = [];
		foreach ( $this->columns as $column ) {
			$columnsSql[] = $this->generateSingleColumnSql( $column );

			if ( $column->isPrimary() ) {
				$primaryKey[] = $column->getName();
			}
		}

		if ( ! empty( $primaryKey ) ) {
			$columnsSql[] = "PRIMARY KEY (" . implode( ', ', $primaryKey ) . ")";
		}

		if ( ! empty( $this->commands ) ) {
			foreach ( $this->commands as $command ) {
				if ( $command instanceof ForeignKeyDefinition ) {
					$columnsSql[] = $command->getForeignKeySql();
				}
			}
		}

		return $columnsSql;
	}

	private function tableExists( $tableName ) {
		global $wpdb;

		$exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $tableName ) );

		return $exists !== null;
	}

	private function columnExists( $tableName, $columnName ) {
		global $wpdb;

		$exists = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM `{$tableName}` LIKE %s", $columnName ) );

		return $exists !== null;
	}

	public function run() {
		global $wpdb;
		if ( $this->command === 'create' ) {
			$columnsSql = $this->prepareColumns();

			$tableName = $wpdb->prefix . $this->table;

			if ( $this->tableExists( $tableName ) ) {
				return;
			}

			$columnsDef = implode( ",\n  ", $columnsSql );

			$sql = "CREATE TABLE `$tableName` (\n  $columnsDef\n) {$wpdb->get_charset_collate()};";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$wpdb->query( $sql );
		} else {
			$tableName = $wpdb->prefix . $this->table;

			foreach ( $this->columns as $column ) {
				$columnName = $column->getName();

				if ( ! $this->columnExists( $tableName, $columnName ) ) {
					$columnSql = $this->generateSingleColumnSql( $column );

					$afterColumn = $column->getAfter();
					$sql = "ALTER TABLE `$tableName` ADD $columnSql" . ( $afterColumn ? " AFTER `$afterColumn`" : "" ) . ";";
					$wpdb->query( $sql );
				}
			}
		}
	}

	/**
	 * Create a new auto-incrementing big integer (8-byte) column on the table.
	 *
	 * @param  string  $column
	 * 
	 * @return ColumnDefinition
	 */
	public function bigIncrements( $column ) {
		return $this->unsignedBigInteger( $column, true );
	}

	/**
	 * Create a new unsigned big integer (8-byte) column on the table.
	 *
	 * @param  string  $column
	 * @param  bool  $autoIncrement
	 * 
	 * @return ColumnDefinition
	 */
	public function unsignedBigInteger( $column, $autoIncrement = false ) {
		return $this->bigInteger( $column, $autoIncrement, true );
	}

	/**
	 * Add nullable creation and update timestamps to the table.
	 *
	 * @param  int|null  $precision
	 * @return Collection<int, ColumnDefinition>
	 */
	public function timestamps( $precision = null ) {
		//change timestamp to dateTime for wordpress compatibility
		return new Collection( [ 
			$this->dateTime( 'created_at', $precision )->nullable(),
			$this->dateTime( 'updated_at', $precision )->nullable(),
		] );
	}

	/**
	 * Create a new timestamp column on the table.
	 *
	 * @param  string  $column
	 * @param  int|null  $precision
	 * 
	 * @return ColumnDefinition
	 */
	public function timestamp( $column, $precision = null ) {
		$precision ??= $this->defaultTimePrecision();

		return $this->addColumn( 'timestamp', $column, compact( 'precision' ) );
	}

	/**
	 * Create a new date-time column on the table.
	 *
	 * @param  string  $column
	 * @param  int|null  $precision
	 * @return ColumnDefinition
	 */
	public function dateTime( $column, $precision = null ) {
		$precision ??= $this->defaultTimePrecision();

		return $this->addColumn( 'dateTime', $column, compact( 'precision' ) );
	}

	public function text( $column ) {
		return $this->addColumn( 'text', $column );
	}


	public function boolean( $column ) {
		return $this->addColumn( 'boolean', $column );
	}

	/**
	 * Get the default time precision.
	 */
	protected function defaultTimePrecision(): ?int {
		return 0;
	}

	/**
	 * Create a new big integer (8-byte) column on the table.
	 *
	 * @param  string  $column
	 * @param  bool  $autoIncrement
	 * @param  bool  $unsigned
	 * 
	 * @return ColumnDefinition
	 */
	public function bigInteger( $column, $autoIncrement = false, $unsigned = false ) {
		return $this->addColumn( 'bigInteger', $column, compact( 'autoIncrement', 'unsigned' ) );
	}

	/**
	 * Create a new integer (4-byte) column on the table.
	 *
	 * @param  string  $column
	 * @param  bool  $autoIncrement
	 * @param  bool  $unsigned
	 * 
	 * @return ColumnDefinition
	 */
	public function integer( $column, $autoIncrement = false, $unsigned = false ) {
		return $this->addColumn( 'integer', $column, compact( 'autoIncrement', 'unsigned' ) );
	}

	/**
	 * Create a new string column on the table.
	 *
	 * @param  string  $column
	 * @param  int|null  $length
	 * @return ColumnDefinition
	 */
	public function string( $column, $length = null ) {
		$length = $length ?: 255;

		return $this->addColumn( 'string', $column, compact( 'length' ) );
	}

	/**
	 * Specify a foreign key for the table.
	 *
	 * @param  string|array  $columns
	 * @param  string|null  $name
	 * @return ForeignKeyDefinition
	 */
	public function foreign( $columns, $name = null ) {
		$foreignInstance = $this->columns[ count( $this->columns ) - 1 ];

		if ( $foreignInstance instanceof ForeignIdColumnDefinition ) {
			$command = new ForeignKeyDefinition( $this, $foreignInstance->getAttributes() );
			$this->commands[] = $command;
			return $command;
		}

		return new ForeignKeyDefinition( $this, [ 
			'columns' => $columns,
			'name' => $name,
			'blueprint' => $this,
		] );
	}

	protected function indexCommand( $type, $columns, $index, $algorithm = null ) {
		// implementation of indexCommand
		return $this;
	}

	/**
	 * Create a new unsigned big integer (8-byte) column on the table.
	 *
	 * @param  string  $column
	 * @return ForeignIdColumnDefinition
	 */
	public function foreignId( $column ) {
		return $this->addColumnDefinition( new ForeignIdColumnDefinition( $this, [ 
			'type' => 'bigInteger',
			'name' => $column,
			'autoIncrement' => false,
			'unsigned' => true,
		] ) );
	}

	/**
	 * Add a new column to the blueprint.
	 *
	 * @param  string  $type
	 * @param  string  $name
	 * @param  array  $parameters
	 * 
	 * @return ColumnDefinition
	 */
	public function addColumn( $type, $name, array $parameters = [] ) {
		return $this->addColumnDefinition( new ColumnDefinition(
			array_merge( compact( 'type', 'name' ), $parameters )
		) );
	}

	/**
	 * Add a new column definition to the blueprint.
	 *
	 * @param  ColumnDefinition  $definition
	 * 
	 * @return ColumnDefinition
	 */
	protected function addColumnDefinition( $definition ) {
		$this->columns[] = $definition;

		return $definition;
	}

	public function getTable() {
		return $this->table;
	}

	public function dropColumn( $column ) {
		$this->commands[] = [ 'dropColumn', $column ];
		return $this;
	}
}