<?php
namespace KlarnaOnsiteMessaging\Blocks;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Integration for Klarna Onsite Messaging Cart Block.
 */
class CartBlockIntegration implements IntegrationInterface {

	/**
	 * Get the name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'osm-cart-block-integration';
	}

	/**
	 * Initialize the integration.
	 *
	 * @return void
	 */
	public function initialize() {
		$script_url = plugin_dir_url( __FILE__ ) . 'assets/js/block-extension.js';

		wp_register_script( 'osm-cart-block-integration-script', $script_url, array( 'wp-blocks', 'wp-element', 'wp-editor' ), filemtime( $script_url ), true );
	}

	/**
	 * Get the script handles to be enqueued for the integration.
	 *
	 * @return array
	 */
	public function get_script_handles() {
		return array( 'osm-cart-block-integration-script' );
	}

	/**
	 * Get the editor script handles to be enqueued for the integration.
	 *
	 * @return array
	 */
	public function get_editor_script_handles() {
		return array();
	}

	/**
	 * Get the script data to be localized for the integration.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return array();
	}
}
