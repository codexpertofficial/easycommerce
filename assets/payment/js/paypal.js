jQuery(async function ($) {
    const { __, sprintf } = wp.i18n;

    let cartData = null;
    let paypalButtonsInstance = null;

    function handlePayPalTransaction(details) {
        return new Promise((resolve) => {
            const $form = $('form');
            const $hiddenInput = $('<input>', {
                type: 'hidden',
                name: 'meta[paypalPaymentID]',
                value: details.id
            });

            $form.append($hiddenInput);
            $form.trigger('payment');
            resolve();
        });
    }

    async function fetchData() {
        try {
            const response = await fetch(`${EASYCOMMERCE.rest_base}/cart`, {
                credentials: 'include',
                headers: {
                    'X-WP-Nonce': EASYCOMMERCE.nonce
                }
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            if (data && data.data && data.data.cart) {
                cartData = data.data.cart;
            } else {
                throw new Error('Invalid cart data received');
            }
        } catch (error) {
            console.error('Error fetching cart data:', error);
            $('#easycommerce_paypal_payment_errors').text(__('Failed to load cart data. Please refresh the page.', 'easycommerce'));
        }
    }

    function setupPayPalButtons() {

        const total       = cartData.amounts?.total ?? 0;
        let   lockedTotal = null; 

        // Payment button options.
        const ButtonOptions = {
            onClick: function (data, actions) {
                const form = $('form')[0];

                if (!form) return actions.resolve();

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return actions.reject();
                }

                return actions.resolve();
            },

            createOrder: async function (data, actions) {
                const lockedResponse = await fetch(`${EASYCOMMERCE.rest_base}/cart/lock`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': EASYCOMMERCE.nonce,
                    },
                });

                if (lockedResponse.ok) {
                    const lockedData = await lockedResponse.json();
                    const lockedAmount = lockedData.data?.total;
                    if (lockedAmount != null) {
                        lockedTotal = parseFloat(lockedAmount);
                    }
                }

                await fetchData();

                const amountToCharge = lockedTotal ?? total;

                const shippingFee = parseFloat(cartData.amounts?.shipping_fee    ?? 0);
                const tax         = parseFloat(cartData.amounts?.tax             ?? 0);
                const shippingTax = parseFloat(cartData.amounts?.shipping_tax    ?? 0);
                const discount    = parseFloat(cartData.amounts?.discount_amount ?? 0);
                const totalTax    = parseFloat((tax + shippingTax).toFixed(2));

                const itemTotal = parseFloat(
                    cartData.items.reduce((sum, item) => sum + (item.subtotal ?? 0), 0).toFixed(2)
                );

                const breakdown = {
                    item_total: {
                        currency_code: EASYCOMMERCE.currency_code,
                        value: itemTotal.toFixed(2),
                    },
                };

                if (shippingFee > 0) {
                    breakdown.shipping = {
                        currency_code: EASYCOMMERCE.currency_code,
                        value: shippingFee.toFixed(2),
                    };
                }

                if (totalTax > 0) {
                    breakdown.tax_total = {
                        currency_code: EASYCOMMERCE.currency_code,
                        value: totalTax.toFixed(2),
                    };
                }

                if (discount > 0) {
                    breakdown.discount = {
                        currency_code: EASYCOMMERCE.currency_code,
                        value: discount.toFixed(2),
                    };
                }

                return actions.order.create({
                    purchase_units: [
                        {
                            amount: {
                                currency_code: EASYCOMMERCE.currency_code,
                                value: parseFloat(amountToCharge).toFixed(2),
                                breakdown: breakdown,
                            },
                            description: __('Purchase from EasyCommerce', 'easycommerce'),
                            items: cartData.items.map(item => ({
                                name: item.title || __('Item', 'easycommerce'),
                                unit_amount: {
                                    currency_code: EASYCOMMERCE.currency_code,
                                    value: item.is_free
                                        ? (0).toFixed(2)
                                        : parseFloat(String(item.unit_price).replace(/[^0-9.]/g, '')).toFixed(2),
                                },
                                quantity: item.quantity || 1,
                            })),
                        },
                    ],
                });
            },

            onApprove: async function (data, actions) {
                try {
                    const cartResponse = await fetch(`${EASYCOMMERCE.rest_base}/cart`, {
                        credentials: 'include',
                        headers: { 'X-WP-Nonce': EASYCOMMERCE.nonce },
                    });

                    if (cartResponse.ok) {
                        const cartJson = await cartResponse.json();
                        const currentTotal = parseFloat(cartJson.data?.cart?.amounts?.total ?? 0);
                        const expectedTotal = lockedTotal ?? total;

                        if (Math.abs(currentTotal - expectedTotal) > 0.01) {
                            $('#easycommerce-checkout-order-error')
                                .text(__('Your cart has been updated. Please refresh the page and try again.', 'easycommerce'))
                                .slideDown(400, function () {
                                    this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                });
                            return;
                        }
                    }
                } catch (e) {
                    console.warn('[PayPal] Pre-capture verification failed, proceeding:', e);
                }

                return actions.order.capture().then(function (details) {
                    handlePayPalTransaction(details);
                });
            },

            onError: function (err) {
                console.error('PayPal payment error:', err);
                const errorMessage = err && err.message ? err.message : __('An unknown error occurred with PayPal', 'easycommerce');
                // translators: %s: PayPal error message.
                const displayMessage = sprintf(__('PayPal Error: %s', 'easycommerce'), errorMessage);
                $('#easycommerce_paypal_payment_errors').text(displayMessage);
                $('#easycommerce-checkout-order-error').text(displayMessage).slideDown(400, function () {
                    this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            },
        };

        if (paypalButtonsInstance) {
            try { paypalButtonsInstance.close(); } catch (e) {}
            paypalButtonsInstance = null;
        }

        paypalButtonsInstance = paypal.Buttons(ButtonOptions);
        paypalButtonsInstance.render('#easycommerce_paypal_payment_form');
    }

    async function initializePayPalButtons() {
        const $formContainer = $('#easycommerce_paypal_payment_form');

        if (!$formContainer.length) return;

        await fetchData();

        if (cartData) {
            setupPayPalButtons();
        }
    }

    $(document).on("easycommerceRenderPaymentForm", initializePayPalButtons);

    $(document).on('easycommerce_cart_updated', function () {
        if ($('#easycommerce_paypal_payment_form').length) {
            initializePayPalButtons();
        }
    });

    $(document).on('change', 'input[name="easycommerce-payment_method"]', function () {
        if ($(this).val() === 'paypal') {
            initializePayPalButtons();
        }
    });
});
