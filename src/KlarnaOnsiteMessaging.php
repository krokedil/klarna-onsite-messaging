<?php
namespace Krokedil\KlarnaOnsiteMessaging;

use Krokedil\KlarnaOnsiteMessaging\Pages\Product;
use Krokedil\KlarnaOnsiteMessaging\Pages\Cart;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KOSM_VERSION', '1.1.0' );

/**
 * The orchestrator class.
 */
class KlarnaOnsiteMessaging {
	/**
	 * The internal settings state.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Display placement on product page.
	 *
	 * @var Product
	 */
	private $product;

	/**
	 * Display placement on cart page.
	 *
	 * @var Cart
	 */
	private $cart;

	/**
	 * Display placement with shortcode.
	 *
	 * @var Shortcode
	 */
	private $shortcode;


	/**
	 * Class constructor.
	 *
	 * @param array $settings Any existing KOSM settings.
	 */
	public function __construct( $settings ) {
		$this->settings  = new Settings( $settings );
		$this->product   = new Product( $this->settings );
		$this->cart      = new Cart( $this->settings );
		$this->shortcode = new Shortcode();

		add_action( 'widgets_init', array( $this, 'init_widget' ) );

		if ( class_exists( 'WooCommerce' ) ) {
			// Lower hook priority to ensure the dequeue of the KOSM plugin scripts happens AFTER they have been enqueued.
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 99 );
			add_filter( 'script_loader_tag', array( $this, 'add_data_attributes' ), 10, 2 );
		}

		add_action( 'admin_notices', array( $this, 'kosm_installed_admin_notice' ) );

		// Unhook the KOSM plugin's action hooks.
		if ( class_exists( 'Klarna_OnSite_Messaging_For_WooCommerce' ) ) {
			$hooks    = wc_get_var( $GLOBALS['wp_filter']['wp_head'] );
			$priority = 10;
			foreach ( $hooks->callbacks[ $priority ] as $callback ) {
				$function = $callback['function'];
				if ( is_array( $function ) ) {
					$class  = reset( $function );
					$method = end( $function );
					if ( is_object( $class ) && strpos( get_class( $class ), 'Klarna_OnSite_Messaging' ) !== false ) {
						remove_action( 'wp_head', array( $class, $method ), $priority );
					}
				}
			}
		}
	}

	/**
	 * Register the widget.
	 *
	 * @return void
	 */
	public function init_widget() {
		register_widget( new Widget() );
	}

	/**
	 * Check if the Klarna On-Site Messaging plugin is active, and notify the admin about the new changes.
	 *
	 * @return void
	 */
	public function kosm_installed_admin_notice() {
		$plugin = 'klarna-onsite-messaging-for-woocommerce/klarna-onsite-messaging-for-woocommerce.php';
		if ( is_plugin_active( $plugin ) ) {
			$message = __( 'The "Klarna On-Site Messaging for WooCommerce" plugin is now integrated into Klarna Payments. Please disable the plugin.', 'klarna-onsite-messaging-for-woocommerce' );
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $message ) );

		}
	}

	/**
	 * Add data- attributes to <script> tag.
	 *
	 * @param string $tag The <script> tag for the enqueued script.
	 * @param string $handle The script’s registered handle.
	 * @return string
	 */
	public function add_data_attributes( $tag, $handle ) {
		if ( 'klarna_onsite_messaging_sdk' !== $handle ) {
			return $tag;
		}

		$environment    = 'yes' === $this->settings->get( 'onsite_messaging_test_mode' ) ? 'playground' : 'production';
		$data_client_id = apply_filters( 'kosm_data_client_id', $this->settings->get( 'data_client_id' ) );
		$tag            = str_replace( ' src', ' async src', $tag );
		$tag            = str_replace( '></script>', " data-environment={$environment} data-client-id='{$data_client_id}'></script>", $tag );

		return $tag;
	}

	/**
	 * Register KOSM and library scripts.
	 *
	 * @return void
	 */
	public function register_scripts() {

		$client_id = apply_filters( 'kosm_data_client_id', $this->settings->get( 'data_client_id' ) );

		if ( ! empty( $client_id ) ) {
			// phpcs:ignore -- The version is managed by Klarna.
			wp_register_script( 'klarna_onsite_messaging_sdk', 'https://js.klarna.com/web-sdk/v1/klarna.js', array(), false );
		}

		// Deregister the script that is registered by the KOSM plugin.
		wp_deregister_script( 'klarna_onsite_messaging' );
		wp_deregister_script( 'klarna-onsite-messaging' );
		wp_deregister_script( 'onsite_messaging_script' );

		$script_path = plugin_dir_url( __FILE__ ) . 'assets/js/klarna-onsite-messaging.js';
		wp_register_script( 'klarna_onsite_messaging', $script_path, array( 'jquery', 'klarna_onsite_messaging_sdk' ), KOSM_VERSION, true );
	}

	/**
	 * Get the settings object.
	 *
	 * @return Settings
	 */
	public function settings() {
		return $this->settings;
	}

	/**
	 * Get the product object.
	 *
	 * @return Product
	 */
	public function product() {
		return $this->product;
	}

	/**
	 * Get the cart object.
	 *
	 * @return Cart
	 */
	public function cart() {
		return $this->cart;
	}

	/**
	 * Get the shortcode object.
	 *
	 * @return Shortcode
	 */
	public function shortcode() {
		return $this->shortcode;
	}
}
