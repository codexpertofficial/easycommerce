jQuery(document).ready(function ($) {
	$(document).on("click", ".show-more", function() {
        $(this).closest(".ec-summary-text").find(".short-text").hide();
        $(this).closest(".ec-summary-text").find(".full-text").show();
    });

    $(document).on("click", ".show-less", function() {
        $(this).closest(".ec-summary-text").find(".full-text").hide();
        $(this).closest(".ec-summary-text").find(".short-text").show();
    });
});
