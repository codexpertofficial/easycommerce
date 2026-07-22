import { __ } from "@wordpress/i18n";
import { useSelect } from "@wordpress/data";
import { useBlockProps } from "@wordpress/block-editor";
import { useEffect, useRef, useState } from "@wordpress/element";

import { Swiper, SwiperSlide } from "swiper/react";
import { FreeMode, Navigation, Thumbs } from "swiper/modules";

import Inspector from "./inspector";

// Import Swiper styles
import "swiper/css";
import "swiper/css/free-mode";
import "swiper/css/navigation";
import "swiper/css/thumbs";

const Edit = (props) => {
    const blockProps = useBlockProps();
    const [productGallery, setProductGallery] = useState([]);
    const [thumbsSwiper, setThumbsSwiper] = useState(null);

    // get Product Data
    const postType = wp.data.select("core/editor").getCurrentPostType();
    const postId = wp.data.select("core/editor").getCurrentPostId();

    //get attributes
    const { attributes, setAttributes } = props;
    const { GalleryItem } = attributes;

    useEffect(() => {
        const fetchProducts = async () => {
            try {
                const response = await fetch(
                    EASYCOMMERCE.rest_base + "/products/" + postId
                );
                const product = await response.json();

                setProductGallery(product.data.gallery);
            } catch (error) {
            }
        };
        fetchProducts();
    }, []);

    return postType === "product" ? (
        productGallery.length > 0 ? (
            <div {...blockProps}>
                <Inspector {...props} />
                {/* Gallery Preview */}
                <Swiper
                    style={{
                        "--swiper-navigation-color": "#fff",
                        "--swiper-pagination-color": "#fff",
                        marginBottom: "10px",
                        width: "100%",
                        height: "100%",
                    }}
                    loop={true}
                    autoHeight={true}
                    spaceBetween={10}
                    thumbs={{ swiper: thumbsSwiper }}
                    modules={[FreeMode, Navigation, Thumbs]}
                    className="mySwiper2"
                >
                    {productGallery.map((image) => (
                        <SwiperSlide key={image.id}>
                            <img src={image.url} />
                        </SwiperSlide>
                    ))}
                </Swiper>

                {/* Gallery Items */}
                <Swiper
                    onSwiper={setThumbsSwiper}
                    loop={true}
                    spaceBetween={10}
                    slidesPerView={GalleryItem}
                    freeMode={true}
                    navigation={true}
                    watchSlidesProgress={true}
                    modules={[FreeMode, Navigation, Thumbs]}
                    className="mySwiper easycommerce-product-list-swiper-slide"
                >
                    {productGallery.map((image) => (
                        <SwiperSlide key={image.id}>
                            <img src={image.thumbnail} />
                        </SwiperSlide>
                    ))}
                </Swiper>
            </div>
        ) : (
            <div>
                <img
                    className="w-full h-auto"
                    src={
                        EASYCOMMERCE.assets +
                        "/public/img/product/shop-product-placeholder.png"
                    }
                    alt=""
                />
            </div>
        )
    ) : (
        <div {...blockProps}>
            <p>{__("Post type is not product.", "easycommerce")}</p>
        </div>
    );
};

export default Edit;
