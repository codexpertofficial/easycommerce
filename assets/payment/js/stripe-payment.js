jQuery(function ($) {
    if (typeof Stripe === 'undefined' || !EASYCOMMERCE.stripe || !EASYCOMMERCE.stripe.publishable_key) {
        return;
    }

    var stripe = Stripe(EASYCOMMERCE.stripe.publishable_key);
    var elements = null;
    var paymentElement = null;
    var clientSecret = null;
    var intentId = null;
    var customerId = null;
    var isSubmitting = false;
    var cartData = null;
    var intentMode = (EASYCOMMERCE.stripe && EASYCOMMERCE.stripe.intent_mode === 'setup') ? 'setup' : 'payment';

    initializePaymentElement();

        async function fetchCartData() {
        try {
            const response = await fetch(EASYCOMMERCE.rest_base + '/cart', {
                credentials: 'include',
                headers: {
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
            });

            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }

            const data = await response.json();

            if (data && data.data && data.data.cart) {
                cartData = data.data.cart;
                return cartData;
            } else {
                throw new Error('Invalid cart data received');
            }
        } catch (error) {
            console.error('Error fetching cart data:', error);
            showPaymentError('Failed to load cart data. Please refresh the page.');
            return null;
        }
    }

    function getCartAmount() {
        if (cartData && cartData.amounts && cartData.amounts.total) {
            return Math.max(parseInt(parseFloat(cartData.amounts.total) * 100), 50);
        }
        return 50;
    }

    function filterMethodsByAmount(methods, amountInCents, currency) {
        var minimums = (EASYCOMMERCE.stripe && EASYCOMMERCE.stripe.payment_method_minimums) || {};
        var maximums = (EASYCOMMERCE.stripe && EASYCOMMERCE.stripe.payment_method_maximums) || {};
        return methods.filter(function(method) {
            var min = minimums[method] && minimums[method][currency];
            if (min && amountInCents < min) return false;
            var max = maximums[method] && maximums[method][currency];
            if (max && amountInCents > max) return false;
            return true;
        });
    }

    async function createPaymentIntent() {
        try {
            const response = await fetch(EASYCOMMERCE.rest_base + '/stripe/payment-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
                body: JSON.stringify({ intent_for: intentMode === 'setup' ? 'setup' : 'payment' }),
            });

            if (!response.ok) {
                throw new Error('Failed to create payment intent');
            }

            return await response.json();
        } catch (error) {
            console.error('Error creating payment intent:', error);
            showPaymentError("Failed to create payment. Please try again.");
            return null;
        }
    }

    async function initializePaymentElement() {

        const cart = await fetchCartData();

        if (!cart) {
            return;
        }

        const currency = (EASYCOMMERCE.currency_code || 'usd').toLowerCase();
        const amountInCents = getCartAmount();
        const enabledPaymentMethods = filterMethodsByAmount(
            EASYCOMMERCE.stripe.enabled_payment_methods || ['card'],
            amountInCents,
            currency
        );

        var elementsOptions = {
            mode: intentMode,
            currency: currency,
            paymentMethodTypes: enabledPaymentMethods,
            appearance: {
                theme: EASYCOMMERCE.stripe.payment_element_theme || 'stripe',
            },
        };

        if (intentMode === 'setup') {
            elementsOptions.setupFutureUsage = 'off_session';
        } else {
            elementsOptions.amount = amountInCents;
        }

        elements = stripe.elements(elementsOptions);

        var paymentElementOptions = {
            layout: {
                type: EASYCOMMERCE.stripe.payment_element_layout || 'tabs',
                defaultCollapsed: false,
            },
            fields: {
                billingDetails: {
                    name: 'auto',
                    email: 'auto',
                    phone: 'auto',
                    address: {
                        country: 'never',
                        postalCode: 'auto',
                    }
                }
            }
        };

        paymentElement = elements.create('payment', paymentElementOptions);

        paymentElement.on('loaderror', function(event) {
            console.error('Stripe load error:', event);

            var $displayError = $("#easycommerce_stripe_payment_errors");

            if (event.error && event.error.message) {
                $displayError.text(event.error.message).css("display", "flex");
            } else {
                $displayError.text("Payment form failed to load. Please refresh.").css("display", "flex");
            }

            $(".easycommerce-css-loader-wrapper").hide();
            $(".easycommerce-checkout-main-btn").css("display", "flex");
        });

        paymentElement.mount('#easycommerce_stripe_payment_form');

        paymentElement.on('change', function (event) {
            var $displayError = $("#easycommerce_stripe_payment_errors");
            $displayError.text(event.error ? event.error.message : "");

            if (event.value && event.value.type) {
                updateSelectedPaymentMethod(event.value.type);
            }
        });
    }

    function updateSelectedPaymentMethod(paymentMethodType) {
        var $paymentMethodInput = $('input[name="meta[stripePaymentMethod]"]');
        if (!$paymentMethodInput.length) {
            $paymentMethodInput = $("<input>", {
                type: "hidden",
                name: "meta[stripePaymentMethod]",
                value: paymentMethodType,
            });
            $("#easycommerce-checkout").append($paymentMethodInput);
        } else {
            $paymentMethodInput.val(paymentMethodType);
        }
    }

    var $stripeForm = $("#easycommerce-checkout");

    $stripeForm.on("submit", async function (event) {
        event.preventDefault();

        if (!$("#easycommerce-payment_method-stripe").prop("checked")) {
            return;
        }

        if (isSubmitting) {
            return;
        }

        await handleStripePaymentSubmission();
    });

    async function handleStripePaymentSubmission() {
        isSubmitting = true;
        showPaymentProcessing();

        try {
            const freshCart = await fetchCartData();
            if (!freshCart) {
                showPaymentError('Could not verify cart. Please refresh and try again.');
                isSubmitting = false;
                return;
            }

            if (intentMode === 'payment') {
                elements.update({ amount: getCartAmount() });
            }

            const billingAddress = collectBillingAddress();

            const {error: submitError} = await elements.submit();
            if (submitError) {
                showPaymentError(submitError.message);
                isSubmitting = false;
                return;
            }

            const paymentIntentData = await createPaymentIntent();

            if (!paymentIntentData || !paymentIntentData.client_secret) {
                showPaymentError("Failed to create payment. Please refresh the page and try again.");
                isSubmitting = false;
                return;
            }

            clientSecret = paymentIntentData.client_secret;
            intentId = paymentIntentData.payment_intent_id || paymentIntentData.setup_intent_id;
            customerId = paymentIntentData.customer_id;

            const intentType = intentMode === 'setup' ? 'setup_intent' : 'payment_intent';
            const customHandling = $(document).triggerHandler('easycommerce_stripe_before_payment', [intentType, billingAddress]);

            if (customHandling === false) {
                isSubmitting = false;
                return;
            }

            if (intentMode === 'setup') {
                showPaymentError("This payment handler is not available. Please refresh and try again.");
                isSubmitting = false;
                return;
            }

            const returnUrl = window.location.origin + window.location.pathname + '?payment_success=1';

            const {error, paymentIntent} = await stripe.confirmPayment({
                elements,
                clientSecret: clientSecret,
                confirmParams: {
                    payment_method_data: {
                        billing_details: billingAddress,
                    },
                    return_url: returnUrl,
                },
                redirect: 'if_required',
            });

            if (error) {
                showPaymentError(error.message);
                isSubmitting = false;
            } else if (paymentIntent) {
                const successStatuses = ['succeeded', 'processing', 'requires_capture', 'requires_confirmation'];

                if (successStatuses.includes(paymentIntent.status)) {
                    addHiddenInput('meta[stripePaymentIntentId]', paymentIntent.id);
                    if (paymentIntent.payment_method) {
                        addHiddenInput('meta[stripePaymentMethodId]', paymentIntent.payment_method);
                    }
                    addHiddenInput('meta[stripeCustomerId]', customerId);
                    addHiddenInput('meta[stripePaymentStatus]', paymentIntent.status);

                    $stripeForm.trigger("payment");

                } else if (paymentIntent.status === 'requires_action') {
                    showPaymentError("Additional verification required. Please complete the payment process.");
                    isSubmitting = false;

                } else if (paymentIntent.status === 'requires_payment_method') {
                    showPaymentError("Payment failed. Please try a different payment method.");
                    isSubmitting = false;

                } else if (paymentIntent.status === 'canceled') {
                    showPaymentError("Payment was canceled. Please try again.");
                    isSubmitting = false;

                } else {
                    showPaymentError("Payment status: " + paymentIntent.status + ". Please contact support if this persists.");
                    isSubmitting = false;
                }
            } else {
                showPaymentError("Payment could not be processed. Please try again.");
                isSubmitting = false;
            }
        } catch (error) {
            console.error("Error processing payment:", error);
            showPaymentError("An error occurred while processing your payment. Please try again.");
            isSubmitting = false;
        }
    }

    function collectBillingAddress() {
        return {
            name: $('input[name="billing_address[first_name]"]').val() + ' ' + $('input[name="billing_address[last_name]"]').val(),
            email: $('input[name="billing_address[email]"]').val(),
            phone: $('input[name="billing_address[phone]"]').val(),
            address: {
                line1: $('input[name="billing_address[address_1]"]').val(),
                line2: $('input[name="billing_address[address_2]"]').val(),
                city: $('input[name="billing_address[city]"]').val(),
                state: $('input[name="billing_address[state]"]').val(),
                postal_code: $('input[name="billing_address[postcode]"]').val(),
                country: $('select[name="billing_address[country]"]').val() || 'US',
            }
        };
    }

    function addHiddenInput(name, value) {
        $stripeForm.find('input[name="' + name + '"]').remove();

        var $input = $("<input>", {
            type: "hidden",
            name: name,
            value: value,
        });
        $stripeForm.append($input);
    }

    function showPaymentProcessing() {
        $(".easycommerce-checkout-main-btn").hide();
        $(".easycommerce-css-loader-wrapper").css("display", "flex");
    }

    function showPaymentError(message) {
        $("#easycommerce_stripe_payment_errors").text(message);
        $(".easycommerce-css-loader-wrapper").hide();
        $(".easycommerce-checkout-main-btn").css("display", "flex");
    }

    $(document).on('easycommerce_cart_updated', async function () {
        if (!elements) return;

        const cart = await fetchCartData();
        if (cart && intentMode === 'payment') {
            elements.update({ amount: getCartAmount() });
        }
    });

    $(document).ready(function () {
        var urlParams = new URLSearchParams(window.location.search);

        const setupIntentId = urlParams.get('setup_intent');
        const setupIntentClientSecret = urlParams.get('setup_intent_client_secret');

        if (setupIntentId && setupIntentClientSecret) {
            $('#easycommerce_stripe_payment_errors').text("Verifying payment...").css({
                display: "flex",
            });

            showPaymentProcessing();

            stripe.retrieveSetupIntent(setupIntentClientSecret)
                .then(function(result) {
                    if (result.error) {
                        showPaymentError(result.error.message);
                        window.history.replaceState({}, document.title, window.location.pathname);
                        return;
                    }

                    const setupIntent = result.setupIntent;

                    if (setupIntent.status === 'succeeded') {
                        addHiddenInput('meta[stripeSetupIntentId]', setupIntent.id);

                        if (setupIntent.payment_method) {
                            addHiddenInput('meta[stripePaymentMethodId]', setupIntent.payment_method);
                        }

                        addHiddenInput('meta[stripePaymentStatus]', setupIntent.status);

                        $('#easycommerce_stripe_payment_errors').text("Payment successful! Creating order...").css({
                            display: "flex",
                        });

                        $('#easycommerce-checkout').trigger('payment');

                    } else {
                        showPaymentError("Payment method could not be saved. Status: " + setupIntent.status);
                        window.history.replaceState({}, document.title, window.location.pathname);
                    }
                })
                .catch(function(error) {
                    console.error("Error retrieving setup intent:", error);
                    showPaymentError("Failed to verify payment. Please contact support.");
                    window.history.replaceState({}, document.title, window.location.pathname);
                });

            return;
        }

        const paymentIntentId = urlParams.get('payment_intent');
        const paymentIntentClientSecret = urlParams.get('payment_intent_client_secret');

        if (paymentIntentId && paymentIntentClientSecret) {
            $('#easycommerce_stripe_payment_errors').text("Verifying payment...").css({
                display: "flex",
            });
            
            showPaymentProcessing();

            stripe.retrievePaymentIntent(paymentIntentClientSecret)
                .then(function(result) {
                    if (result.error) {
                        showPaymentError(result.error.message);
                        window.history.replaceState({}, document.title, window.location.pathname);
                        return;
                    }

                    const paymentIntent = result.paymentIntent;
                    const successStatuses = ['succeeded', 'processing', 'requires_capture', 'requires_confirmation'];
                    
                    if (successStatuses.includes(paymentIntent.status)) {
                        addHiddenInput('meta[stripePaymentIntentId]', paymentIntent.id);
                        
                        if (paymentIntent.payment_method) {
                            addHiddenInput('meta[stripePaymentMethodId]', paymentIntent.payment_method);
                        }
                        
                        if (paymentIntent.customer) {
                            addHiddenInput('meta[stripeCustomerId]', paymentIntent.customer);
                        }
                        
                        addHiddenInput('meta[stripePaymentStatus]', paymentIntent.status);
                        
                        if (paymentIntent.payment_method_types && paymentIntent.payment_method_types.length > 0) {
                            addHiddenInput('meta[stripePaymentMethod]', paymentIntent.payment_method_types[0]);
                        }
                        
                        $('#easycommerce_stripe_payment_errors').text("Payment successful! Creating order...").css({
                            display: "flex",
                        });
                        
                        $('#easycommerce-checkout').trigger('payment');
                        
                    } else if (paymentIntent.status === 'requires_payment_method') {
                        showPaymentError("Payment failed. Please try again with a different payment method.");
                        window.history.replaceState({}, document.title, window.location.pathname);
                        
                    } else if (paymentIntent.status === 'canceled') {
                        showPaymentError("Payment was canceled. Please try again.");
                        window.history.replaceState({}, document.title, window.location.pathname);
                        
                    } else if (paymentIntent.status === 'requires_action') {
                        showPaymentError("Additional action required. Please complete the payment process.");
                        window.history.replaceState({}, document.title, window.location.pathname);
                        
                    } else {
                        showPaymentError("Payment could not be completed. Status: " + paymentIntent.status);
                        window.history.replaceState({}, document.title, window.location.pathname);
                    }
                })
                .catch(function(error) {
                    console.error("Error retrieving payment intent:", error);
                    showPaymentError("Failed to verify payment. Please contact support.");
                    window.history.replaceState({}, document.title, window.location.pathname);
                });
        }
    });

    $(document).on('change', 'input[name="easycommerce-payment_method"]', function () {
        if ($(this).val() === 'stripe' && $(this).is(':checked')) {
            if (!paymentElement) {
                initializePaymentElement();
            }
        }
    });

    $(document).on("easycommerceRenderPaymentForm", function () {
        try {
            if (paymentElement) {
                paymentElement.unmount();
            }
        } catch (e) {
            console.warn('Stripe payment element unmount failed:', e);
        }
        clientSecret = null;
        intentId = null;
        customerId = null;
        elements = null;
        paymentElement = null;
        isSubmitting = false;
        cartData      = null;
        initializePaymentElement();
    });

    window.easycommerceStripe = {
        stripe: stripe,
        getElements: function() { return elements; },
        getPaymentElement: function() { return paymentElement; },
        getIntentId: function() { return intentId; },
        getCustomerId: function() { return customerId; },
        getClientSecret: function() { return clientSecret; },
        getIntentMode: function() { return intentMode; },
        addHiddenInput: addHiddenInput,
        showPaymentProcessing: showPaymentProcessing,
        showPaymentError: showPaymentError,
        collectBillingAddress: collectBillingAddress,
    };
});