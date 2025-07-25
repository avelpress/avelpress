<?php

namespace AvelPress\Database;

use AvelPress\Database\Eloquent\Model;
use AvelPress\Database\Eloquent\QueryBuilder;

class DynamicModel extends Model {
	protected $primaryKey = 'id';

	public function __construct( $data, $table ) {
		parent::__construct( $data, $table );
	}

	public function getTableName() {
		return $this->table;
	}
}