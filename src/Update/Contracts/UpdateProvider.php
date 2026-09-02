<?php

namespace AvelPress\Update\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * Where a plugin learns that a new version exists.
 *
 * Kept behind an interface because the answer comes from different places: a
 * store that validates a licence before saying anything, a public endpoint, or
 * whatever a plugin already talks to.
 *
 * @since 1.3.0
 */
interface UpdateProvider {

	/**
	 * Asks for the current release of a plugin.
	 *
	 * @param string $slug Plugin slug.
	 * @return array|\WP_Error Release payload, or the error that stopped it.
	 */
	public function fetch( $slug );
}
