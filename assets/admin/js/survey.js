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

        // Defensive: the radios are `required`, so the browser blocks an empty
        // submit before this fires — but guard anyway against a programmatic submit.
        if (!reason) {
            return;
        }

        const feedback_url = `${EASYCOMMERCE_SURVEY.rest_base}/connectivity/feedback`;

        // Fire feedback in the background and deactivate immediately — the user
        // never waits on the (possibly slow) hub round-trip. sendBeacon can't set
        // headers, so the REST nonce goes in the body as `_wpnonce`, which WP
        // validates from $_REQUEST just like the X-WP-Nonce header.
        if (navigator.sendBeacon) {
            const payload = new FormData();
            payload.append("event", "deactivation");
            payload.append("name", EASYCOMMERCE_SURVEY.user?.name ?? "");
            payload.append("email", EASYCOMMERCE_SURVEY.user?.email ?? "");
            payload.append("home", EASYCOMMERCE_SURVEY.home);
            payload.append("subject", reason);
            payload.append("message", message);
            payload.append("deactivated", 1);
            payload.append("_wpnonce", EASYCOMMERCE_SURVEY.nonce);

            navigator.sendBeacon(feedback_url, payload);
            window.location.href = deactivation_url;
            return;
        }

        // Fallback (no sendBeacon): best-effort request, deactivate regardless of
        // the outcome so a slow/failing hub never strands the user on the popup.
        $('.loader').show();

        $.ajax({
            url: feedback_url,
            type: "POST",
            dataType: "JSON",
            data: {
                event: "deactivation",
                name: EASYCOMMERCE_SURVEY.user?.name,
                email: EASYCOMMERCE_SURVEY.user?.email,
                home: EASYCOMMERCE_SURVEY.home,
                subject: reason,
                message: message,
                deactivated: 1
            },
            headers: {
                "X-WP-Nonce": EASYCOMMERCE_SURVEY.nonce,
            },
            complete: () => {
                window.location.href = deactivation_url;
            },
        });
    });

    $(".easycommerce-survey-item-checkbox").change(function () {
        if ($('.easycommerce-survey-reason:checked').length) {
            $("#easycommerce-survey-comment").slideDown();
        } else {
            $("#easycommerce-survey-comment").slideUp();
        }
    });
    
    $(".easycommerce-survey-cross").on("click", function () {
        $("#easycommerce-survey-wrap").fadeOut(350);
    });
});
