<?php
namespace AvelPress\Utils;

defined( 'ABSPATH' ) || exit;

class Reflection {
	public static function getDefaultValue( $className, $propertyName, $default = null ) {
		$reflectionClass = new \ReflectionClass( $className );
		$defaultProperties = $reflectionClass->getDefaultProperties();
		return array_key_exists( $propertyName, $defaultProperties ) ? $defaultProperties[ $propertyName ] : $default;
	}
}