jQuery(function ($) {
    $('.wp-list-table tr[data-slug="easycommerce"] .deactivate a').click( function (e) {
        e.preventDefault();
        $("#easycommerce-survey-wrap").fadeIn(350);
    } );

    $("#easycommerce-survey-form").submit(function (e) {
        e.preventDefault();

        let deactivation_url = $(this).attr("action");
        let reason = $('.easycommerce-survey-reason[name="reason"]:checked').val();
        let message = $('#easycommerce-survey-message textarea[name="message"]').val();

        $('.loader').show();

        $.ajax({
            url: `${EASYCOMMERCE_SURVEY.rest_base}/connectivity/feedback`,
            type: "POST",
            dataType: "JSON",
            data: {
                name: EASYCOMMERCE_SURVEY.user.data.display_name,
                email: EASYCOMMERCE_SURVEY.user.data.user_email,
                home: EASYCOMMERCE_SURVEY.home,
                subject: reason,
                message: message,
                deactivated: 1
            },
            headers: {
                "X-WP-Nonce": EASYCOMMERCE_SURVEY.nonce,
            },
            success: (success) => {
                $('.loader').hide();
                window.location.href = deactivation_url;
            },
            error: (error) => {
                $('.loader').hide();
            },
        });
    });

    $(".easycommerce-survey-item-checkbox").change(function () {
        if ($(this).prop("checked")) {
            $("#easycommerce-survey-comment").slideDown();
        } else {
            $("#easycommerce-survey-comment").slideUp();
        }
    });
    
    $(".easycommerce-survey-cross").on("click", function () {
        $("#easycommerce-survey-wrap").fadeOut(350);
    });
});
