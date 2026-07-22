import {useState} from 'react'
import { __ } from '@wordpress/i18n';
import { toast } from "react-toastify";
import DeletePopup from '../../../../../common/components/DeletePopup'

const ProductActions = ({data}) => {
    const [isActionOpen, setIsActionOpen] = useState(false)
    const [isDeletePopupOpen, setIsDeletePopupOpen] = useState(false)

    const buttonClass = 'group px-3 flex items-center gap-2 py-2 text-left duration-300 text-[14px] font-normal leading-[26px] cursor-pointer rounded-[4px] m-0'

    const deleteAction = () => {
        easycommerce_modal(true);

        fetch(
            `${EASYCOMMERCE.rest_base}/products/${data.id}`,
            {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": EASYCOMMERCE.nonce,
                }
            }
        )
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success) {
                    toast.success(__('Product trashed!', 'easycommerce'));
                    window.location.hash = `#/products`;
                }
            });
    };

    const deleteNow = () => {
        easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/products/${data.id}?force=true`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            }
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success) {
                    toast.success(__('Product deleted permanently!', 'easycommerce'));
                    window.location.hash = `#/products`;
                }
            });
    };

    return (
        <div className='relative'>
            <button 
                type="button" 
                className="h-[41px] w-[41px] flex items-center justify-center rounded-lg border border-ec-primary bg-white"
                onClick={() => setIsActionOpen(prev => !prev)}
                onBlur={() => {
                    setTimeout(() => setIsActionOpen(false), 500)
                }}
            >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 6H18" stroke="#7351FD" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M6 10H18" stroke="#7351FD" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M6 14H18" stroke="#7351FD" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M6 18H18" stroke="#7351FD" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </button>

            {isActionOpen && 
            <div className="border w-[120px] bg-white border-ec-border rounded-lg shadow-2xl p-2 font-inter flex flex-col absolute top-[48px]">
                <a className={buttonClass + ' text-ec-body hover:bg-[#F8F8F8]'} href={`${EASYCOMMERCE.product_edit_base}=${data.id}`} target='_blank'>
                    {__('Builder', 'easycommerce')}
                </a>
                <button 
                    type='button' 
                    className={buttonClass + ' text-[#FF3A52] hover:bg-[#FF3A520D]'}
                    onClick={() => setIsDeletePopupOpen(true)}
                >
                    {__('Delete', 'easycommerce')}
                </button>
            </div>}

            {isDeletePopupOpen && 
            <DeletePopup
                onClose={() => setIsDeletePopupOpen(false)}
                itemName={data.title}
                isProduct={true}
                onConfirm={() => deleteAction()}
                onPermanentDelete={() => deleteNow()}
            />}
        </div>
    )
}

export default ProductActions