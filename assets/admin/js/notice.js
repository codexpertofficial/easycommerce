 jQuery(document).ready(function($) {
    const { __ } = wp.i18n;

    $(document).on('click', '.easycommerce-pro-notice .notice-dismiss', function() {
        $.post(ajaxurl, {
            action: 'easycommerce_dismiss_pro_notice',
            nonce: EASYCOMMERCE_NOTICE.nonce,
        });
    });

    // Move WP core notices outside easycommerce_render div
    if ($('#easycommerce_render').length) {
        const container = document.getElementById("easycommerce_render");

        // Move any existing notices inside to before the wrap div
        const notices = container.querySelectorAll('.notice');
        notices.forEach(notice => {
            container.parentNode.parentNode.insertBefore(notice, container.parentNode);
        });
    }

    // Function to show toast messages
    function showToast(message, type = "success") {
        const toast = $('<div class="easycommerce-toast ' + type + '">' + message + '</div>');
        toast.css({
            position: 'fixed',
            top: '60px',
            right: '-400px',
            backgroundColor: type === 'success' ? '#4CAF50' : '#f44336',
            color: 'white',
            padding: '12px 24px',
            borderRadius: '6px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.25)',
            zIndex: 9999,
            fontSize: '14px',
            opacity: 0,
            minWidth: '220px'
        });

        $('body').append(toast);

        // Slide in from right
        toast.animate({
            right: '20px',
            opacity: 1
        }, 400);

        // Slide out to right & remove
        setTimeout(() => {
            toast.animate({
                right: '-400px',
                opacity: 0
            }, 400, function() {
                $(this).remove();
            });
        }, 3000);
    }

    $(document).on("click", "#wp-admin-bar-easycommerce-migration", function(e) {
        e.preventDefault();

        if (EASYCOMMERCE_NOTICE.migration_addon_active) { 
            window.open(EASYCOMMERCE_NOTICE.migration_addon_page, "_blank");
        } else {
            const $popup    = $(".migration-popup-wrapper");
            $popup.addClass('active');
        }
    });

    const btnCancel   = ".migration-popup-wrapper .button.cancel";
    const btnClose    = ".migration-popup-wrapper .close-button";

    $(document).on("click", btnCancel, function() {
        $(".migration-popup-wrapper").removeClass("active");
    });

    $(document).on("click", btnClose, function() {
        $(".migration-popup-wrapper").removeClass("active");
    });

    $(document).on("click", ".button.migration", function(e) {
        e.preventDefault();
        var $button = $(this);
        var originalText = $button.text();
        $button.text(__("Installing..", 'easycommerce')).prop('disabled', true);

        $.ajax({
            url: EASYCOMMERCE_NOTICE.migration_api_root + "easycommerce/v1/addons",
            method: "POST",
            data: {
                addon: "easycommerce-migration",
                action: "activate"
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE_NOTICE.rest_nonce);
            },
            success: function(response) {
                if (response.data.status === true) {
                    $(".migration-popup-wrapper").removeClass("active");
                    showToast(__("Migration addon activated successfully!", 'easycommerce'), "success");
                    setTimeout(() => {
                        window.location.href = EASYCOMMERCE_NOTICE.migration_addon_page;
                    }, 2000);
                } else {
                    $button.text(originalText).prop("disabled", false);
                    $(".migration-popup-wrapper").removeClass("active");
                    showToast(response.message || __("Failed to install migration addon.", 'easycommerce'), "error");
                }
            },
            error: function(xhr, status, error) {
                $button.text(originalText).prop("disabled", false);
                $(".migration-popup-wrapper").removeClass("active");
                showToast(__("Failed to install migration addon.", 'easycommerce'), "error");
            }
        });
    });
    // 
    $(document).on('click','.easycommerce-compatible-theme-notice .close-icon',function(){
        $('.easycommerce-compatible-theme-notice').remove();
    });
});