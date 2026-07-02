jQuery(document).ready(function($) {
    // Add the custom tab after the "Popular" tab
    var customTab = '<li><a href="#" data-sort="easycommerce-recommended">EasyCommerce Recommended</a></li>';
    $('.filter-links li:first').after(customTab);

     // Auto-click the tab if URL contains browse=easycommerce-recommended
    var urlParams = new URLSearchParams(window.location.search);
    var browseParam = urlParams.get('browse');
    
    if (browseParam === 'easycommerce-recommended') {
        // Wait a bit for the page to fully load
        setTimeout(function() {
            $('.filter-links a[data-sort="easycommerce-recommended"]').trigger('click');
        }, 500);
    }
});