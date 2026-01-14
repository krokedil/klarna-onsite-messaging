const { registerPlugin } = wp.plugins;
const { ExperimentalOrderMeta } = wc.blocksCheckout;

const KlarnaPlacement = ({ klarnaKey, locale, theme, purchaseAmount }) => (
    React.createElement(
        'klarna-placement',
        {
            className: 'klarna-onsite-messaging',
            'data-preloaded': 'true',
            'class': 'klarna-onsite-messaging',
            'data-key': klarnaKey,
            'data-locale': locale,
            'data-theme': theme,
            'data-purchase-amount': purchaseAmount
        }
    )
);

const render = () => {
    const osmData =
	window.wc?.wcSettings?.getSetting(
		'osm-cart-block-integration_data',
		{}
	) || {};
    return (
        React.createElement(
            ExperimentalOrderMeta,
            null,
            React.createElement(KlarnaPlacement, {
                klarnaKey: osmData.key || '',
                locale: osmData.locale || '',
                theme: osmData.theme || '',
                purchaseAmount: osmData.purchase_amount || ''
            })
        )
    );
};

registerPlugin('osm-cart-block-integration', {
    render,
    scope: 'woocommerce-checkout',
});
