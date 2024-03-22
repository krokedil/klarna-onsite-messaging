<?php
namespace Krokedil\KlarnaOnsiteMessaging\Pages;

use Krokedil\KlarnaOnsiteMessaging\Utility;
use Krokedil\KlarnaOnsiteMessaging\Settings;

/**
 * TODO: Update class doc.
 */
class Cart extends Page {

	/**
	 * The location of the placement on the product page.
	 *
	 * @var string
	 */
	protected $priority = 5;

	/**
	 * The setting keys for the Product.
	 *
	 * @var array
	 */
	protected $properties = array(
		'enabled'      => 'onsite_messaging_enabled_cart',
		'theme'        => 'onsite_messaging_theme_cart',
		'key'          => 'placement_data_key_cart',
		'client_id'    => 'data_client_id',
		'placement_id' => 'placement_data_key_cart',
		'target'       => 'onsite_messaging_cart_location',
	);

	/**
	 * Class constructor.
	 *
	 * @param Settings $settings The KOSM settings.
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings, $this->properties );

		add_action(
			'wp_head',
			function () {
				if ( $this->enabled && is_cart() ) {
					$target   = apply_filters( 'klarna_onsite_messaging_cart_target', $this->target );
					$priority = apply_filters( 'klarna_onsite_messaging_cart_priority', $this->priority );
					add_action( $target, array( $this, 'add_iframe' ), $priority );

					add_action(
						'woocommerce_cart_totals_after_order_total',
						function () {
							?>
			<input type="hidden" id="kosm_cart_total" name="kosm_cart_total" value="<?php echo esc_html( WC()->cart->get_total( 'klarna_onsite_messaging' ) ); ?>">
							<?php
						}
					);
				}
			}
		);

	}

		/**
		 * Adds the iframe to the page.
		 *
		 * @return void
		 */
	public function add_iframe() {
		if ( ! empty( $this->client_id ) ) {
			$args = array(
				'key'             => $this->key,
				'purchase_amount' => WC()->cart->get_total( 'kosm' ) * 100,
				'theme'           => $this->theme,
				'client_id'       => $this->client_id,
			);
			Utility::print_placement( $args );
		} /*
		else {
			?>
			<klarna-placement class="klarna-onsite-messaging-product" <?php echo ( ! empty( $this->theme ) ) ? esc_html( "data-theme=$this->theme" ) : ''; ?>
				data-id="<?php echo esc_html( $this->placement_id ); ?>"
				data-total_amount="<?php echo esc_html( $price ); ?>"
				></klarna-placement>
			<?php
		} */
	}
}
