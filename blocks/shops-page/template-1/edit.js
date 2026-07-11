import { BlockControls, useBlockProps } from '@wordpress/block-editor';
import { useEffect, useState } from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl , TabPanel } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import Filters from './components/Filters';
import StarRating from './components/Rating';
import TypographyControls from '../../components/typography';
import Color from '../../components/color';


const Edit = ({ attributes, setAttributes }) => {
    const [products, setProducts] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const { columns } = attributes;
    const { ProductPerPage } = attributes;
    const { starSize } = attributes;
    const { showFilters } = attributes;
    const { showPagination } = attributes;

    const [addedToCart, setAddedToCart] = useState({});


    const blockProps = useBlockProps({ className: 'easycommerce' });

    const cartButtonClass = `cart-button-${blockProps.id}`;

    const checkoutButtonClass = `checkout-button-${blockProps.id}`;

    const handleAddToCart = (productId) => {
        if (!productId) return;

        setAddedToCart((prev) => ({
            ...prev,
            [productId]: true,
        }));
    };

    const handleCheckout = (productId) => {
        if (typeof window !== "undefined") {
            window.location.href = "/checkout";
        }
    };

    // get Product Data
    useEffect(() => {
        const fetchProducts = async () => {
            setIsLoading(true);

            fetch(
                `${EASYCOMMERCE.rest_base}/products?per_page=${ProductPerPage}`
            )
                .then((res) => res.json())
                .then((data) => {
                    setIsLoading(false);
                    setProducts(data.data.products);
                });
        };

        fetchProducts();
    }, [ProductPerPage]);

    const placeholderImage = `${EASYCOMMERCE.assets}/public/img/product/shop-product-placeholder.png`;

    return (
        <>
            <InspectorControls>
                <PanelBody title="Product Settings">
                    <RangeControl
                        label="Products Per Page"
                        value={ProductPerPage}
                        onChange={(value) =>
                            setAttributes({ ProductPerPage: value })
                        }
                        min={1}
                        max={20}
                    />
                </PanelBody>
                <PanelBody title="Grid Settings">
                     <ToggleControl
                         label="Show Filters"
                         checked={!!showFilters}
                         onChange={(value) => setAttributes({ showFilters: value })}
                     />

                     <ToggleControl
                         label="Show Pagination"
                         checked={!!showPagination}
                         onChange={(value) => setAttributes({ showPagination: value })}
                     />

                     <RangeControl
                        label="Number of Columns"
                        value={columns}
                        onChange={(value) => setAttributes({ columns: value })}
                        min={1}
                        max={4}
                    />
                </PanelBody>
                <PanelBody
                    initialOpen={false}
                    title={__('Product Title', 'codesigner')}
                >
                    <Color
                        attributes={attributes}
                        attributeName="titleColor"
                        setAttributes={setAttributes}
                        title="Text Color"
                    />

                    <Color
                        attributes={attributes}
                        attributeName="titleHoverColor"
                        setAttributes={setAttributes}
                        title="Hover Text Color"
                    />

                    <TypographyControls
                        attributes={attributes}
                        setAttributes={setAttributes}
                        attributeNames={[
                            'titleFontSize',
                            'titleFontWeight',
                            'titleTextTransform',
                            'titleTextStyle',
                            'titleDecoration',
                            'titleLineHeight',
                            'titleSpacing',
                        ]}
                    />
                </PanelBody>
                <PanelBody
                    initialOpen={false}
                    title={__('Product Category', 'codesigner')}
                >
                    <Color
                        attributes={attributes}
                        attributeName="categoryColor"
                        setAttributes={setAttributes}
                        title="Color"
                    />
                    <TypographyControls
                        attributes={attributes}
                        setAttributes={setAttributes}
                        attributeNames={[
                            'categoryFontSize',
                            'categoryFontWeight',
                            'categoryTextTransform',
                            'categoryStyle',
                            'categoryDecoration',
                            'categoryLineHeight',
                            'categorySpacing',
                        ]}
                    />
                </PanelBody>
                <PanelBody
                    initialOpen={false}
                    title={__('Product Rating', 'codesigner')}
                >
                    <RangeControl
                        label="Star Size"
                        value={starSize}
                        onChange={(value) => setAttributes({ starSize: value })}
                        min={1}
                        max={100}
                    />
                    <Color
                        attributes={attributes}
                        attributeName="ratingColor"
                        setAttributes={setAttributes}
                        title="Color"
                    />
                    <TypographyControls
                        attributes={attributes}
                        setAttributes={setAttributes}
                        attributeNames={[
                            'ratingFontSize',
                            'ratingFontWeight',
                            'ratingTextTransform',
                            'ratingStyle',
                            'ratingDecoration',
                            'ratingLineHeight',
                            'ratingSpacing',
                        ]}
                    />
                </PanelBody>
                <PanelBody
                    initialOpen={false}
                    title={__('Product Price', 'codesigner')}
                >
                    <TabPanel
                        className="price-tabs"
                        activeClass="active-tab"
                        tabs={[
                            {
                                name: 'regular',
                                title: __('Regular Price', 'codesigner'),
                                className: 'regular-price-tab',
                            },
                            {
                                name: 'sale',
                                title: __('Sale Price', 'codesigner'),
                                className: 'sale-price-tab',
                            },
                        ]}
                    >
                        {(tab) => {
                            if (tab.name === 'regular') {
                                return (
                                    <>
                                        <Color
                                            attributes={attributes}
                                            attributeName="priceColor"
                                            setAttributes={setAttributes}
                                            title="Color"
                                        />
                                        <TypographyControls
                                            attributes={attributes}
                                            setAttributes={setAttributes}
                                            attributeNames={[
                                                'priceFontSize',
                                                'priceFontWeight',
                                                'priceTextTransform',
                                                'priceStyle',
                                                'priceDecoration',
                                                'priceLineHeight',
                                                'priceSpacing',
                                            ]}
                                        />
                                    </>
                                );
                            } else if (tab.name === 'sale') {
                                return (
                                    <>
                                        <Color
                                            attributes={attributes}
                                            attributeName="salePriceColor"
                                            setAttributes={setAttributes}
                                            title="Color"
                                        />
                                        <TypographyControls
                                            attributes={attributes}
                                            setAttributes={setAttributes}
                                            attributeNames={[
                                                'salePriceFontSize',
                                                'salePriceFontWeight',
                                                'salePriceTextTransform',
                                                'salePriceStyle',
                                                'salePriceDecoration',
                                                'salePriceLineHeight',
                                                'salePriceSpacing',
                                            ]}
                                        />
                                    </>
                                );
                            }
                        }}
                    </TabPanel>
                </PanelBody>

                <PanelBody
                    title={__('Cart Button', 'codesigner')}
                    initialOpen={true}
                >
                     <TabPanel
                        className="cart-button-tabs"
                        activeClass="active-tab"
                        tabs={[
                            {
                                name: 'normal',
                                title: __('Normal', 'codesigner'),
                                className: 'cart-button-normal-tab',
                            },
                            {
                                name: 'hover',
                                title: __('Hover', 'codesigner'),
                                className: 'cart-button-hover-tab',
                            },
                        ]}
                    >
                        {(tab) => {
                            if (tab.name === 'normal') {
                                return (
                                    <>
                                        <Color
                                            attributes={attributes}
                                            attributeName="cartButtonColor"
                                            setAttributes={setAttributes}
                                            title="Text Color"
                                        />
                                        <Color
                                            attributes={attributes}
                                            attributeName="cartButtonBgColor"
                                            setAttributes={setAttributes}
                                            title="Background Color"
                                        />
                                        <Color
                                            attributes={attributes}
                                            attributeName="cartButtonFocusColor"
                                            setAttributes={setAttributes}
                                            title="Focus Text Color"
                                        />

                                        <Color
                                            attributes={attributes}
                                            attributeName="cartButtonFocusBgColor"
                                            setAttributes={setAttributes}
                                            title="Focus Background Color"
                                        />
                                        <TypographyControls
                                            attributes={attributes}
                                            setAttributes={setAttributes}
                                            attributeNames={[
                                                'cartButtonFontSize',
                                                'cartButtonFontWeight',
                                                'cartButtonTextTransform',
                                                'cartButtonStyle',
                                                'cartButtonDecoration',
                                                'cartButtonLineHeight',
                                                'cartButtonSpacing',
                                            ]}
                                        />
                                    </>
                                );
                            } else if (tab.name === 'hover') {
                                return (
                                    <>
                                        <Color
                                            attributes={attributes}
                                            attributeName="cartButtonHoverColor"
                                            setAttributes={setAttributes}
                                            title="Text Color (Hover)"
                                        />
                                        <Color
                                            attributes={attributes}
                                            attributeName="cartButtonHoverBgColor"
                                            setAttributes={setAttributes}
                                            title="Background Color (Hover)"
                                        />
                                        <TypographyControls
                                            attributes={attributes}
                                            setAttributes={setAttributes}
                                            attributeNames={[
                                                'cartButtonHoverFontSize',
                                                'cartButtonHoverFontWeight',
                                                'cartButtonHoverTextTransform',
                                                'cartButtonHoverStyle',
                                                'cartButtonHoverDecoration',
                                                'cartButtonHoverLineHeight',
                                                'cartButtonHoverSpacing',
                                            ]}
                                        />
                                    </>
                                );
                            }
                        }}
                    </TabPanel>
                </PanelBody>
                <PanelBody
                    title={__('Checkout Button', 'codesigner')}
                    initialOpen={true}
                >
                     <TabPanel
                        className="checkout-button-tabs"
                        activeClass="active-tab"
                        tabs={[
                            {
                                name: 'normal',
                                title: __('Normal', 'codesigner'),
                                className: 'checkout-button-normal-tab',
                            },
                            {
                                name: 'hover',
                                title: __('Hover', 'codesigner'),
                                className: 'checkout-button-hover-tab',
                            },
                        ]}
                    >
                        {(tab) => {
                            if (tab.name === 'normal') {
                                return (
                                    <>
                                        <Color
                                            attributes={attributes}
                                            attributeName="checkoutButtonColor"
                                            setAttributes={setAttributes}
                                            title="Text Color"
                                        />
                                        <Color
                                            attributes={attributes}
                                            attributeName="checkoutButtonBgColor"
                                            setAttributes={setAttributes}
                                            title="Background Color"
                                        />
                                        <TypographyControls
                                            attributes={attributes}
                                            setAttributes={setAttributes}
                                            attributeNames={[
                                                'checkoutButtonFontSize',
                                                'checkoutButtonFontWeight',
                                                'checkoutButtonTextTransform',
                                                'checkoutButtonStyle',
                                                'checkoutButtonDecoration',
                                                'checkoutButtonLineHeight',
                                                'checkoutButtonSpacing',
                                            ]}
                                        />
                                    </>
                                );
                            } else if (tab.name === 'hover') {
                                return (
                                    <>
                                        <Color
                                            attributes={attributes}
                                            attributeName="checkoutButtonHoverColor"
                                            setAttributes={setAttributes}
                                            title="Text Color (Hover)"
                                        />
                                        <Color
                                            attributes={attributes}
                                            attributeName="checkoutButtonHoverBgColor"
                                            setAttributes={setAttributes}
                                            title="Background Color (Hover)"
                                        />
                                        <TypographyControls
                                            attributes={attributes}
                                            setAttributes={setAttributes}
                                            attributeNames={[
                                                'checkoutButtonHoverFontSize',
                                                'checkoutButtonHoverFontWeight',
                                                'checkoutButtonHoverTextTransform',
                                                'checkoutButtonHoverStyle',
                                                'checkoutButtonHoverDecoration',
                                                'checkoutButtonHoverLineHeight',
                                                'checkoutButtonHoverSpacing',
                                            ]}
                                        />
                                    </>
                                );
                            }
                        }}
                    </TabPanel>
                </PanelBody>
            </InspectorControls>
            {/* Block content start from here  */}
            <div {...blockProps}>
                <div className="">
                 <style>
                    {`
                    .${cartButtonClass} {
                        color: ${attributes.cartButtonColor};
                        background-color: ${attributes.cartButtonBgColor};
                        font-size: ${attributes.cartButtonFontSize}px;
                        font-weight: ${attributes.cartButtonFontWeight};
                        text-transform: ${attributes.cartButtonTextTransform};
                        font-style: ${attributes.cartButtonStyle};
                        text-decoration: ${attributes.cartButtonDecoration};
                        line-height: ${attributes.cartButtonLineHeight}px;
                        letter-spacing: ${attributes.cartButtonSpacing}px;
                        transition: all 0.3s ease;
                        display: inline-flex;
                        gap: 8px;
                        align-items: center;
                        padding: 8px 16px;
                        border-radius: 100px;
                        border: 1px solid #ebebeb;
                    }
                    .${cartButtonClass}:hover {
                        color: ${attributes.cartButtonHoverColor};
                        background-color: ${attributes.cartButtonHoverBgColor};
                        font-size: ${attributes.cartButtonHoverFontSize}px;
                        font-weight: ${attributes.cartButtonHoverFontWeight};
                        text-transform: ${attributes.cartButtonHoverTextTransform};
                        font-style: ${attributes.cartButtonHoverStyle};
                        text-decoration: ${attributes.cartButtonHoverDecoration};
                        line-height: ${attributes.cartButtonHoverLineHeight}px;
                        letter-spacing: ${attributes.cartButtonHoverSpacing}px;
                    }
                    .${cartButtonClass}:focus {
                        color: ${attributes.cartButtonFocusColor};
                        background-color: ${attributes.cartButtonFocusBgColor};
                    }
                    `}
                    {`
                    .${checkoutButtonClass} {
                        color: ${attributes.checkoutButtonColor};
                        background-color: ${attributes.checkoutButtonBgColor};
                        font-size: ${attributes.checkoutButtonFontSize}px;
                        font-weight: ${attributes.checkoutButtonFontWeight}; 
                        text-transform: ${attributes.checkoutButtonTextTransform};
                        font-style: ${attributes.checkoutButtonStyle};
                        text-decoration: ${attributes.checkoutButtonDecoration};
                        line-height: ${attributes.checkoutButtonLineHeight}px;
                        letter-spacing: ${attributes.checkoutButtonSpacing}px;
                        transition: all 0.3s ease;
                        display: inline-flex;
                        gap: 8px;
                        align-items: center;
                        padding: 8px 16px;
                        border-radius: 100px;
                        border: 1px solid #ebebeb;
                    }
                    .${checkoutButtonClass}:hover {
                        color: ${attributes.checkoutButtonHoverColor};
                        background-color: ${attributes.checkoutButtonHoverBgColor};
                        font-size: ${attributes.checkoutButtonHoverFontSize}px;
                        font-weight: ${attributes.checkoutButtonHoverFontWeight};
                        text-transform: ${attributes.checkoutButtonHoverTextTransform};             
                        font-style: ${attributes.checkoutButtonHoverStyle};
                        text-decoration: ${attributes.checkoutButtonHoverDecoration};
                        line-height: ${attributes.checkoutButtonHoverLineHeight}px;
                        letter-spacing: ${attributes.checkoutButtonHoverSpacing}px;
                    }
                    `}
                </style>

                    {/* <Filters /> */}
                    {showFilters && <Filters />}
                        <div
                            className={
                                showFilters
                                    ? "w-full easycommerce-shop-container"
                                    : "w-full easycommerce-shop-container"
                            }
                        >

                        {!isLoading &&
                            (products && products.length > 0 ? (
                                <div
                                    className={`grid grid-cols-${columns} gap-[30px]`}
                                >
                                    {products.map((product, index) => (

                                        <div
                                            key={index}
                                            className=""
                                        >
                                            <div className="w-full flex items-center justify-center border border-ec-border rounded-t-xl">
                                                <img
                                                    src={
                                                        product.thumbnail.url
                                                            ? product.thumbnail
                                                                  .url
                                                            : placeholderImage
                                                    }
                                                    alt={product.title}
                                                    className="border rounded-t-xl easycommerce-thumbnail-img"
                                                />
                                            </div>
                                            <div className="pt-4">

                                                {product.title && (
                                                    <div
                                                        className="mb-[2px]"
                                                        style={{
                                                            color:
                                                                attributes.titleColor ||
                                                                 'var(--color-ec-secondary)',
                                                            fontSize:
                                                                attributes.titleFontSize
                                                                    ? `${attributes.titleFontSize}px`
                                                                    : '16px',
                                                            fontWeight:
                                                                attributes.titleFontWeight ||
                                                                '600',
                                                            textTransform:
                                                                attributes.titleTextTransform ||
                                                                'none',
                                                            fontStyle:
                                                                attributes.titleTextStyle ||
                                                                'normal',
                                                            textDecoration:
                                                                attributes.titleDecoration ||
                                                                'none',
                                                            lineHeight:
                                                                attributes.titleLineHeight
                                                                    ? `${attributes.titleLineHeight}px`
                                                                    : '26px',
                                                            letterSpacing:
                                                                attributes.titleSpacing
                                                                    ? `${attributes.titleSpacing}px`
                                                                    : '0px',
                                                        }}
                                                    >
                                                        {product.title}
                                                    </div>
                                                )}
                                                <style>
                                                    {attributes.titleHoverColor &&
                                                        `.easycommerce-product-title-shop:hover {
                                                            color: ${attributes.titleHoverColor} !important;
                                                        }`}
                                                </style>
                                              {product.categories &&
                                                product.categories.length >
                                                    0 && (
                                                    <div
                                                        className="my-4"
                                                        style={{
                                                            color:
                                                                attributes.categoryColor ||
                                                                'var(--color-ec-secondary)',
                                                            fontSize:
                                                                attributes.categoryFontSize
                                                                    ? `${attributes.categoryFontSize}px`
                                                                    : '12px',
                                                            fontWeight:
                                                                attributes.categoryFontWeight ||
                                                                '500',
                                                            textTransform:
                                                                attributes.categoryTextTransform ||
                                                                'none',
                                                            fontStyle:
                                                                attributes.categoryStyle ||
                                                                'normal',
                                                            textDecoration:
                                                                attributes.categoryDecoration ||
                                                                'none',
                                                            lineHeight:
                                                                attributes.categoryLineHeight
                                                                    ? `${attributes.categoryLineHeight}px`
                                                                    : '20px',
                                                            letterSpacing:
                                                                attributes.categorySpacing
                                                                    ? `${attributes.categorySpacing}px`
                                                                    : '0px',
                                                        }}
                                                    >
                                                        {
                                                            product
                                                                .categories[0]
                                                                .name
                                                        }
                                                    </div>
                                                )}
                                                <div className="mt-4">
                                                    {product.rating ? (
                                                        <div className="flex justify-between items-center">
                                                            <div className="w-[50%]">
                                                                 <StarRating
                                                                    rating={parseFloat(product.rating)}
                                                                    attributes={attributes}
                                                                />
                                                            </div>


                                                            <div className="w-[50%] easycommerce-product-price-wrap flex justify-end items-center gap-3">
                                                                {product.formatted_sale_price > 0 ? (
                                                                    <>
                                                                        <span
                                                                            className="font-inter"
                                                                            style={{
                                                                                color: attributes.priceColor || "var(--color-ec-body)",
                                                                                fontSize: attributes.priceFontSize ? `${attributes.priceFontSize}px` : "16px",
                                                                                fontWeight: attributes.priceFontWeight || "500",
                                                                                textTransform: attributes.priceTextTransform || "none",
                                                                                fontStyle: attributes.priceStyle || "normal",
                                                                                textDecoration: attributes.priceDecoration || "none",
                                                                                lineHeight: attributes.priceLineHeight ? `${attributes.priceLineHeight}px` : "20px",
                                                                                letterSpacing: attributes.priceSpacing ? `${attributes.priceSpacing}px` : "0px",
                                                                            }}
                                                                        >
                                                                            {product.sale_price}
                                                                        </span>
                                                                        <del
                                                                            className="font-inter"
                                                                            style={{
                                                                                color: attributes.salePriceColor || "var(--color-ec-body)",
                                                                                fontSize: attributes.salePriceFontSize ? `${attributes.salePriceFontSize}px` : "16px",
                                                                                fontWeight: attributes.salePriceFontWeight || "500",
                                                                                textTransform: attributes.salePriceTextTransform || "none",
                                                                                fontStyle: attributes.salePriceStyle || "normal",
                                                                                textDecoration: attributes.salePriceDecoration || "line-through",
                                                                                lineHeight: attributes.salePriceLineHeight ? `${attributes.salePriceLineHeight}px` : "20px",
                                                                                letterSpacing: attributes.salePriceSpacing ? `${attributes.salePriceSpacing}px` : "0px",
                                                                            }}
                                                                        >
                                                                            {product.price}
                                                                        </del>
                                                                    </>
                                                                ) : (
                                                                    <span
                                                                        className="font-inter"
                                                                        style={{
                                                                            color: attributes.priceColor || "var(--color-ec-body)",
                                                                            fontSize: attributes.priceFontSize ? `${attributes.priceFontSize}px` : "16px",
                                                                            fontWeight: attributes.priceFontWeight || "500",
                                                                            textTransform: attributes.priceTextTransform || "none",
                                                                            fontStyle: attributes.priceStyle || "normal",
                                                                            textDecoration: attributes.priceDecoration || "none",
                                                                            lineHeight: attributes.priceLineHeight ? `${attributes.priceLineHeight}px` : "20px",
                                                                            letterSpacing: attributes.priceSpacing ? `${attributes.priceSpacing}px` : "0px",
                                                                        }}
                                                                    >
                                                                        {product.price}
                                                                    </span>
                                                                )}
                                                            </div>
                                                        </div>
                                                    ) : (
                                                        <div className="easycommerce-product-price-wrap mt-3">
                                                            {product.formatted_sale_price > 0 ? (
                                                                <>
                                                                    <span
                                                                        className="font-inter"
                                                                        style={{
                                                                            color: attributes.priceColor || "var(--color-ec-body)",
                                                                            fontSize: attributes.priceFontSize ? `${attributes.priceFontSize}px` : "16px",
                                                                            fontWeight: attributes.priceFontWeight || "500",
                                                                            textTransform: attributes.priceTextTransform || "none",
                                                                            fontStyle: attributes.priceStyle || "normal",
                                                                            textDecoration: attributes.priceDecoration || "none",
                                                                            lineHeight: attributes.priceLineHeight ? `${attributes.priceLineHeight}px` : "20px",
                                                                            letterSpacing: attributes.priceSpacing ? `${attributes.priceSpacing}px` : "0px",
                                                                        }}
                                                                    >
                                                                        {product.sale_price}
                                                                    </span>
                                                                    <del
                                                                        className="font-inter"
                                                                        style={{
                                                                            color: attributes.salePriceColor || "var(--color-ec-body)",
                                                                            fontSize: attributes.salePriceFontSize ? `${attributes.salePriceFontSize}px` : "16px",
                                                                            fontWeight: attributes.salePriceFontWeight || "500",
                                                                            textTransform: attributes.salePriceTextTransform || "none",
                                                                            fontStyle: attributes.salePriceStyle || "normal",
                                                                            textDecoration: attributes.salePriceDecoration || "line-through",
                                                                            lineHeight: attributes.salePriceLineHeight ? `${attributes.salePriceLineHeight}px` : "20px",
                                                                            letterSpacing: attributes.salePriceSpacing ? `${attributes.salePriceSpacing}px` : "0px",
                                                                        }}
                                                                    >
                                                                        {product.price}
                                                                    </del>
                                                                </>
                                                            ) : (
                                                                <span
                                                                    className="font-inter"
                                                                    style={{
                                                                        color: attributes.priceColor || "var(--color-ec-body)",
                                                                        fontSize: attributes.priceFontSize ? `${attributes.priceFontSize}px` : "16px",
                                                                        fontWeight: attributes.priceFontWeight || "500",
                                                                        textTransform: attributes.priceTextTransform || "none",
                                                                        fontStyle: attributes.priceStyle || "normal",
                                                                        textDecoration: attributes.priceDecoration || "none",
                                                                        lineHeight: attributes.priceLineHeight ? `${attributes.priceLineHeight}px` : "20px",
                                                                        letterSpacing: attributes.priceSpacing ? `${attributes.priceSpacing}px` : "0px",
                                                                    }}
                                                                >
                                                                    {product.price}
                                                                </span>
                                                            )}
                                                        </div>
                                                    )}
                                                </div>

                                                <div className="flex items-center mt-4">
                                                    <div className='easycommerce-product-price-wrap mt-3 flex gap-10'>
                                                        <button
                                                            href={product.permalink}
                                                            onClick={() => handleAddToCart(product?.id || product?.ID)}
                                                            className={cartButtonClass}
                                                        >
                                                            Add To Cart
                                                        </button>
                                                        {(addedToCart[product?.id || product?.ID]) && (
                                                            <button
                                                                onClick={() => handleCheckout(product?.id || product?.ID)}
                                                                className={checkoutButtonClass}>
                                                                Checkout
                                                            </button>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p>No products found.</p>
                            ))}
                    </div>
                </div>
            </div>
        </>
    );
};

export default Edit;
