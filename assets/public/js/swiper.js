const galleryItemElement = document.querySelector(".easycommerce-gallery_item");
const galleryItemValue = galleryItemElement.getAttribute("galleryItem");

const galleryItem = parseInt(galleryItemValue);

var swiper = new Swiper(".easycommerce-single-product-gallery-items-slider", {
    loop: true,
    spaceBetween: 16,
    slidesPerView: galleryItem,
    freeMode: true,
    watchSlidesProgress: true,
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
});
var swiper2 = new Swiper(
    ".easycommerce-single-product-gallery-feature-slider",
    {
        loop: true,
        spaceBetween: 1,
        autoHeight: true,
        slidesPerView: 1,
        thumbs: {
            swiper: swiper,
        },
    }
);
