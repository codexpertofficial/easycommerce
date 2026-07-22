import React, { useState } from 'react';
import { __, _n, sprintf } from '@wordpress/i18n';
import DeletePopup from '../../../../common/components/DeletePopup';
import { toast } from 'react-toastify';
import BulkReminderPopup from './BulkReminderPopup';

const ActionBar = ({ selectedCarts = [], statusCounts, setRefreshList, setSelectedCarts, setAbandonedCarts }) => {
    const [showDeletePopup, setShowDeletePopup] = useState(false);
    const [showBulkPopup, setShowBulkPopup] = useState(false);

    const handleBulkApply = () => {
        easycommerce_modal(true);
        setShowBulkPopup(false);
        fetch(`${EASYCOMMERCE.rest_base}/abandoned-carts/bulk/reminder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': EASYCOMMERCE.nonce,
            },
            body: JSON.stringify({
                abandoned_cart_hashes: selectedCarts
            }),
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    statusCounts();
                    setRefreshList(prev => !prev);
                    toast.success(__('Reminders sent successfully', 'easycommerce'));
                    setSelectedCarts([]);
                }
                easycommerce_modal(false);
            });
    };
    
    const handleBulkDelete = () => {
        easycommerce_modal(true);
        setShowDeletePopup(false);
        fetch(`${EASYCOMMERCE.rest_base}/abandoned-carts/bulk/delete`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': EASYCOMMERCE.nonce,
            },
            body: JSON.stringify({ abandoned_cart_hashes: selectedCarts }),
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    statusCounts();
                    setRefreshList(prev => !prev);
                    setSelectedCarts([]);
                    toast.success(__('carts deleted successfully', 'easycommerce'));
                }
                easycommerce_modal(false);
            });
    };

    return (
        <div className="flex flex-row gap-3 h-9 items-center mb-[2px]">
   
            <button
                onClick={() => setShowBulkPopup(true)}
                className="relative h-ec-input group text-sm font-inter text-ec-primary font-medium border border-ec-primary py-2 
                px-3 rounded-lg hover:bg-ec-primary hover:text-white transition duration-200 ease-in-out flex items-center gap-2"
            >
                {__('Remind All', 'easycommerce')}
                <svg width="13" height="12" viewBox="0 0 13 12" fill="none" className="fill-current" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.6719 0.140625C11.9219 0.3125 12.0234 0.5625 11.9766 0.890625L10.4766 10.6172C10.4297 10.8516 10.3047 11.0312 10.1016 11.1562C10.0078 11.2031 9.88281 11.2344 9.72656 11.25C9.63281 11.25 9.53906 11.2344 9.44531 11.2031L6.60938 10.0078L4.61719 11.9531C4.57031 11.9844 4.52344 12 4.47656 12C4.42969 12 4.38281 11.9531 4.33594 11.8594L2.88281 8.4375L0.46875 7.42969C0.1875 7.28906 0.03125 7.07031 0 6.77344C0 6.49219 0.125 6.25781 0.375 6.07031L10.875 0.09375C11 0.03125 11.125 0 11.25 0C11.4062 0 11.5469 0.046875 11.6719 0.140625ZM0.75 6.75L2.90625 7.64062L9.65625 1.66406L0.75 6.75ZM4.71094 10.8281L5.92969 9.67969L5.10938 9.35156C5 9.30469 4.92969 9.21875 4.89844 9.09375C4.86719 8.98438 4.88281 8.88281 4.94531 8.78906L8.92969 3.32812L3.53906 8.08594L4.71094 10.8281ZM9.77344 10.3828L11.1328 1.54688L5.83594 8.83594L9.77344 10.3828Z"/>
                </svg>
            </button>

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
                    itemName={sprintf(
                        // translators: %d: number of carts.
                        _n('%d cart', '%d carts', selectedCarts.length, 'easycommerce'),
                        selectedCarts.length
                    )}
                />
            )}

            {showBulkPopup && (
                <BulkReminderPopup
                    onClose={() => setShowBulkPopup(false)}
                    onConfirm={handleBulkApply}
                    itemName={sprintf(
                        // translators: %d: number of carts.
                        _n('%d cart', '%d carts', selectedCarts.length, 'easycommerce'),
                        selectedCarts.length
                    )}
                />
            )}
        </div>
    );
};

export default ActionBar;
