<?php

namespace AvelPress\Update;

use AvelPress\Update\Contracts\UpdateProvider;

defined( 'ABSPATH' ) || exit;

/**
 * Feeds the WordPress update APIs with well formed data for a plugin.
 *
 * The remote endpoint returns only the release payload (version, package and
 * store assets), while core expects a very specific shape on each side: the
 * update_plugins transient entry must carry "plugin"/"id"/"slug" — missing keys
 * break wp_list_pluck() on the plugin install screen — and plugins_api expects
 * "version"/"download_link"/"name". Both objects are assembled here so no plugin
 * has to learn that again.
 *
 * @since 1.3.0
 */
class PluginUpdater {

	/**
	 * How long a successful lookup is cached.
	 */
	const CACHE_TTL = 6 * HOUR_IN_SECONDS;

	/**
	 * How long a failed lookup is cached, so a broken endpoint is not hammered.
	 */
	const CACHE_TTL_FAILURE = 15 * MINUTE_IN_SECONDS;

	/**
	 * Where the release payload comes from.
	 *
	 * @var UpdateProvider
	 */
	protected $provider;

	/**
	 * Plugin basename, e.g. "my-plugin/my-plugin.php".
	 *
	 * @var string
	 */
	protected $basename;

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Installed plugin version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * @param UpdateProvider $provider Where the release payload comes from.
	 * @param string         $basename Plugin basename.
	 * @param string         $slug     Plugin slug.
	 * @param string         $version  Installed version.
	 */
	public function __construct( UpdateProvider $provider, $basename, $slug, $version ) {
		$this->provider = $provider;
		$this->basename = $basename;
		$this->slug = $slug;
		$this->version = $version;
	}

	/**
	 * Registers the update hooks.
	 */
	public function register() {
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'checkForUpdate' ] );
		add_filter( 'plugins_api', [ $this, 'updateInfo' ], 20, 3 );
	}

	/**
	 * Adds the plugin to the update_plugins transient.
	 *
	 * @param object|array $transient Update transient.
	 * @return object|array
	 */
	public function checkForUpdate( $transient ) {
		if ( empty( $transient ) ) {
			return $transient;
		}

		if ( is_array( $transient ) ) {
			$transient = (object) $transient;
		}

		$remote = $this->fetchRemoteInfo();

		if ( empty( $remote['new_version'] ) ) {
			return $transient;
		}

		foreach ( [ 'response', 'no_update' ] as $bucket ) {
			if ( ! isset( $transient->$bucket ) || ! is_array( $transient->$bucket ) ) {
				$transient->$bucket = [];
			}
		}

		$hasUpdate = version_compare( $this->version, $remote['new_version'], '<' );
		$target = $hasUpdate ? 'response' : 'no_update';
		$other = $hasUpdate ? 'no_update' : 'response';

		$transient->{$target}[ $this->basename ] = $this->buildUpdateObject( $remote );
		unset( $transient->{$other}[ $this->basename ] );

		return $transient;
	}

	/**
	 * Provides the plugin information shown on the "view details" modal.
	 *
	 * @param false|object|array $result Response from the plugins API.
	 * @param string             $action Requested action.
	 * @param object             $args   Request arguments.
	 * @return false|object|array
	 */
	public function updateInfo( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( ! isset( $args->slug ) || $args->slug !== $this->slug ) {
			return $result;
		}

		$remote = $this->fetchRemoteInfo();

		if ( empty( $remote ) ) {
			return $result;
		}

		$header = $this->pluginHeader();

		return (object) [ 
			'name' => $header['Name'],
			'slug' => $this->slug,
			'version' => isset( $remote['new_version'] ) ? $remote['new_version'] : $this->version,
			'author' => $header['Author'],
			'homepage' => $this->detailsUrl( $remote, $header ),
			'requires' => isset( $remote['requires'] ) ? $remote['requires'] : '',
			'tested' => isset( $remote['tested'] ) ? $remote['tested'] : '',
			'requires_php' => isset( $remote['requires_php'] ) ? $remote['requires_php'] : '',
			'last_updated' => isset( $remote['last_updated'] ) ? $remote['last_updated'] : '',
			'sections' => isset( $remote['sections'] ) ? (array) $remote['sections'] : [],
			'banners' => isset( $remote['banners'] ) ? (array) $remote['banners'] : [],
			'banners_rtl' => isset( $remote['banners_rtl'] ) ? (array) $remote['banners_rtl'] : [],
			'icons' => isset( $remote['icons'] ) ? (array) $remote['icons'] : [],
			'download_link' => isset( $remote['package'] ) ? $remote['package'] : '',
		];
	}

	/**
	 * Builds the transient entry in the shape expected by core.
	 *
	 * @param array $remote Remote release payload.
	 * @return object
	 */
	protected function buildUpdateObject( array $remote ) {
		return (object) [ 
			'id' => $this->slug,
			'slug' => $this->slug,
			'plugin' => $this->basename,
			'new_version' => $remote['new_version'],
			'url' => $this->detailsUrl( $remote ),
			'package' => isset( $remote['package'] ) ? $remote['package'] : '',
			'icons' => isset( $remote['icons'] ) ? (array) $remote['icons'] : [],
			'banners' => isset( $remote['banners'] ) ? (array) $remote['banners'] : [],
			'banners_rtl' => isset( $remote['banners_rtl'] ) ? (array) $remote['banners_rtl'] : [],
			'requires' => isset( $remote['requires'] ) ? $remote['requires'] : '',
			'tested' => isset( $remote['tested'] ) ? $remote['tested'] : '',
			'requires_php' => isset( $remote['requires_php'] ) ? $remote['requires_php'] : '',
		];
	}

	/**
	 * Resolves the public URL used as plugin homepage.
	 *
	 * @param array $remote Remote release payload.
	 * @param array $header Plugin header data.
	 * @return string
	 */
	protected function detailsUrl( array $remote, $header = [] ) {
		if ( ! empty( $remote['url'] ) ) {
			return $remote['url'];
		}

		if ( ! empty( $remote['download_url'] ) ) {
			return $remote['download_url'];
		}

		return empty( $header['PluginURI'] ) ? '' : $header['PluginURI'];
	}

	/**
	 * Reads the installed plugin header.
	 *
	 * @return array
	 */
	protected function pluginHeader() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return get_plugin_data( WP_PLUGIN_DIR . '/' . $this->basename );
	}

	/**
	 * Fetches the remote release payload, cached between checks.
	 *
	 * Failures are cached for a shorter period so an unreachable endpoint does
	 * not add a blocking request to every update check.
	 *
	 * @return array Release payload, empty when unavailable.
	 */
	protected function fetchRemoteInfo() {
		$key = 'avelpress_update_' . md5( $this->slug );
		$cached = get_site_transient( $key );

		if ( is_array( $cached ) && ! $this->isForceCheck() ) {
			return $cached;
		}

		$remote = $this->provider->fetch( $this->slug );

		if ( is_wp_error( $remote ) ) {
			set_site_transient( $key, [], self::CACHE_TTL_FAILURE );

			return [];
		}

		set_site_transient( $key, $remote, self::CACHE_TTL );

		return $remote;
	}

	/**
	 * Whether the user asked for a fresh check on the updates screen.
	 *
	 * @return bool
	 */
	protected function isForceCheck() {
		return ! empty( $_GET['force-check'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}
