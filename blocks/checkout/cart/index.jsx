import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { Slot } from '@wordpress/components';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

const cartIcon = (
    <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
    >
        <g clip-path="url(#clip0_10678_1681)">
            <path
                d="M2.5 3.4375C2.55208 2.86458 2.86458 2.55208 3.4375 2.5H6.25C6.74479 2.52604 7.05729 2.78646 7.1875 3.28125L7.22656 3.75H23.6719C24.0885 3.77604 24.4141 3.94531 24.6484 4.25781C24.8828 4.57031 24.9609 4.9349 24.8828 5.35156L22.7734 12.8516C22.5651 13.4245 22.1615 13.724 21.5625 13.75H9.17969L9.53125 15.625H21.5625C22.1354 15.6771 22.4479 15.9896 22.5 16.5625C22.4479 17.1354 22.1354 17.4479 21.5625 17.5H8.71094C8.24219 17.474 7.94271 17.2266 7.8125 16.7578L5.46875 4.375H3.4375C2.86458 4.32292 2.55208 4.01042 2.5 3.4375ZM7.61719 5.625L8.82812 11.875H21.0938L22.8516 5.625H7.61719ZM11.25 20.625C11.224 21.1458 11.0417 21.5885 10.7031 21.9531C10.3385 22.2917 9.89583 22.474 9.375 22.5C8.85417 22.474 8.41146 22.2917 8.04688 21.9531C7.70833 21.5885 7.52604 21.1458 7.5 20.625C7.52604 20.1042 7.70833 19.6615 8.04688 19.2969C8.41146 18.9583 8.85417 18.776 9.375 18.75C9.89583 18.776 10.3385 18.9583 10.7031 19.2969C11.0417 19.6615 11.224 20.1042 11.25 20.625ZM18.75 20.625C18.776 20.1042 18.9583 19.6615 19.2969 19.2969C19.6615 18.9583 20.1042 18.776 20.625 18.75C21.1458 18.776 21.5885 18.9583 21.9531 19.2969C22.2917 19.6615 22.474 20.1042 22.5 20.625C22.474 21.1458 22.2917 21.5885 21.9531 21.9531C21.5885 22.2917 21.1458 22.474 20.625 22.5C20.1042 22.474 19.6615 22.2917 19.2969 21.9531C18.9583 21.5885 18.776 21.1458 18.75 20.625ZM4.0625 6.25C4.63542 6.30208 4.94792 6.61458 5 7.1875C4.94792 7.76042 4.63542 8.07292 4.0625 8.125H0.9375C0.364583 8.07292 0.0520833 7.76042 0 7.1875C0.0520833 6.61458 0.364583 6.30208 0.9375 6.25H4.0625ZM4.6875 9.375C5.26042 9.42708 5.57292 9.73958 5.625 10.3125C5.57292 10.8854 5.26042 11.1979 4.6875 11.25H0.9375C0.364583 11.1979 0.0520833 10.8854 0 10.3125C0.0520833 9.73958 0.364583 9.42708 0.9375 9.375H4.6875ZM5.3125 12.5C5.88542 12.5521 6.19792 12.8646 6.25 13.4375C6.19792 14.0104 5.88542 14.3229 5.3125 14.375H0.9375C0.364583 14.3229 0.0520833 14.0104 0 13.4375C0.0520833 12.8646 0.364583 12.5521 0.9375 12.5H5.3125Z"
                fill="#B851FD"
            />
        </g>
        <defs>
            <clipPath id="clip0_10678_1681">
                <rect width="24" height="24" fill="white" />
            </clipPath>
        </defs>
    </svg>
);

/**
 * Filters the cart block configuration.
 *
 * @since 1.0.0
 * @param {Object} config The block configuration.
 */
const blockConfig = applyFilters('easycommerce.blocks.cart.config', {
    title: __('Cart', 'easycommerce'),
    icon: cartIcon,
    category: 'easycommerce-checkout',
});

registerBlockType('easycommerce/checkout--cart', {
    ...blockConfig,
    edit: () => {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps} className="">
                <div className="flex justify-between items-center">
                    <h3 className="mt-0 font-semibold">{__('Your Cart', 'easycommerce')}</h3>
                    <button className="easycommerce-clear-cart">
                        {__('Clear cart', 'easycommerce')}
                    </button>
                </div>
                <p>{__('This is your cart being edited.', 'easycommerce')}</p>
                <Slot name="easycommerce.blocks.cart.edit" props={{ blockProps }} />
            </div>
        );
    },
    save: () => {
        const blockProps = useBlockProps.save();
        return (
            <div {...blockProps} className="">
                <div className="flex justify-between items-center">
                    <h3 className="mt-0 font-semibold">{__('Your Cart', 'easycommerce')}</h3>
                    <button className="easycommerce-clear-cart">
                        {__('Clear cart', 'easycommerce')}
                    </button>
                </div>
                <div id="easycommerce-cart-content"></div>
            </div>
        );
    },
});
