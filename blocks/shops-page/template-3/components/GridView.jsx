import React from 'react'
import StarRating from './Rating';
import { __ } from '@wordpress/i18n';

const GridView = ({products,
    attributes,
    columns,
    cartButtonClass,
    addedToCart,
    checkoutButtonClass,
    handleAddToCart,
    handleCheckout
}) => {
  const placeholderImage = `${EASYCOMMERCE.assets}/public/img/product/shop-product-placeholder.png`;
  return (
    <div
        className={`grid grid-cols-${columns} gap-[30px] mt-12`}
    >
        {products.map((product, index) => (
            <div
                key={index}
                    className="easycommerce-st-product-card border border-[#DBDBDB] rounded-md duration-300 ease-in-out"
            >
                <div className="w-full flex items-center justify-center rounded-md p-3">
                    <img
                        src={
                            product.thumbnail.url
                                ? product.thumbnail
                                        .url
                                : placeholderImage
                        }
                        alt={product.title}
                        className="border rounded-md easycommerce-thumbnail-img"
                    />
                </div>
                <div className="p-4">
                    {product.title && (
                        <div
                            className="easycommerce-product-title-shop font-inter"
                            style={{
                                color:
                                    attributes.titleColor ||
                                        'var(--color-ec-shop3)',
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
                    {product.rating && (
                        <StarRating
                            rating={parseFloat(product.rating)} 
                            attributes={attributes} 
                        />
                    )}
                    <div className="flex items-center gap-3 flex-wrap mt-3">
                        {product.formatted_sale_price > 0 ? (
                            <>
                            {/* Sale Price */}
                                <span
                                    className="font-inter mr-3"
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
                                    {product.sale_price && (
                                        <>
                                            <span
                                                style={{
                                                    fontSize: '12px'
                                                }}
                                                >
                                                {product.sale_price[0]}
                                            </span>
                                            <span
                                            >
                                                {product.sale_price.slice(1)}
                                            </span>
                                        </>
                                    )}
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
                                    {product.price && (
                                    <>
                                        <span
                                        style={{
                                            fontSize: '12px',
                                        }}
                                        >
                                        {product.price[0]}
                                        </span>
                                        <span
                                        >
                                        {product.price.slice(1)}
                                        </span>
                                    </>
                                    )}
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
                                {product.price && (
                                    <>
                                        <span
                                            style={{
                                            fontSize: '12px'
                                            }}
                                        >
                                            {product.price[0]}
                                        </span>
                                        <span
                                        >
                                            {product.price.slice(1)}
                                        </span>
                                    </>
                                )}
                            </span>
                        )}
                    </div>

                    <div className="mt-4">
                        <div className='easycommerce-product-price-wrap mt-3'>
                                <button
                                href={product.permalink}
                                onClick={() => handleAddToCart(product?.id || product?.ID)}
                                className={cartButtonClass}
                            >
                                {__('Add To Cart', 'easycommerce')}
                            </button>
                            {(addedToCart[product?.id || product?.ID]) && (
                                <button 
                                    onClick={() => handleCheckout(product?.id || product?.ID)}
                                    className={checkoutButtonClass}>
                                    {__('Checkout', 'easycommerce')}
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        ))}
    </div>
  )
}

export default GridView
