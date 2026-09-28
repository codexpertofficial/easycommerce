jQuery(document).ready(function ($) {
	$(document).on("click", ".show-more", function() {
        $(this).closest(".ec-summary-text").find(".short-text").hide();
        $(this).closest(".ec-summary-text").find(".full-text").show();
    });

    $(document).on("click", ".show-less", function() {
        $(this).closest(".ec-summary-text").find(".full-text").hide();
        $(this).closest(".ec-summary-text").find(".short-text").show();
    });

    $(document).on("submit", ".easycommerce-restock-request", function (event) {
        event.preventDefault();

        const { __ } = wp.i18n;
        const $form = $(this);
        const $button = $form.find(".easycommerce-restock-request-button");
        const $message = $form.find(".easycommerce-restock-request-message");

        const showMessage = (text, isError) => {
            $message
                .text(text)
                .css("color", isError ? "#dc2626" : "#16a34a")
                .show();
        };

        $button.prop("disabled", true);

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/restock-requests`,
            method: "POST",
            headers: { "X-WP-Nonce": EASYCOMMERCE.nonce },
            data: {
                product_id: $form.data("product-id"),
                variation_id: $form.attr("data-variation-id") || 0,
                email: $form.find(".easycommerce-restock-request-email").val(),
            },
        })
            .done(function (response) {
                showMessage(response?.data?.message || __("Thanks! We will email you when it is back.", "easycommerce"), false);
                $button.prop("disabled", false);
            })
            .fail(function (response) {
                $button.prop("disabled", false);
                showMessage(
                    response?.responseJSON?.data?.message || __("Unable to save your request. Please try again.", "easycommerce"),
                    true
                );
            });
    });
});
