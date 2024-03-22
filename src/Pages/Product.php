<?php
namespace Krokedil\KlarnaOnsiteMessaging\Pages;

use Krokedil\KlarnaOnsiteMessaging\Utility;
use Krokedil\KlarnaOnsiteMessaging\Settings;

/**
 * TODO: Update class doc.
 */
class Product extends Page {
	/**
	 * The hook name for location of the placement.
	 *
	 * @var string
	 */
	protected $target = 'woocommerce_single_product_summary';

	/**
	 * The setting keys for the Product.
	 *
	 * @var array
	 */
	protected $properties = array(
		'enabled'      => 'onsite_messaging_enabled_product',
		'theme'        => 'onsite_messaging_theme_product',
		'key'          => 'placement_data_key_product',
		'client_id'    => 'data_client_id',
		'placement_id' => 'placement_data_key_product',
		'priority'     => 'onsite_messaging_product_location',
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
				if ( $this->enabled && is_product() ) {
					$target   = apply_filters( 'klarna_onsite_messaging_product_target', $this->target );
					$priority = apply_filters( 'klarna_onsite_messaging_product_priority', $this->priority );
					add_action( $target, array( $this, 'add_iframe' ), $priority );
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
				'purchase-amount' => '',
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
