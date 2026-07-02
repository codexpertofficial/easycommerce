jQuery(function ($) {
    "use strict";

    var $form    = $("#easycommerce-checkout");
    var order_id = $form.data("order-id");

    if ( ! order_id ) return;

    // Override checkout.js's "payment" event handler — call /orders/{id}/pay instead of /orders
    $form.off("payment").on("payment", function (e) {
        e.preventDefault();

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
                if ( typeof render_message === "function" ) {
                    render_message(response);
                }
                if ( response.success && response.data && response.data.redirect ) {
                    window.location = response.data.redirect;
                } else {
                    $(".easycommerce-checkout-main-btn").show();
                    $(".easycommerce-css-loader-wrapper").hide();
                }
            },
            error: function (error) {
                if ( typeof render_message === "function" ) {
                    render_message(error);
                }
                $(".easycommerce-checkout-main-btn").show();
                $(".easycommerce-css-loader-wrapper").hide();
            },
        });
    });
});
