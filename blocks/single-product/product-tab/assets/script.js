jQuery(function ($) {
    $(document).ready(function () {
        // Show the first tab and its content
        $(".tab-content .easycommerce-tab-content")
            .first()
            .addClass("active")
            .removeClass("hidden");
        $(".easycommerce-tabs .easycommerce-tab")
            .first()
            .addClass("active")
            .css("border-bottom", "1px solid var(--color-ec-primary)");

        // Tab click event
        $(".easycommerce-tab").on("click", function () {
            const tabId = $(this).data("tab");

            // Remove active class from all tabs and hide all content
            $(".easycommerce-tab")
                .removeClass("active text-ec-primary")
                .css("border-bottom", "none");
            $(".easycommerce-tab-content")
                .addClass("hidden")
                .removeClass("active");

            // Add active class to the clicked tab and corresponding content
            $(this)
                .addClass("active text-ec-primary")
                .css("border-bottom", "1px solid var(--color-ec-primary)");
            $("#" + tabId)
                .removeClass("hidden")
                .addClass("active");
        });

        /**
         * Submit Review Form
         */
        $(".easycommerce-review-form")
            .off("submit")
            .on("submit", function (event) {
                event.preventDefault();

                const productId = EASYCOMMERCE.product_id;
                const reviewTextRequired = EASYCOMMERCE.review_text_mandatory;
                const rating = parseInt(
                    $("#easycommerce-star-rating-value").val()
                );
                const reviewText = $(
                    ".easycommerce-single-product-review-text"
                ).val();

                if (rating === 0) {
                    const msg =
                        reviewTextRequired && !reviewText
                            ? EASYCOMMERCE?.review_msg_text.both
                            : EASYCOMMERCE?.review_msg_text.rating;

                    $(".easycommerce-rating-message").text(msg);
                    $(".easycommerce-rating-message")
                        .show()
                        .css("color", "#FF3A52");

                    return;
                }

                if (rating > 0 && reviewTextRequired && !reviewText) {
                    $(".easycommerce-rating-message").text(
                        EASYCOMMERCE?.review_msg_text.review
                    );
                    $(".easycommerce-rating-message")
                        .show()
                        .css("color", "#FF3A52");

                    return;
                }

                $.ajax({
                    url: `${EASYCOMMERCE.rest_base}/products/${productId}/reviews`,
                    type: "POST",
                    data: {
                        rating: rating,
                        text: reviewText,
                    },
                    dataType: "JSON",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
                    },
                    success: function (response) {
                        if (response.success === true) {
                            $(".star").removeClass("full");
                            $(".easycommerce-review-form").trigger("reset");

                            location.reload();
                        }
                    },
                    error: function (error) {
                        let response = JSON.parse(error.responseText);
                    },
                });
            });
        /**
         * Star Rating
         */
        $(".star").on("click", function () {
            var selectedValue = $(this).data("value");

            // Update the hidden input
            $("#easycommerce-star-rating-value").val(selectedValue);

            // Update the star classes
            $(".star").each(function () {
                var starValue = $(this).data("value");
                if (starValue <= selectedValue) {
                    $(this).addClass("full");
                } else {
                    $(this).removeClass("full");
                }
            });
        });
    });
});
