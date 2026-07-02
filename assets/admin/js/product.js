jQuery(function ($) {
    $(".easycommerce-product-section .panel-collapse").click(function(){
        $(this).toggleClass("collapsed");
        $(this).closest(".easycommerce-product-section").toggleClass("collapsed");
    })
});



