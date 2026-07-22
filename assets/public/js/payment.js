jQuery(function ($) {
    "use strict";

    var $form    = $("#easycommerce-checkout");
    var order_id = $form.data("order-id");

    if ( ! order_id ) return;

    /**
     * checkout.js declares render_message() with `let` inside its own jQuery
     * closure, so it is never global and cannot be reached from here. Write
     * straight to the shared error div instead — the same one the checkout
     * templates and the gateway add-ons already use.
     */
    var error_div = "#easycommerce-checkout-order-error";

    var show_error = function (message) {
        $(error_div).text(message).slideDown(400, function () {
            this.scrollIntoView({ behavior: "smooth", block: "center" });
        });
    };

    var clear_error = function () {
        $(error_div).stop(true, true).hide().text("");
    };

    var reset_form = function () {
        $(".easycommerce-checkout-main-btn").show();
        $(".easycommerce-css-loader-wrapper").hide();
    };

    /**
     * Pull a human-readable message out of a failed /pay response.
     *
     * The endpoint answers in three shapes: wp_send_json_error() with a plain
     * string (order not found, invalid or offline payment method), the same
     * with an object carrying `message`, and a WP_Error (bad nonce, rejected
     * permission callback) whose message sits at the top level.
     */
    var error_message = function (xhr) {
        var payload = (xhr && xhr.responseJSON) || null;

        if ( ! payload && xhr && xhr.responseText ) {
            try {
                payload = JSON.parse(xhr.responseText);
            } catch (e) {
                payload = null;
            }
        }

        if (payload) {
            if (typeof payload.data === "string" && payload.data) {
                return payload.data;
            }
            if (payload.data && payload.data.message) {
                return payload.data.message;
            }
            if (payload.message) {
                return payload.message;
            }
        }

        return "An error occurred. Please try again.";
    };

    // Override checkout.js's "payment" event handler — call /orders/{id}/pay instead of /orders
    $form.off("payment").on("payment", function (e) {
        e.preventDefault();

        clear_error();
        $(".easycommerce-checkout-main-btn").hide();
        $(".easycommerce-css-loader-wrapper").show();

        var data = $form.serializeArray();

        $.ajax({
            url: EASYCOMMERCE.rest_base + "/orders/" + order_id + "/pay",
            type: "POST",
            data: data,
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (response) {
                if ( response && response.success && response.data && response.data.redirect ) {
                    window.location = response.data.redirect;
                    return;
                }

                reset_form();

                // A gateway add-on can filter the redirect away to take the
                // payment over in its own UI — that is not a failure, so only
                // an explicit success:false gets an error message here.
                if ( response && response.success === false ) {
                    show_error(error_message({ responseJSON: response }));
                }
            },
            error: function (xhr) {
                reset_form();
                show_error(error_message(xhr));
            },
        });
    });
});
