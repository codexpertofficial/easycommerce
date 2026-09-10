import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl, Placeholder, Disabled } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const ServerSideRender = wp.serverSideRender;

const SOURCES = [
    { label: __( 'Featured', 'easycommerce' ), value: 'featured' },
    { label: __( 'Best sellers', 'easycommerce' ), value: 'best-selling' },
    { label: __( 'New arrivals', 'easycommerce' ), value: 'newest' },
    { label: __( 'All products', 'easycommerce' ), value: 'all' },
];

const Empty = () => (
    <Placeholder
        label={ __( 'Product Collection', 'easycommerce' ) }
        instructions={ __( 'No products yet. Once you add products they will appear here.', 'easycommerce' ) }
    />
);

const Edit = ({ attributes, setAttributes }) => {
    const { source, category, count, columns } = attributes;

    const [categories, setCategories] = useState([]);

    // The storefront CSS is scoped to body.easycommerce, a class body_class only adds on the front end.
    const blockProps = useBlockProps({ className: 'easycommerce ec-product-collection' });

    // Load categories once for the selector.
    useEffect(() => {
        fetch(`${EASYCOMMERCE.rest_base}/products/categories`)
            .then((res) => res.json())
            .then((data) => setCategories(data?.data?.categories || []))
            .catch(() => setCategories([]));
    }, []);

    const categoryOptions = [
        { label: __( 'All categories', 'easycommerce' ), value: '' },
        ...categories.map((cat) => ({
            label: cat.name,
            value: String(cat.slug || cat.id),
        })),
    ];

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={ __( 'Collection settings', 'easycommerce' ) }>
                    <SelectControl
                        label={ __( 'Source', 'easycommerce' ) }
                        value={ source }
                        options={ SOURCES }
                        onChange={ (value) => setAttributes({ source: value }) }
                    />
                    <SelectControl
                        label={ __( 'Category', 'easycommerce' ) }
                        value={ category }
                        options={ categoryOptions }
                        onChange={ (value) => setAttributes({ category: value }) }
                    />
                    <RangeControl
                        label={ __( 'Number of products', 'easycommerce' ) }
                        value={ count }
                        min={ 1 }
                        max={ 12 }
                        onChange={ (value) => setAttributes({ count: value }) }
                    />
                    <RangeControl
                        label={ __( 'Columns', 'easycommerce' ) }
                        value={ columns }
                        min={ 1 }
                        max={ 4 }
                        onChange={ (value) => setAttributes({ columns: value }) }
                    />
                </PanelBody>
            </InspectorControls>

            { ServerSideRender ? (
                // Disabled keeps the previewed cards inert inside the canvas.
                <Disabled>
                    <ServerSideRender
                        block="easycommerce/product-collection"
                        attributes={ attributes }
                        EmptyResponsePlaceholder={ Empty }
                    />
                </Disabled>
            ) : (
                <Empty />
            ) }
        </div>
    );
};

export default Edit;
