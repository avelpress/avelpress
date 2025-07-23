<?php

namespace AvelPress\Support;

defined( 'ABSPATH' ) || exit;

class Formatter {
	public static function formatDocument( $document ) {
		$document = preg_replace( '/\D/', '', $document );

		if ( strlen( $document ) === 11 ) {
			return preg_replace( '/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $document );
		} elseif ( strlen( $document ) === 14 ) {
			return preg_replace( '/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $document );
		} else {
			return $document;
		}
	}
}