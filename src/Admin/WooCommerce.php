<?php
namespace AvelPress\Admin;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use AvelPress\Facades\Config;
use AvelPress\Foundation\Application;

defined( 'ABSPATH' ) || exit;

class WooCommerce {

	/**
	 * The application instance.
	 *
	 * @var Application
	 */
	protected $app;

	/**
	 * Constructor to initialize WooCommerce compatibility.
	 *
	 * @param Application $app The application instance.
	 */
	public function __construct( $app ) {
		$this->app = $app;
	}

	public function init() {
		if ( Config::boolean( 'app.woocommerce_declare_compatibility' ) ) {
			add_action( 'before_woocommerce_init', [ $this, 'woocommerce_declare_compatibility' ] );
		}
	}

	public function woocommerce_declare_compatibility() {
		if ( class_exists( FeaturesUtil::class) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->app->getPluginFile(), true );
			FeaturesUtil::declare_compatibility( 'product_block_editor', $this->app->getPluginFile(), true );
		}
	}
}