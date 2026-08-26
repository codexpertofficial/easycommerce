jQuery(function ($) {
    const { __, sprintf } = wp.i18n;

    const VALIDATION_RULES = {
        name:     { pattern: /^[\p{L}\p{M}\s'\-.]+$/u, message: __( 'Only letters, spaces, hyphens, apostrophes, and periods are allowed.', 'easycommerce' ) },
        email:    { pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,     message: __( 'Please enter a valid email address.', 'easycommerce' ) },
        phone:    { pattern: /^[+\d\s\-(). ]{6,20}$/,           message: __( 'Please enter a valid phone number (6–20 characters).', 'easycommerce' ) },
        postcode: { pattern: /^[A-Za-z0-9\s\-]{3,10}$/,         message: __( 'Please enter a valid postcode.', 'easycommerce' ) },
    };

    function showFieldError( $input, message ) {
        var $wrapper = $input.closest( '.easycommerce-field-wrapper, .easycommerce-checkout_field-wrapper' );
        $wrapper.addClass( 'easycommerce-field-has-error' );
        $input.css( 'border-color', '#FF3A52' );
        $input[0].setCustomValidity( message );
    }

    function clearFieldError( $input ) {
        var $wrapper = $input.closest( '.easycommerce-field-wrapper, .easycommerce-checkout_field-wrapper' );
        $wrapper.removeClass( 'easycommerce-field-has-error' );
        $input.css( 'border-color', '' );
        $input[0].setCustomValidity( '' );
    }

    function validateFieldGroup( $fields, isValid, $firstError ) {
        $fields.each( function () {
            var $input     = $( this );
            var fieldId    = $input.data( 'field_id' );
            var value      = ( $input.val() || '' ).toString().trim();
            var isRequired = $input.prop( 'required' );
            var validation = $input.data( 'validation' );

            clearFieldError( $input );

            if ( isRequired && value === '' ) {
                var label = $input.closest( '.easycommerce-field-wrapper, .easycommerce-checkout_field-wrapper' ).find( 'label' ).text().replace( '*', '' ).trim();
                // translators: %s: checkout field label.
                showFieldError( $input, sprintf( __( '%s is required.', 'easycommerce' ), label || fieldId ) );
                if ( ! $firstError ) $firstError = $input;
                isValid = false;
                return;
            }

            if ( value !== '' && validation && VALIDATION_RULES[ validation ] ) {
                var rule = VALIDATION_RULES[ validation ];
                if ( ! rule.pattern.test( value ) ) {
                    showFieldError( $input, rule.message );
                    if ( ! $firstError ) $firstError = $input;
                    isValid = false;
                }
            }
        } );

        return { isValid: isValid, $firstError: $firstError };
    }

    function validateCheckoutFields() {
        var isValid    = true;
        var $firstError = null;

        var billing = validateFieldGroup(
            $( '#easycommerce-checkout .easycommerce-checkout-billing .easycommerce-field' ),
            isValid,
            $firstError
        );
        isValid     = billing.isValid;
        $firstError = billing.$firstError;

        var $sameAsShipping = $( '#easycommerce-checkout-same-as-shipping' );
        if ( $sameAsShipping.length && ! $sameAsShipping.is( ':checked' ) ) {
            var shipping = validateFieldGroup(
                $( '#easycommerce-checkout .easycommerce-checkout-shipping .easycommerce-field' ),
                isValid,
                $firstError
            );
            isValid     = shipping.isValid;
            $firstError = shipping.$firstError;
        }

        if ( $firstError ) {
            $firstError[0].reportValidity();
        }

        return isValid;
    }

    function validateSelectedMethods() {
        var isValid = true;

        if ( $( '.easycommerce-shipping-method' ).length ) {
            if ( ! $( '.easycommerce-shipping-method:checked' ).length ) {
                $( '.easycommerce-shipping-method-error' ).removeClass( 'hidden' );
                isValid = false;
            } else {
                $( '.easycommerce-shipping-method-error' ).addClass( 'hidden' );
            }
        }

        if ( $( 'input.easycommerce-payment_method' ).length ) {
            if ( ! $( 'input.easycommerce-payment_method:checked' ).length ) {
                $( '.easycommerce-payment-method-error' ).removeClass( 'hidden' );
                isValid = false;
            } else {
                $( '.easycommerce-payment-method-error' ).addClass( 'hidden' );
            }
        }

        return isValid;
    }

    // Clear per-field errors as the user corrects them
    $( document ).on( 'input change', '#easycommerce-checkout .easycommerce-checkout-billing .easycommerce-field, #easycommerce-checkout .easycommerce-checkout-shipping .easycommerce-field', function () {
        clearFieldError( $( this ) );
    } );

    let render_message = (response, msgDivId) => {
        let color;
        let message;

        if (response && response.success == true) {
            if (!msgDivId) return;

            color   = "#4bc30f";
            message = response.data && response.data.message ? response.data.message : "";
        } else {
            msgDivId = msgDivId || "#easycommerce-checkout-order-error";
            color    = "#FF3A52";
            message  = "An error occurred. Please try again.";

            try {
                // Most response_error() callers pass a plain string, not { message }.
                const parsed = JSON.parse(response.responseText);
                if (parsed && parsed.data) {
                    if (typeof parsed.data === "string") {
                        message = parsed.data;
                    } else if (parsed.data.message) {
                        message = parsed.data.message;
                    }
                }
            } catch (e) {}
        }

        if (!message) return;

        const isError = color === "#FF3A52";

        $(msgDivId).css("color", color).text(message).slideDown(400, function () {
            if (isError) {
                this.scrollIntoView({ behavior: "smooth", block: "center" });
            }
        });
    };
    const dispatchEvent = (name) => document.dispatchEvent(new Event(name));
    const paymentEvent = () => dispatchEvent("easycommerceRenderPaymentForm");
    const fragmentUpdated = () => dispatchEvent("easycommerceFragmentUpdated");
    /**
     * Render fragment HTML from the cart response
     */
    let render_fragments = (cart) => {
        $(".easycommerce-cart-wrapper").html(cart.fragments.items);
        $(".easycommerce-summary-wrapper").html(cart.fragments.summary);
        if( cart.payment_update_required ) {
            $(".easycommerce-payment-methods").html(cart.fragments.payment_methods);
            paymentEvent();
        }
        fragmentUpdated();
    };

    // This endpoint writes state, so an older response must never win.
    var shippingSeq = 0;
    var shippingXhr = null;
    var shippingAddressSaved = false;

    function updateShippingMethods() {
        let form = $("#easycommerce-checkout");
        let data = form.serializeArray();

        // Mirrored shipping selects lag behind the geo cascade, so read billing.
        if ($("#easycommerce-checkout-same-as-shipping").is(":checked")) {
            let shippingKeys = ["first_name", "last_name", "email", "phone", "address_1", "address_2", "country", "state", "city", "postcode"];

            data = $.grep(data, function (field) {
                return field.name.indexOf("shipping_address[") !== 0;
            });

            shippingKeys.forEach(function (key) {
                let billingField = $('.easycommerce-checkout-billing .easycommerce-field[data-field_id="' + key + '"]');
                if (billingField.length) {
                    data.push({ name: "shipping_address[" + key + "]", value: billingField.val() });
                }
            });
        }

        var seq = ++shippingSeq;

        shippingAddressSaved = data.some(function (field) {
            return field.name === "shipping_address[country]" && field.value;
        });

        if (shippingXhr && shippingXhr.abort) {
            shippingXhr.abort();
        }

        shippingXhr = $.ajax({
            // POST, not GET: the address must not land in access logs.
            url: `${EASYCOMMERCE.rest_base}/cart/shipping/calculate`,
            type: "POST",
            data: data,
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (response) {
                if (seq !== shippingSeq) return;
                render_message(response);
                render_fragments(response.data.cart);
            },
            error: function (error) {
                if (seq !== shippingSeq || (error && error.statusText === "abort")) return;
                render_message(error, "#easycommerce-checkout-order-error");
            },
            complete: function () {
                if (seq === shippingSeq) {
                    shippingXhr = null;
                }
            },
        });
    }

    function updateSelectedMethod() {
        var selectedMethod = $("input[name='easycommerce-payment_method']:checked").val();
        if (!selectedMethod) {
            return;
        }
        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/cart/payment`,
            type: "POST",
            dataType: "JSON",
            data: { payment_method: selectedMethod },
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            }
        });
    }
    //shipping related functions
    $(document).ready(function () {
        paymentEvent();
        updateSelectedMethod();
        const defaultShipping = $(".easycommerce-shipping-method:checked");
        if (defaultShipping.length) {
            const method_id = defaultShipping.val();
            sendShippingMethod(method_id);
        }
    });

    $(document).on("change", ".easycommerce-shipping-method", function (e) {
        const method_id = $(this).val();
        sendShippingMethod(method_id);
    });

    function allShippingFieldsFilled() {
        var same = $("#easycommerce-checkout-same-as-shipping").is(":checked");
        var $block = (same || $(".easycommerce-checkout-shipping").length === 0) ? $(".easycommerce-checkout-billing") : $(".easycommerce-checkout-shipping");
        var required = ["country"];
        for (var i = 0; i < required.length; i++) {
            if (!$block.find('.easycommerce-field[data-field_id="' + required[i] + '"]').val()) {
                return false;
            }
        }
        return true;
    }

    // One write per address change: the country cascade fires state and city too.
    var shippingUpdateTimer;
    var shippingFallbackTimer;
    var shippingPending = false;
    var geoRequests = 0;
    var SHIPPING_UPDATE_DELAY = 300;
    var GEO_CASCADE_TIMEOUT = 5000;

    function runShippingUpdate() {
        clearTimeout(shippingFallbackTimer);
        shippingPending = false;

        // An emptied address must reach the server, or the old one keeps charging.
        if (allShippingFieldsFilled() || shippingAddressSaved) {
            updateShippingMethods();
        }
    }

    function triggerShippingUpdate() {
        clearTimeout(shippingUpdateTimer);
        shippingUpdateTimer = setTimeout(function () {
            if (geoRequests > 0) {
                // The cascade is still repopulating and will re-fire change.
                shippingPending = true;
                clearTimeout(shippingFallbackTimer);
                shippingFallbackTimer = setTimeout(runShippingUpdate, GEO_CASCADE_TIMEOUT);
                return;
            }

            runShippingUpdate();
        }, SHIPPING_UPDATE_DELAY);
    }

    function geoRequestStarted() {
        geoRequests++;
    }

    function geoRequestFinished() {
        geoRequests = Math.max(0, geoRequests - 1);

        if (geoRequests === 0 && shippingPending) {
            triggerShippingUpdate();
        }
    }

    $(document).on("change", ".easycommerce-checkout-billing .easycommerce-field", function (e) {
        let field_id    = $(this).data("field_id");
        var same        = $("#easycommerce-checkout-same-as-shipping").is(":checked");

        if ($(".easycommerce-checkout-shipping").length === 0) {
            if (field_id === "country" || field_id === "state" || field_id === "city" || field_id === "postcode") {
                triggerShippingUpdate();
            }
            return;
        }

        if ( field_id === "country" ) {
            const billingCountry = $( this ).val();
            const allowedShippingCountries = Object.keys( EASYCOMMERCE.shipping.countries );
            const $sameAsShippingCheckbox = $( "#easycommerce-checkout-same-as-shipping" );
            const $sameAsShippingLabel = $( "label:has(#easycommerce-checkout-same-as-shipping)" );

            // Only lock "Same as Billing" off when a *chosen* billing country
            // cannot be shipped to. An empty/unset country must keep the default
            // (checked + enabled): the field starts empty, so without this guard
            // the toggle loads unchecked and the shipping fields become wrongly
            // required, and a guest who leaves them blank is rejected with
            // "First name is required".
            if ( billingCountry && allowedShippingCountries.length > 0 && ! allowedShippingCountries.includes( billingCountry ) ) {
                $sameAsShippingCheckbox.prop( { checked: false, disabled: true } ).data( "sameAsForcedOff", true );
                $sameAsShippingCheckbox.add( $sameAsShippingLabel ).css( {
                    opacity: 0.5,
                    cursor: "not-allowed"
                } );
                $( ".easycommerce-checkout-shipping-same-as-address" ).slideDown();
                $( '.easycommerce-checkout-shipping .easycommerce-field[data-field_id="country"], .easycommerce-checkout-shipping .easycommerce-field[data-field_id="state"], .easycommerce-checkout-shipping .easycommerce-field[data-field_id="city"], .easycommerce-checkout-shipping .easycommerce-field[data-field_id="postcode"]' ).val( "" );
                updateShippingMethods();
                return;
            }

            $sameAsShippingCheckbox.prop( "disabled", false );
            $sameAsShippingCheckbox.add( $sameAsShippingLabel ).css( {
                opacity: 1,
                cursor: "pointer"
            } );

            // If the toggle was force-disabled for a previously unshippable
            // country, restore its default (checked, shipping hidden) now that the
            // billing country is shippable — but never override a manual uncheck.
            if ( $sameAsShippingCheckbox.data( "sameAsForcedOff" ) ) {
                $sameAsShippingCheckbox.prop( "checked", true ).data( "sameAsForcedOff", false );
                $( ".easycommerce-checkout-shipping-same-as-address" ).slideUp();
                same = true;
            }
        }

        // Copy billing fields to shipping fields if "Same as shipping" is checked, then trigger
        if (same && (field_id === "country" || field_id === "state" || field_id === "city" || field_id === "postcode")) {
            let val = $(this).val();
            $(`.easycommerce-checkout-shipping .easycommerce-field[data-field_id="${field_id}"]`).val(val);
            triggerShippingUpdate();
        }
    });

    $(document).on("change", ".easycommerce-checkout-shipping .easycommerce-field", function () {
        if (!$("#easycommerce-checkout-same-as-shipping").is(":checked")) {
            const field_id = $(this).data("field_id");
            if (field_id === "country" || field_id === "state" || field_id === "city" || field_id === "postcode") {
                triggerShippingUpdate();
            }
        }
    });

    /**
     * Choose a shipping_method for the cart
     */
    function sendShippingMethod(method_id) {
        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/cart/shipping`,
            type: "POST",
            data: { id: method_id },
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (response) {
                if (response.success === true) {
                    render_fragments(response.data.cart);
                }
            },
            error: function (error) {
                console.error("Shipping update error:", error);
            },
        });
    }

    $(document).on( "change", "#easycommerce-checkout-same-as-shipping", function (e) {
        if (!$(this).is(":checked")) {
            $( ".easycommerce-checkout-shipping-same-as-address" ).slideDown();
            $( '.easycommerce-checkout-shipping .easycommerce-field[data-field_id="state"], .easycommerce-checkout-shipping .easycommerce-field[data-field_id="city"], .easycommerce-checkout-shipping .easycommerce-field[data-field_id="postcode"]' ).val( "" );
        } else {
            $(".easycommerce-checkout-shipping-same-as-address").slideUp();
        }
        updateShippingMethods();
    });

    /**
     * When a country is chosen, generate its states
     */
    $('.easycommerce-field[data-field_id="country"]').change(function (e) {
        let country         = $(this).val();
        let wrap            = $(this).closest(".easycommerce-address");
        let address_type    = wrap.data("address_type");

        // /geo/states/ has no route without a country, and its 404 shows an error.
        if (!country) {
            return;
        }

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/geo/states/${country}`,
            type: "GET",
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
                geoRequestStarted();
            },
            complete: geoRequestFinished,
            success: function (response) {
                if (response.success === true) {
                    let stateHTML = "";
                    $(
                        '.easycommerce-field[data-field_id="state"]',
                        wrap
                    ).html("");

                    // Check if statesData is an object (for key-value pairs)
                    if (
                        typeof response.data.states === "object" &&
                        !Array.isArray(response.data.states)
                    ) {
                        Object.entries(response.data.states).forEach(
                            ([code, state]) => {
                                stateHTML += `<option value="${code}">${state}</option>`;
                            }
                        );
                    }
                    // If statesData is an array (list of state names)
                    else if (Array.isArray(response.data.states)) {
                        response.data.states.forEach((state, index) => {
                            stateHTML += `<option value="${state}">${state}</option>`;
                        });
                    }
                    $('.easycommerce-field[data-field_id="state"]', wrap)
                        .html(stateHTML)
                        .val(EASYCOMMERCE.customer.address[address_type].state)
                        .change();
                }
            },
            error: function (error) {
                render_message(error, "#easycommerce-checkout-order-error");
            },
        });
    }).change();

    /**
     * When a state is chosen, generate its cities
     * @todo can be further improved to check corresponding data for shipping and billing
     */
    $('.easycommerce-field[data-field_id="state"]').change(function (e) {
        let wrap            = $(this).closest(".easycommerce-address");
        let address_type    = wrap.data("address_type");
        let country         = $(`#easycommerce-field-${address_type}_country`).val();
        let state           = $(this).val();

        if (!country) {
            return;
        }

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/geo/cities/${country}`,
            type: "GET",
            data: { state: state },
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
                geoRequestStarted();
            },
            complete: geoRequestFinished,
            success: function (response) {
                if (response.success === true) {
                    let cityHTML = "";
                    if (
                        response.data.cities &&
                        ((typeof response.data.cities === "object" &&
                            Object.keys(response.data.cities).length > 0) ||
                            (Array.isArray(response.data.cities) &&
                                response.data.cities.length > 0))
                    ) {
                        // Ensure the city field is a <select> element if options are available
                        if (
                            !$(`#easycommerce-field-${address_type}_city`).is(
                                "select"
                            )
                        ) {
                            $(`#easycommerce-field-${address_type}_city`)
                                .replaceWith(`
                                <select id="easycommerce-field-${address_type}_city" name="${address_type}_address[city]" required class="easycommerce-field" data-field_id="city"></select>
                            `);
                        }

                        // Populate select with options if cities data is not empty
                        if (
                            typeof response.data.cities === "object" &&
                            !Array.isArray(response.data.cities)
                        ) {
                            Object.entries(response.data.cities).forEach(
                                ([code, city]) => {
                                    cityHTML += `<option value="${code}">${city}</option>`;
                                }
                            );
                        } else if (Array.isArray(response.data.cities)) {
                            response.data.cities.forEach((city) => {
                                cityHTML += `<option value="${city}">${city}</option>`;
                            });
                        }
                        $(`#easycommerce-field-${address_type}_city`)
                            .html(cityHTML)
                            .val(EASYCOMMERCE.customer.address[address_type].city)
                            .change();
                    } else {
                        let cityValue = $(`#easycommerce-field-${address_type}_city`).val()
                            || EASYCOMMERCE.customer.address[address_type].city
                            || '';

                        $(`#easycommerce-field-${address_type}_city`)
                            .replaceWith(`
                            <input type="text" id="easycommerce-field-${address_type}_city" name="${address_type}_address[city]" placeholder="${__( 'Enter city', 'easycommerce' )}" required class="easycommerce-field" data-field_id="city">
                        `);
                        $(`#easycommerce-field-${address_type}_city`).val(cityValue).trigger('change');
                    }
                }
            },
            error: function (error) {
                render_message(error, "#easycommerce-checkout-order-error");
            },
        });
    });

    /**
     * Coupon button apply button visiable
     */
    $(document).on("input", "#easycommerce-coupon-field", function () {
        if ($(this).val().trim() !== "") {
            $("#easycommerce-coupon-apply").attr(
                "style",
                "background-color:#272435 !important; color:#ffffff !important;"
            );
        } else {
            $("#easycommerce-coupon-apply").attr(
                "style",
                "background-color:#F8F8F8 !important; color:#737791 !important;"
            );
        }
    });


    /**
     * Apply a coupon
     */
       $(document).on("click", "#easycommerce-coupon-apply", function (e) {
        let $btn   = $(this);
        let coupon = $("#easycommerce-coupon-field").val();
 
        if (coupon == "") return;
        if ($btn.prop("disabled")) return; 
 
        $("#easycommerce-checkout-coupon-message").slideUp(); 
        $btn
            .css("min-width", $btn.outerWidth()) 
            .prop("disabled", true)
            .addClass("easycommerce-is-loading");
 
        const MIN_LOADING_MS = 800; 
        const startedAt      = Date.now();

        $btn
            .prop("disabled", true)
            .addClass("easycommerce-is-loading");


        requestAnimationFrame(() => {
            $.ajax({
                url: `${EASYCOMMERCE.rest_base}/cart/coupon`,
                type: "POST",
                data: { code: coupon },
                dataType: "JSON",
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
                },
                success: function (success) {
                    render_fragments(success.data.cart);
                    render_message(success, "#easycommerce-checkout-coupon-message");
                    $(".easycommerce-discount-wrapper").show();
                    $(document).trigger("easycommerce_cart_updated");
                },
                error: function (error) {
                    render_message(error, "#easycommerce-checkout-coupon-message");
                },
                complete: function () {
                    let elapsed = Date.now() - startedAt;
                    let waitLeft = Math.max(0, MIN_LOADING_MS - elapsed);

                    setTimeout(function () {
                        $btn
                            .prop("disabled", false)
                            .removeClass("easycommerce-is-loading")
                            .css("min-width", "");
                    }, waitLeft);
                },
            });
        });

    });
 
    $(document).on("click", ".easycommerce-remove-coupon", function (e) {
        let $btn = $(this);
        let coupon = $btn.data("id");

        if (!coupon) return;
        if ($btn.data("busy")) return;

        $btn.data("busy", true);

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/cart/coupon/remove`,
            type: "POST",
            data: { code: coupon },
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (success) {
                $(".easycommerce-discount-wrapper").show();
                render_fragments(success.data.cart);
                render_message(success, "#easycommerce-checkout-coupon-message");
                $("#easycommerce-checkout-coupon-message").css("color", "#ff3a52");
                $(document).trigger("easycommerce_cart_updated");
            },
            error: function (error) {
                render_message(error, "#easycommerce-checkout-coupon-message");
            },
            complete: function () {
                $btn.data("busy", false);
            },
        });
    });


    /**
     * Place the order
     */
    var orderInFlight = false;

    $("#easycommerce-checkout").on("payment", function (e) {
        e.preventDefault();

        if (orderInFlight) {
            return;
        }

        orderInFlight = true;

        $(".easycommerce-checkout-main-btn").hide();
        $(".easycommerce-css-loader-wrapper").show();

        let form = $(this);
        let data = form.serializeArray();

        // Checkout fields are filterable, so either name part may be absent.
        let firstName = $("#easycommerce-field-billing_first_name").val() || "";
        let lastName  = $("#easycommerce-field-billing_last_name").val() || "";

        let customer = [
            {
                name: "customer[name]",
                value: [firstName, lastName].filter(Boolean).join(" ").trim(),
            },
            {
                name: "customer[email]",
                value: $("#easycommerce-field-billing_email").val(),
            },
            {
                name: "customer[first_name]",
                value: firstName,
            },
            {
                name: "customer[last_name]",
                value: lastName,
            },
            {
                name: "customer[meta][phone]",
                value: $("#easycommerce-field-billing_phone").val(),
            },
            {
                name: "customer[meta][country]",
                value: $("#easycommerce-field-billing_country").val(),
            },
        ];

        data.push(...customer);

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/orders`,
            type: "POST",
            data: data,
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (response) {
                render_message(response);

                // A filter returning false, null or nothing must not navigate to "undefined".
                if (response.data && response.data.redirect) {
                    window.location = response.data.redirect;
                    return;
                }

                orderInFlight = false;
                $(".easycommerce-checkout-main-btn").show();
                $(".easycommerce-css-loader-wrapper").hide();
            },
            error: function (error) {
                orderInFlight = false;
                $(".easycommerce-checkout-main-btn").show();
                $(".easycommerce-css-loader-wrapper").hide();
                render_message(error);
                let message = __( "An error occurred. Please try again.", 'easycommerce' );
                try {
                    // data can be a string, { message } or a WP REST envelope with neither.
                    const parsed = JSON.parse(error.responseText);
                    if (parsed && parsed.data && parsed.data.message) {
                        message = parsed.data.message;
                    } else if (parsed && typeof parsed.data === "string") {
                        message = parsed.data;
                    } else if (parsed && parsed.message) {
                        message = parsed.message;
                    }
                } catch (e) {}
                $("#easycommerce-checkout-order-error").text(message).slideDown(400, function () {
                    this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            },
        });
    });

    /**
     * On clicking `minus` button, trigger quantity change
     */
    $(document).on("click", ".easycommerce-checkout-cart-quantity-minus-btn", function(e) {
        let quantity_field = $(this).next(
            ".easycommerce-cart-quantity-input"
        );
        let value = quantity_field.val();
        value > 1 ? value-- : (value = 1);
        quantity_field.val(value);
        quantity_field.trigger("input");
    });

    /**
     * On clicking `plus` button, trigger quantity change
     */
    $(document).on("click", ".easycommerce-checkout-cart-quantity-plus-btn", function(e) {
        let quantity_field      = $(this).prev(".easycommerce-cart-quantity-input");
        let value               = quantity_field.val();
        let maxQuantity         = parseInt(quantity_field.attr("data-stock-count"));
        let type                = quantity_field.attr("data-type");

        if( type == "digital" && value >= 1 ) return;

        if (value >= maxQuantity) return;

        value++;
        quantity_field.val(value);
        quantity_field.trigger("input");
    });

    /**
     * On quantity change, update the cart
     */
    // One timer per row: a shared timer drops the first row's update.
    let qtyUpdateTimers = {};
    $(document).on("input", ".easycommerce-cart-quantity-input", function(e) {
        let $field   = $(this);
        let maxStock = parseInt($field.attr("data-stock-count"));
        let quantity = parseInt($field.val(), 10);

        // An emptied field serialises as "NaN", which absint() commits as 0.
        if (isNaN(quantity)) {
            return;
        }

        if (!isNaN(maxStock) && quantity > maxStock) {
            quantity = maxStock;
            $field.val(quantity);
        }

        let product_id = $field.attr("product-id");
        let price_id = $field.attr("price-id");
        let data = {
            quantity: quantity,
            id: product_id,
            price_id: price_id,
        };

        // Debounce so typing a multi-digit number sends one request, not one per keystroke.
        let rowKey = product_id + "|" + price_id;

        clearTimeout(qtyUpdateTimers[rowKey]);
        qtyUpdateTimers[rowKey] = setTimeout(function () {
            $.ajax({
                url: `${EASYCOMMERCE.rest_base}/cart/update`,
                type: "POST",
                data: data,
                dataType: "JSON",
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
                },
                success: function (response) {
                    if (response.success === true) {
                        render_fragments(response.data.cart);
                        // Tiers are matched server side, so recalculate the cart.
                        triggerShippingUpdate();
                    }
                },
                error: function (error) {
                    render_message(error, "#easycommerce-checkout-order-error");
                },
            });
        }, 400);
    });

    // Delete Product
    $(document).on("click", ".easycommerce-checkout-cart-item-delete-btn", function(e) {
        let product_id = $(this).attr("product-id");
        let price_id = $(this).attr("price-id");
        let $currentBtn = $(this);
        let data = {
            id: product_id,
            price_id: price_id,
        };

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/cart/remove`,
            type: "DELETE",
            data: data,
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (response) {
                $currentBtn.closest(".easycommerce-cart-product").remove();

                if (response.success === true) {
                    let cart = response.data.cart;
                    if (!cart.items?.length) {
                        if (response.data.redirect) {
                            window.location = response.data.redirect;
                        } else if (response.data.reload) {
                            window.location.reload();
                        }
                        return;
                    }
                    render_fragments(cart);
                    if( typeof cart.fragments.shipping_fee == "undefined" ) {
                        $('.easycommerce-shipping-method-wrapper').remove();
                    }
                }
            },

            error: function (error) {
                render_message(error, "#easycommerce-checkout-order-error");
            },
        });
    });

    // Bound on the form so it runs before the gateways and can stop them.
    $("#easycommerce-checkout").on("submit", function (e) {
        // Enter in any text input submits the form again while the first order is open.
        if ( orderInFlight || ! validateCheckoutFields() || ! validateSelectedMethods() ) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });

    $(document).on("submit", "#easycommerce-checkout",async function(e) {
        e.preventDefault();

        var $method = $("input.easycommerce-payment_method:checked").val();

        if ($method === "cash-on-delivery") {
            $(this).trigger("payment");
        }
        // The container is always rendered, so count the radios inside it.
        if ( $( 'input.easycommerce-payment_method' ).length === 0 ) {
            $(this).trigger("payment");
        }
    });

    // Toggle payment methods. The class is on the wrapper too, so scope to the input.
    $(document).on("change", "input.easycommerce-payment_method", function (e) {
        updateSelectedMethod();
        let method = $(this).val();
        if ($(this).is(":checked")) {
            $(".easycommerce-payment_method-form")
                .not(`#easycommerce-payment_method-${method}-form`)
                .stop(true, true)
                .slideUp();

            $(`#easycommerce-payment_method-${method}-form`)
                .stop(true, true)
                .slideDown();
        }
    });


    const $shippingCheckbox = $("#easycommerce-checkout-same-as-shipping");
    const $shippingInputs = $(".easycommerce-checkout-shipping input, .easycommerce-checkout-shipping select");

    function toggleRequired() {
        if ($shippingCheckbox.is(":checked")) {
            $shippingInputs.removeAttr("required");
        } else {
            $shippingInputs.attr("required", "required");
        }
    }

    toggleRequired();

    $shippingCheckbox.on("change", toggleRequired);


    // ── Shipping methods loader ──
    var $sw = $(".easycommerce-summary-wrapper");
    if ($sw.length) {
        $sw.wrap('<div class="easycommerce-summary-container" style="position: relative;"></div>');

        var $ol = $(
            '<div class="easycommerce-shipping-overlay">' +
                '<div class="easycommerce-shipping-loader">' +
                    '<div class="spinner"></div>' +
                    '<span>Loading shipping methods...</span>' +
                '</div>' +
            '</div>'
        );
        $sw.parent().append($ol);

        var ar = 0;
        $(document).on("ajaxSend", function (e, x, s) {
            if (s.url.indexOf("/cart/shipping") !== -1) {
                ar++;
                $ol.addClass("active");
            }
        }).on("ajaxComplete", function (e, x, s) {
            if (s.url.indexOf("/cart/shipping") !== -1) {
                ar--;
                if (ar <= 0) { ar = 0; $ol.removeClass("active"); }
            }
        });
    }
});
