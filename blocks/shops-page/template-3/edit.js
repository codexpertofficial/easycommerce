import { BlockControls, useBlockProps } from '@wordpress/block-editor';
import { useEffect, useState } from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl , TabPanel } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import Filters from './components/Filters';
import StarRating from './components/Rating';
import TypographyControls from '../../components/typography';
import Color from '../../components/color';
import ShortBy from './components/ShortBy';
import GridView from './components/GridView';
import ListView from './components/ListView';


const Edit = ({ attributes, setAttributes }) => {
    const [products, setProducts] = useState([]);
    const [totalProducts, setTotalProducts] = useState(0);
    const [isLoading, setIsLoading] = useState(true);
    const [viewType, setViewType] = useState('grid');
    const { columns } = attributes;
    const { ProductPerPage } = attributes;
    const { starSize } = attributes;
    const { showFilters } = attributes;
    const { showPagination } = attributes;
    const { showShortBy } = attributes;

    const [addedToCart, setAddedToCart] = useState({});
    const blockProps = useBlockProps();
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
                    setTotalProducts(data.data.total || 0);
                });
        };

        fetchProducts();
    }, [ProductPerPage]);

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
                {viewType === 'grid' && (
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

                        <ToggleControl
                            label="Show Sort By"
                            checked={!!showShortBy}
                            onChange={(value) => setAttributes({ showShortBy: value })}
                        />

                        <RangeControl
                            label="Number of Columns"
                            value={columns}
                            onChange={(value) => setAttributes({ columns: value })}
                            min={1}
                            max={4}
                        />
                    </PanelBody>
                )}
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
                {viewType === 'list' && (
                    <PanelBody
                        initialOpen={false}
                        title={__('Product Description', 'EasyCommerce')}
                    >
                        <Color
                            attributes={attributes}
                            attributeName="descriptionColor"
                            setAttributes={setAttributes}
                            title="Description Color"
                        />

                        <Color
                            attributes={attributes}
                            attributeName="descriptionHoverColor"
                            setAttributes={setAttributes}
                            title="Hover Description Color"
                        />
                        
                        <TypographyControls
                            attributes={attributes}
                            setAttributes={setAttributes}
                            attributeNames={[
                                'descriptionFontSize',
                                'descriptionFontWeight',
                                'descriptionTextTransform',
                                'descriptionTextStyle',
                                'descriptionDecoration',
                                'descriptionLineHeight',
                                'descriptionSpacing',
                            ]}
                        />
                    </PanelBody>
                )}

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
                <div className="flex items-start justify-between gap-8">
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
                        padding: 4px 14px;
                        border: 1px solid #272435;
                        border-radius: 48px;
                        box-shadow: 4px 4px 0px 0px #000000;
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
                        border-radius: 48px;
                        border: 1px solid var(--color-ec-primary);
                        box-shadow: none;
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
                        border-radius: 6px;
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
                                    ? "!w-[calc(100%_-_340px)] easycommerce-shop-container"
                                    : "w-full easycommerce-shop-container"
                            }
                        >
                        {showShortBy && <ShortBy viewType={viewType} setViewType={setViewType} perPage={ProductPerPage} totalProducts={totalProducts} showPagination={showPagination} />}

                        {!isLoading && (
                            products && products.length > 0 ? (
                                viewType === 'grid' ? (
                                    <GridView
                                        products={products} 
                                        attributes={attributes} 
                                        columns={columns} 
                                        cartButtonClass={cartButtonClass} 
                                        checkoutButtonClass={checkoutButtonClass} 
                                        addedToCart={addedToCart}
                                        handleAddToCart={handleAddToCart}
                                        handleCheckout={handleCheckout}
                                    />
                                ) : (
                                    <ListView
                                        products={products} 
                                        attributes={attributes} 
                                        columns={columns} 
                                        cartButtonClass={cartButtonClass} 
                                        checkoutButtonClass={checkoutButtonClass} 
                                        addedToCart={addedToCart}
                                        handleAddToCart={handleAddToCart}
                                        handleCheckout={handleCheckout}
                                    />
                                )
                            ) : (
                                <p>No products found.</p>
                            )
                        )}

                       
                    </div>
                </div>
            </div>
        </>
    );
};

export default Edit;
