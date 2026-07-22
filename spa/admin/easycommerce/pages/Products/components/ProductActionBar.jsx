import React, { useState } from 'react';
import ActionDropdown from '../../../../common/components/inputs/ActionDropdown';
import DeletePopup from '../../../../common/components/DeletePopup';
import BulkPopup from '../../../../common/components/BulkPopup';
import { toast } from 'react-toastify';
import { __, _n } from '@wordpress/i18n';

const productStatusesRaw = EASYCOMMERCE.product_statuses;

const productStatuses = Object.entries(productStatusesRaw).map(([value, label]) => ({
    label,
    value,
}));

const ProductActionBar = ({
    selectedProducts = [],
    setSelectedProducts,
    setProducts,
    fetchProductsStatuses,
    productSetStatusCounts,
}) => {
    const [selectedStatus, setSelectedStatus] = useState(null);
    const [showDeletePopup, setShowDeletePopup] = useState(false);
    const [showBulkPopup, setShowBulkPopup] = useState(false);

    const handleStatusChange = (option) => {
        setSelectedStatus(option);
        setShowBulkPopup(true);
    };

    const updateLocalStatusCounts = (productsToUpdate, newStatus) => {
        productSetStatusCounts((prev) => {
            const updated = { ...prev };

            productsToUpdate.forEach(product => {
                const originalStatus = product.status;
                
                if (originalStatus && updated[originalStatus] > 0) {
                    updated[originalStatus] -= 1;
                }
                if (newStatus !== 'deleted') {
                    updated[newStatus] = (updated[newStatus] || 0) + 1;
                }
            });
            
            return updated;
        });
    };

    const handleBulkApply = () => {
        if (!selectedStatus?.value) {
            toast.error(__('Please select a valid status.', 'easycommerce'));
            return;
        }

        easycommerce_modal(true);
        setShowBulkPopup(false);

        const productsToUpdate = [];
        setProducts(prev => {
            prev.forEach(product => {
                if (selectedProducts.includes(product.id)) {
                    productsToUpdate.push({ id: product.id, status: product.status });
                }
            });
            return prev;
        });

        fetch(`${EASYCOMMERCE.rest_base}/products/update-statuses`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': EASYCOMMERCE.nonce,
            },
            body: JSON.stringify({
                product_ids: selectedProducts,
                status: selectedStatus.value,
            }),
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setProducts(prev =>
                        prev.map(product =>
                            selectedProducts.includes(product.id)
                                ? { ...product, status: selectedStatus.value }
                                : product
                        )
                    );
                    
                    updateLocalStatusCounts(productsToUpdate, selectedStatus.value);
                    toast.success(__('Status updated successfully', 'easycommerce'));
                    fetchProductsStatuses();
                    
                    setSelectedProducts([]);
                    setSelectedStatus(null);
                } else {
                    toast.error(__('Failed to update products.', 'easycommerce'));
                }
                easycommerce_modal(false);
            })
            .catch((error) => {
                toast.error(__('An error occurred while updating statuses.', 'easycommerce'));
                easycommerce_modal(false);
            });
    };

    const handleBulkDelete = async () => {
        const productIds = selectedProducts.map(id => parseInt(id, 10));
        const productsToUpdate = [];
        setProducts(prev => {
            prev.forEach(product => {
                if (productIds.includes(product.id)) {
                    productsToUpdate.push({ id: product.id, status: product.status });
                }
            });
            return prev;
        });

        try {
            easycommerce_modal(true);
            setShowDeletePopup(false);

            const res = await fetch(`${EASYCOMMERCE.rest_base}/products/bulk-delete`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
                body: JSON.stringify({ product_ids: productIds }),
            });

            const data = await res.json();
            easycommerce_modal(false);

            if (data.success) {
                updateLocalStatusCounts(productsToUpdate, 'deleted');
                toast.success(_n('Product deleted', 'Products deleted', productIds.length, 'easycommerce'));
                setProducts(prev => prev.filter(product => !productIds.includes(product.id)));
                fetchProductsStatuses();
                setSelectedProducts([]);
            } else {
                toast.error(__('Delete failed.', 'easycommerce'));
            }
        } catch (err) {
            console.error('Delete failed:', err);
            toast.error(__('Something went wrong.', 'easycommerce'));
            easycommerce_modal(false);
        }
    };

    return (
        <div className="flex flex-row gap-3 h-9 items-center mb-[2px]">
            <div className="w-[160px]">
                <ActionDropdown
                    options={productStatuses}
                    placeholder={__("Set Status", "easycommerce")}
                    value={selectedStatus}
                    onChange={handleStatusChange}
                />
            </div>

            <button
                onClick={() => setShowDeletePopup(true)}
                className="group flex h-8 w-8 justify-center items-center hover:bg-ec-red transition-colors
                duration-300 border ec-table-stock rounded-full"
            >
                <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg" className="size-4 fill-ec-body group-hover:fill-white duration-300">
                    <path fillRule="evenodd" clipRule="evenodd" d="M15.1501 6.92017H1.85394L2.90454 17.3333C2.99002 18.184 3.68756 18.822 4.53044 18.822H12.4701C13.3147 18.822 14.0105 18.184 14.096 17.3333L15.1466 6.92017H15.1501ZM15.8442 5.1667C15.8442 4.37763 15.2074 3.73094 14.4303 3.73094H2.57632C1.79927 3.73094 1.16243 4.37765 1.16243 5.1667V5.74221H15.8443L15.8442 5.1667ZM5.04676 2.55558V2.49134C5.04676 1.1198 6.14951 0 7.50016 0H9.50217C10.8528 0 11.9556 1.1198 11.9556 2.49134V2.55558H14.4287C15.8443 2.55558 17 3.72918 17 5.1667V6.33077C17 6.65542 16.7393 6.91931 16.4204 6.91931H16.3084L15.245 17.4522C15.098 18.9036 13.9038 19.9991 12.4678 19.9991L4.53225 20C3.09698 20 1.90182 18.9045 1.75498 17.4531L0.691554 6.92017H0.579571C0.25987 6.92017 0 6.65542 0 6.33164V5.16757C0 3.73005 1.15573 2.55645 2.57135 2.55645H5.04444L5.04676 2.55558ZM10.7972 2.55558V2.49134C10.7972 1.76912 10.2125 1.17536 9.50123 1.17536H7.49921C6.78799 1.17536 6.20328 1.76912 6.20328 2.49134V2.55558H10.7972ZM5.75952 14.8916C5.75952 15.2163 5.49879 15.4802 5.17995 15.4802C4.86025 15.4802 4.60038 15.2154 4.60038 14.8916V10.8005C4.60038 10.4759 4.8611 10.212 5.17995 10.212C5.49965 10.212 5.75952 10.4767 5.75952 10.8005V14.8916ZM11.2261 10.8005C11.2261 10.4759 11.4868 10.212 11.8057 10.212C12.1254 10.212 12.3852 10.4767 12.3852 10.8005V14.8916C12.3852 15.2163 12.1245 15.4802 11.8057 15.4802C11.486 15.4802 11.2261 15.2154 11.2261 14.8916V10.8005ZM9.07277 15.9421C9.07277 16.2667 8.81205 16.5306 8.4932 16.5306C8.1735 16.5306 7.91363 16.2659 7.91363 15.9421V9.7485C7.91363 9.42385 8.17435 9.15996 8.4932 9.15996C8.8129 9.15996 9.07277 9.42472 9.07277 9.7485V15.9421Z" />
                </svg>
            </button>

            {showDeletePopup && (
                <DeletePopup
                    onClose={() => setShowDeletePopup(false)}
                    onConfirm={handleBulkDelete}
                    itemName={`${selectedProducts.length} product${selectedProducts.length > 1 ? 's' : ''}`}
                />
            )}

            {showBulkPopup && (
                <BulkPopup
                    onClose={() => setShowBulkPopup(false)}
                    onConfirm={handleBulkApply}
                    itemName={`${selectedProducts.length} product${selectedProducts.length > 1 ? 's' : ''}`}
                />
            )}
        </div>
    );
};

export default ProductActionBar;
