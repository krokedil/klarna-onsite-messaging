// assets/js/block-extension.js
const { registerPlugin } = wp.plugins;
const { ExperimentalOrderMeta } = wc.blocksCheckout; // blocksCheckout is used for both the Cart and Checkout blocks.

const render = () => {
    alert('Render function called!');
    return (
        React.createElement(
            ExperimentalOrderMeta,
            null,
            React.createElement("p", null, "Text to show!")
        )
    );
};

registerPlugin('my-wc-block-integration', {
    render,
    scope: 'woocommerce-checkout', // woocommerce-checkout applies to both the Cart and Checkout blocks.
});