define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (
    Component,
    rendererList
) {
    'use strict';

    rendererList.push({
        type: 'virtualpay_pix',
        component: 'VirtualPay_Payment/js/view/payment/method-renderer/pix'
    });

    return Component.extend({});
});
