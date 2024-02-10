/*browser:true*/
/*global define*/

define(
    [
        'Magento_Checkout/js/view/payment/default',
        'VirtualPay_Payment/js/fingerprint',
    ],
    function (Component, fingerprint) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'VirtualPay_Payment/payment/form/pix',
                taxvat: window.checkoutConfig.payment.virtualpay_pix.customer_taxvat.replace(/[^0-9]/g, "")
            },

            /** @inheritdoc */
            initObservable: function () {
                this._super().observe([
                    'taxvat'
                ]);

                return this;
            },

            getCode: function() {
                return 'virtualpay_pix';
            },

            getData: function() {
                fingerprint(window.checkoutConfig.payment[this.getCode()].sandbox);
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'taxvat': this.taxvat(),
                        'fingerprint': window.yapay?.FingerPrint()?.getFingerPrint() || ''
                    }
                };
            },

            hasInstructions: function () {
                return (window.checkoutConfig.payment.virtualpay_pix.checkout_instructions.length > 0);
            },

            getInstructions: function () {
                return window.checkoutConfig.payment.virtualpay_pix.checkout_instructions;
            }
        });
    }
);

