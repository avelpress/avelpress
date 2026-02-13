<?php
namespace AvelPress\Database\Migrations;

defined( 'ABSPATH' ) || exit;
abstract class Migration {

	protected $oldVersion;

	/**
	 * Run the migrations.
	 */
	abstract public function up(): void;

	/**
	 * Reverse the migrations.
	 */
	abstract public function down(): void;

	public function setOldVersion( $version ) {
		$this->oldVersion = $version;
	}
}