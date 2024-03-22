<?php
namespace Krokedil\KlarnaOnsiteMessaging;

use Krokedil\KlarnaOnsiteMessaging\Pages\Product;
use Krokedil\KlarnaOnsiteMessaging\Pages\Cart;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KOSM_VERSION', '0.0.1' );

/**
 * The orchestrator class.
 */
class KlarnaOnsiteMessaging {
	/**
	 * The internal settings state.
	 *
	 * @var Settings
	 */
	public $settings;

	/**
	 * Class constructor.
	 *
	 * @param array $settings Any existing KOSM settings.
	 */
	public function __construct( $settings ) {
		$this->settings = new Settings( $settings );
		$page           = new Product( $this->settings );
		$cart           = new Cart( $this->settings );

		if ( class_exists( 'WooCommerce' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_filter(
				'script_loader_tag',
				function ( $tag, $handle ) {
					if ( 'klarna_onsite_messaging_sdk' !== $handle ) {
						return $tag;
					}

					$environment    = 'yes' === $this->settings->get( 'onsite_messaging_test_mode' ) ? 'playground' : 'production';
					$data_client_id = apply_filters( 'kosm_data_client_id', $this->settings->get( 'data_client_id' ) );
					$tag            = str_replace( ' src', ' async src', $tag );
					$tag            = str_replace( '></script>', " data-environment={$environment} data-client-id='{$data_client_id}'></script>", $tag );

					return $tag;
				},
				10,
				2
			);
		}
	}

	/**
	 * TODO: Add docs.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! ( is_product() || is_cart() || ( ! empty( $post ) && has_shortcode( $post->post_content, 'onsite_messaging' ) ) ) ) {
			return;
		}

		$region        = 'eu-library';
		$base_location = wc_get_base_location();
		if ( is_array( $base_location ) && isset( $base_location['country'] ) ) {
			if ( in_array( $base_location['country'], array( 'US', 'CA' ) ) ) {
				$region = 'na-library';
			} elseif ( in_array( $base_location['country'], array( 'AU', 'NZ' ) ) ) {
				$region = 'oc-library';
			}
		}
		$region = apply_filters( 'kosm_region_library', $region );

		if ( ! empty( $this->settings->get( 'data_client_id' ) ) ) {
			wp_register_script( 'klarna_onsite_messaging_sdk', 'https://js.klarna.com/web-sdk/v1/klarna.js', array( 'jquery' ), null, true );
		}

		$script_path = plugin_dir_url( __FILE__ ) . 'assets/js/klarna-onsite-messaging.js';
		wp_register_script( 'klarna_onsite_messaging', $script_path, array( 'jquery' ), KOSM_VERSION );

		$localize = array(
			'ajaxurl'            => admin_url( 'admin-ajax.php' ),
			'get_cart_total_url' => \WC_AJAX::get_endpoint( 'kosm_get_cart_total' ),
		);

		global $post;
		if ( isset( $_GET['osmDebug'] ) && '1' === $_GET['osmDebug'] ) {
			$localize['debug_info'] = array(
				'product'       => is_product(),
				'cart'          => is_cart(),
				'shortcode'     => ( ! empty( $post ) && has_shortcode( $post->post_content, 'onsite_messaging' ) ),
				'data_client'   => ! ( empty( $this->settings->get( 'data_client_id' ) ) ),
				'locale'        => Utility::get_locale_from_currency(),
				'currency'      => get_woocommerce_currency(),
				'library'       => ( wp_scripts() )->registered['klarna_onsite_messaging_sdk']->src ?? $region,
				'base_location' => $base_location['country'],
			);
		}

		wp_localize_script(
			'klarna_onsite_messaging',
			'klarna_onsite_messaging_params',
			$localize
		);

		if ( ! empty( $this->settings->get( 'data_client_id' ) ) ) {
			wp_enqueue_script( 'klarna_onsite_messaging_sdk' );
		}

		wp_enqueue_script( 'klarna_onsite_messaging' );
	}
}
