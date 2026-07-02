import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { Slot } from '@wordpress/components';
import { applyFilters } from '@wordpress/hooks';
import './style.css';

const billingAddressIcon = (
    <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path
            d="M12.625 13.75C13.5104 13.776 14.2526 14.0755 14.8516 14.6484C15.4245 15.2474 15.724 15.9896 15.75 16.875C15.724 17.2656 15.5156 17.474 15.125 17.5H7.625C7.23438 17.474 7.02604 17.2656 7 16.875C7.02604 15.9896 7.32552 15.2474 7.89844 14.6484C8.4974 14.0755 9.23958 13.776 10.125 13.75H12.625ZM11.375 12.5C10.6719 12.474 10.0859 12.2266 9.61719 11.7578C9.14844 11.2891 8.90104 10.7031 8.875 10C8.90104 9.29688 9.14844 8.71094 9.61719 8.24219C10.0859 7.77344 10.6719 7.52604 11.375 7.5C12.0781 7.52604 12.6641 7.77344 13.1328 8.24219C13.6016 8.71094 13.849 9.29688 13.875 10C13.849 10.7031 13.6016 11.2891 13.1328 11.7578C12.6641 12.2266 12.0781 12.474 11.375 12.5ZM21.375 15C21.7656 15.026 21.974 15.2344 22 15.625V18.125C21.974 18.5156 21.7656 18.724 21.375 18.75H20.75V15H21.375ZM21.375 5C21.7656 5.02604 21.974 5.23438 22 5.625V8.125C21.974 8.51562 21.7656 8.72396 21.375 8.75H20.75V5H21.375ZM21.375 10C21.7656 10.026 21.974 10.2344 22 10.625V13.125C21.974 13.5156 21.7656 13.724 21.375 13.75H20.75V10H21.375ZM17 2.5C17.7031 2.52604 18.2891 2.77344 18.7578 3.24219C19.2266 3.71094 19.474 4.29688 19.5 5V20C19.474 20.7031 19.2266 21.2891 18.7578 21.7578C18.2891 22.2266 17.7031 22.474 17 22.5H5.75C5.04688 22.474 4.46094 22.2266 3.99219 21.7578C3.52344 21.2891 3.27604 20.7031 3.25 20V5C3.27604 4.29688 3.52344 3.71094 3.99219 3.24219C4.46094 2.77344 5.04688 2.52604 5.75 2.5H17ZM17.625 20V5C17.599 4.60938 17.3906 4.40104 17 4.375H5.75C5.35938 4.40104 5.15104 4.60938 5.125 5V20C5.15104 20.3906 5.35938 20.599 5.75 20.625H17C17.3906 20.599 17.599 20.3906 17.625 20Z"
            fill="#EB001F"
        />
    </svg>
);

/**
 * Filters the billing address block configuration.
 *
 * @since 1.0.0
 * @param {Object} config The block configuration.
 */
const blockConfig = applyFilters('easycommerce.blocks.billing-address.config', {
    title: 'Billing Address',
    icon: billingAddressIcon,
    category: 'easycommerce-checkout',
});

registerBlockType('easycommerce/checkout--billing-address', {
    ...blockConfig,
    edit: () => {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps}>
                <h3>Billing Address</h3>
                <p>This is your billing address being edited.</p>
                <Slot name="easycommerce.blocks.billing-address.edit" props={{ blockProps }} />
            </div>
        );
    },
    save: () => {
        const blockProps = useBlockProps.save();
        return (
            <div {...blockProps}>
                <h3>Billing Address</h3>
                <p>This is your billing address as saved.</p>
            </div>
        );
    },
});
