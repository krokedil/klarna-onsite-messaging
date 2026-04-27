const { registerPlugin: RegisterPluginOSM } = wp.plugins
const { useSelect } = wp.data

const ElementOSM = ({ klarnaKey, locale, theme, purchaseAmount }) => {
    const totalPrice = useSelect((select) => select("wc/store/cart").getCartTotals()?.total_price)

    React.useEffect(() => {
        if (window.klarna_onsite_messaging && totalPrice) {
            window.klarna_onsite_messaging.update_total_price(totalPrice)
        }
    }, [totalPrice])

    return React.createElement("klarna-placement", {
        className: "klarna-onsite-messaging",
        "data-preloaded": "true",
        class: "klarna-onsite-messaging",
        "data-key": klarnaKey,
        "data-locale": locale,
        "data-theme": theme,
        "data-purchase-amount": purchaseAmount,
    })
}

const renderOSM = () => {
    const osmData = window.wc?.wcSettings?.getSetting("osm-cart-block-integration_data", {}) || {}
    return React.createElement(ElementOSM, {
        klarnaKey: osmData.key || "",
        locale: osmData.locale || "",
        theme: osmData.theme || "",
        purchaseAmount: osmData.purchase_amount || "",
    })
}

RegisterPluginOSM("osm-cart-block-integration", {
    render: renderOSM,
    scope: "woocommerce-cart",
})
