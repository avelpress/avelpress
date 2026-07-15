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
			case 'longText':
				$sql .= ' longtext';
				break;
			case 'json':
				$sql .= ' json';
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
		$uniqueKeys = [];
		$indexKeys = [];
		foreach ( $this->columns as $column ) {
			$columnsSql[] = $this->generateSingleColumnSql( $column );

			if ( $column->isPrimary() ) {
				$primaryKey[] = $column->getName();
			}

			if ( $column->isUnique() ) {
				$uniqueKeys[] = $column->getName();
			}

			if ( $column->isIndex() && ! $column->isUnique() && ! $column->isPrimary() ) {
				$indexKeys[] = $column->getName();
			}
		}

		if ( ! empty( $uniqueKeys ) ) {
			foreach ( $uniqueKeys as $uniqueKey ) {
				$columnsSql[] = "UNIQUE KEY (`$uniqueKey`)";
			}
		}

		if ( ! empty( $indexKeys ) ) {
			foreach ( $indexKeys as $indexKey ) {
				$columnsSql[] = "KEY (`$indexKey`)";
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

				if ( is_array( $command ) && in_array( $command[0], [ 'index', 'unique' ], true ) ) {
					$keyword = 'unique' === $command[0] ? 'UNIQUE KEY' : 'KEY';
					$columnsSql[] = "$keyword `{$command[1]}` (" . $this->quoteIndexColumns( $command[2] ) . ")";
				}
			}
		}

		return $columnsSql;
	}

	public function tableExists( $tableName ) {
		global $wpdb;

		$exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $tableName ) );

		return $exists !== null;
	}

	private function columnExists( $tableName, $columnName ) {
		global $wpdb;

		$exists = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM `{$tableName}` LIKE %s", $columnName ) );

		return $exists !== null;
	}

	private function indexExists( $tableName, $indexName ) {
		global $wpdb;

		$exists = $wpdb->get_var( $wpdb->prepare( "SHOW INDEX FROM `{$tableName}` WHERE Key_name = %s", $indexName ) );

		return $exists !== null;
	}

	private function runAlterCommands( $tableName ) {
		global $wpdb;

		foreach ( $this->commands as $command ) {
			if ( ! is_array( $command ) || empty( $command[0] ) ) {
				continue;
			}

			if ( 'dropColumn' === $command[0] && ! empty( $command[1] ) ) {
				$columnName = (string) $command[1];

				if ( $this->columnExists( $tableName, $columnName ) ) {
					$wpdb->query( "ALTER TABLE `$tableName` DROP COLUMN `$columnName`;" );
				}
			}

			if ( in_array( $command[0], [ 'dropIndex', 'dropUnique' ], true ) && ! empty( $command[1] ) ) {
				$indexName = (string) $command[1];

				if ( $this->indexExists( $tableName, $indexName ) ) {
					$wpdb->query( "ALTER TABLE `$tableName` DROP INDEX `$indexName`;" );
				}
			}
		}
	}

	private function runIndexCommands( $tableName ) {
		global $wpdb;

		foreach ( $this->commands as $command ) {
			if ( ! is_array( $command ) || empty( $command[0] ) || empty( $command[1] ) ) {
				continue;
			}

			if ( in_array( $command[0], [ 'index', 'unique' ], true ) ) {
				$indexName = (string) $command[1];

				if ( ! $this->indexExists( $tableName, $indexName ) ) {
					$keyword = 'unique' === $command[0] ? 'UNIQUE INDEX' : 'INDEX';
					$wpdb->query( "ALTER TABLE `$tableName` ADD $keyword `$indexName` (" . $this->quoteIndexColumns( $command[2] ) . ");" );
				}
			}
		}
	}

	public function run() {
		global $wpdb;
		if ( $this->command === 'create' ) {

			$tableName = $wpdb->prefix . $this->table;

			if ( $this->tableExists( $tableName ) ) {
				return;
			}

			$columnsSql = $this->prepareColumns();

			$columnsDef = implode( ",\n  ", $columnsSql );

			// Pin the engine instead of inheriting the server default: foreign keys are silently
			// ignored by MyISAM, and a table created on a MyISAM-defaulting host can never be
			// referenced by one, which fails the child's CREATE with errno 150.
			$sql = "CREATE TABLE `$tableName` (\n  $columnsDef\n) ENGINE=InnoDB {$wpdb->get_charset_collate()};";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$this->query( $sql, $tableName );
		} else {
			$tableName = $wpdb->prefix . $this->table;

			$this->runAlterCommands( $tableName );

			foreach ( $this->columns as $column ) {
				$columnName = $column->getName();

				if ( ! $this->columnExists( $tableName, $columnName ) ) {
					$columnSql = $this->generateSingleColumnSql( $column );

					$afterColumn = $column->getAfter();
					$sql = "ALTER TABLE `$tableName` ADD $columnSql" . ( $afterColumn ? " AFTER `$afterColumn`" : "" ) . ";";
					$this->query( $sql, $tableName );
				}
			}

			$this->runIndexCommands( $tableName );
		}
	}

	/**
	 * Run a schema statement, raising the database error instead of swallowing it.
	 *
	 * $wpdb->query() reports failure by returning false, so an unchecked call lets a CREATE or
	 * ALTER fail in complete silence. The migrator would then record the migration as applied
	 * and never retry it, leaving the install permanently missing the table or column while
	 * every write to it quietly does nothing.
	 *
	 * @param string $sql
	 * @param string $tableName
	 * @return void
	 *
	 * @throws \RuntimeException When the statement fails.
	 */
	private function query( $sql, $tableName ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( false === $wpdb->query( $sql ) ) {
			throw new \RuntimeException(
				sprintf( 'Schema statement failed for table %s: %s', $tableName, $wpdb->last_error )
			);
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
	 * Create a new unsigned integer (4-byte) column on the table.
	 *
	 * @param  string  $column
	 * @param  bool  $autoIncrement
	 *
	 * @return ColumnDefinition
	 */
	public function unsignedInteger( $column, $autoIncrement = false ) {
		return $this->integer( $column, $autoIncrement, true );
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

	public function longText( $column ) {
		return $this->addColumn( 'longText', $column );
	}

	public function json( $column ) {
		return $this->addColumn( 'json', $column );
	}

	public function boolean( $column ) {
		return $this->addColumn( 'boolean', $column );
	}

	public function uuid( $column ) {
		return $this->addColumn( 'string', $column, [ 'length' => 36 ] );
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

	/**
	 * Specify an index for the table.
	 *
	 * @param  string|array  $columns
	 * @param  string|null  $name
	 * @return $this
	 */
	public function index( $columns, $name = null ) {
		return $this->indexCommand( 'index', $columns, $name );
	}

	/**
	 * Specify a unique index for the table.
	 *
	 * @param  string|array  $columns
	 * @param  string|null  $name
	 * @return $this
	 */
	public function unique( $columns, $name = null ) {
		return $this->indexCommand( 'unique', $columns, $name );
	}

	/**
	 * Add a new index command to the blueprint.
	 *
	 * @param  string  $type
	 * @param  string|array  $columns
	 * @param  string|null  $index
	 * @return $this
	 */
	protected function indexCommand( $type, $columns, $index = null ) {
		$columns = (array) $columns;

		$index = $index ?: $this->createIndexName( $type, $columns );

		$this->commands[] = [ $type, $index, $columns ];

		return $this;
	}

	/**
	 * Create a default index name for the table.
	 *
	 * @param  string  $type
	 * @param  array  $columns
	 * @return string
	 */
	protected function createIndexName( $type, array $columns ) {
		$index = strtolower( $this->table . '_' . implode( '_', $columns ) . '_' . $type );

		return str_replace( [ '-', '.', '(', ')' ], [ '_', '_', '_', '' ], $index );
	}

	/**
	 * Quote index columns, supporting prefix lengths like "column(20)".
	 *
	 * @param  array  $columns
	 * @return string
	 */
	private function quoteIndexColumns( array $columns ) {
		$quoted = [];

		foreach ( $columns as $column ) {
			if ( preg_match( '/^(\w+)\s*\((\d+)\)$/', $column, $matches ) ) {
				$quoted[] = "`{$matches[1]}`({$matches[2]})";
			} else {
				$quoted[] = "`$column`";
			}
		}

		return implode( ', ', $quoted );
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

	/**
	 * Indicate that the given index should be dropped.
	 *
	 * @param  string|array  $index  Index name or array of columns to resolve the conventional name.
	 * @return $this
	 */
	public function dropIndex( $index ) {
		return $this->dropIndexCommand( 'dropIndex', 'index', $index );
	}

	/**
	 * Indicate that the given unique index should be dropped.
	 *
	 * @param  string|array  $index  Index name or array of columns to resolve the conventional name.
	 * @return $this
	 */
	public function dropUnique( $index ) {
		return $this->dropIndexCommand( 'dropUnique', 'unique', $index );
	}

	/**
	 * Add a new drop index command to the blueprint.
	 *
	 * @param  string  $command
	 * @param  string  $type
	 * @param  string|array  $index
	 * @return $this
	 */
	protected function dropIndexCommand( $command, $type, $index ) {
		if ( is_array( $index ) ) {
			$index = $this->createIndexName( $type, $index );
		}

		$this->commands[] = [ $command, $index ];

		return $this;
	}
}