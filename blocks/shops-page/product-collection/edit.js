import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl, Spinner, Placeholder } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const SOURCES = [
    { label: __( 'Featured', 'easycommerce' ), value: 'featured' },
    { label: __( 'Best sellers', 'easycommerce' ), value: 'best-selling' },
    { label: __( 'New arrivals', 'easycommerce' ), value: 'newest' },
    { label: __( 'All products', 'easycommerce' ), value: 'all' },
];

const Edit = ({ attributes, setAttributes }) => {
    const { source, category, count, columns } = attributes;

    const [products, setProducts] = useState([]);
    const [categories, setCategories] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    const blockProps = useBlockProps({ className: 'easycommerce ec-product-collection' });

    // Load categories once for the selector.
    useEffect(() => {
        fetch(`${EASYCOMMERCE.rest_base}/products/categories`)
            .then((res) => res.json())
            .then((data) => setCategories(data?.data?.categories || []))
            .catch(() => setCategories([]));
    }, []);

    // Preview products (server render is authoritative; this is an editor approximation).
    useEffect(() => {
        setIsLoading(true);
        fetch(`${EASYCOMMERCE.rest_base}/products?per_page=${count}`)
            .then((res) => res.json())
            .then((data) => {
                setProducts(data?.data?.products || []);
                setIsLoading(false);
            })
            .catch(() => setIsLoading(false));
    }, [count, source, category]);

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
                        max={ 6 }
                        onChange={ (value) => setAttributes({ columns: value }) }
                    />
                </PanelBody>
            </InspectorControls>

            { isLoading ? (
                <Placeholder><Spinner /></Placeholder>
            ) : products.length === 0 ? (
                <Placeholder
                    label={ __( 'Product Collection', 'easycommerce' ) }
                    instructions={ __( 'No products yet. Once you add products they will appear here on the front end.', 'easycommerce' ) }
                />
            ) : (
                <div
                    className="ec-product-collection__grid"
                    style={ { display: 'grid', gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`, gap: '24px' } }
                >
                    { products.slice(0, count).map((product) => (
                        <div key={ product.id } className="ec-product-collection__item">
                            { product.thumbnail && (
                                <img src={ product.thumbnail } alt={ product.title } style={ { width: '100%', height: 'auto', display: 'block' } } />
                            ) }
                            <p className="ec-product-collection__title">{ product.title }</p>
                            <p className="ec-product-collection__price">{ product.formatted_price || product.price }</p>
                        </div>
                    )) }
                </div>
            ) }
        </div>
    );
};

export default Edit;
