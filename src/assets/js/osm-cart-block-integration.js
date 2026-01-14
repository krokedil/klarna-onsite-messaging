const { registerPlugin } = wp.plugins;
const { ExperimentalOrderMeta } = wc.blocksCheckout;

const KlarnaPlacement = ({ key, locale, theme, purchaseAmount }) => (
    React.createElement(
        'klarna-placement',
        {
            className: 'klarna-onsite-messaging',
            'data-preloaded': 'true',
            'data-key': key,
            'data-locale': locale,
            'data-theme': theme,
            'data-purchase-amount': purchaseAmount
        }
    )
);

const render = () => {
    const osmData = window.osmCartBlockIntegrationData || {};
    window.wc?.wcSettings?.getSetting(
		'osm-cart-block-integration_data',
		{}
	) || {};
    return (
        React.createElement(
            ExperimentalOrderMeta,
            null,
            React.createElement(KlarnaPlacement, {
                key: osmData.key || '',
                locale: osmData.locale || '',
                theme: osmData.theme || 'default',
                purchaseAmount: osmData.purchase_amount || ''
            })
        )
    );
};

registerPlugin('osm-cart-block-integration', {
    render,
    scope: 'woocommerce-checkout',
});