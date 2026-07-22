import React, { useState, useEffect, useRef } from "react";
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __, sprintf } from '@wordpress/i18n';
import { toast } from 'react-toastify';

const EDITOR_ID = "easycommerce-dashboard-reminder-editor";

const AbandonedCartModal = ({ item, onClose, onDeleted, onSent }) => {
    const [activeTab, setActiveTab] = useState("reminder");
    const [subject, setSubject] = useState("");
    const [message, setMessage] = useState("");
    const editorRef = useRef(null);
    const editorInitialized = useRef(false);
    const [cartDetails, setCartDetails] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isDeleting, setIsDeleting] = useState(false);
    const [isSending, setIsSending] = useState(false);
    const [isFetchingEmail, setIsFetchingEmail] = useState(true);
    const [sendStatus, setSendStatus] = useState(null);
    const [sendMessage, setSendMessage] = useState('');

    useEffect(() => {
        if (!item?.hash) return;

        setIsLoading(true);
        setCartDetails(null);
        setSendStatus(null);

        apiFetch({
            path: addQueryArgs('/easycommerce/v1/dashboard/abandoned-cart', { hash: item.hash }),
        }).then((data) => {
            if (data.success) {
                setCartDetails(data.data);
            }
            setIsLoading(false);
        }).catch(() => {
            setIsLoading(false);
        });
    }, [item?.hash]);

    useEffect(() => {
        if (!item?.hash) return;
        setIsFetchingEmail(true);
        apiFetch({
            path: addQueryArgs('/easycommerce/v1/abandoned-carts/email-content', { hash: item.hash }),
        }).then((data) => {
            if (data.success) {
                setSubject(data.data.subject || "");
                setMessage(data.data.body || "");
            }
        }).catch(() => {}).finally(() => setIsFetchingEmail(false));
    }, [item?.hash]);

    useEffect(() => {
        if (activeTab !== "reminder" || isFetchingEmail) return;
        if (!editorRef.current || editorInitialized.current) return;

        editorInitialized.current = true;
        const savedBody = message;

        window.tinymce.init({
            target: editorRef.current,
            plugins: ["link", "lists"],
            toolbar: "bold italic underline | link | alignleft aligncenter alignright | bullist numlist",
            relative_urls: false,
            remove_script_host: false,
            document_base_url: window.location.origin + "/",
            statusbar: false,
            height: 200,
            resize: false,
            setup: (editor) => {
                editor.on("init", () => {
                    editor.setContent(savedBody);
                });
            },
        });

        return () => {
            const editor = window.tinymce.get(EDITOR_ID);
            if (editor) editor.remove();
            editorInitialized.current = false;
        };
    }, [activeTab, isFetchingEmail]);

    const handleDelete = () => {
        setIsDeleting(true);
        apiFetch({
            path: '/easycommerce/v1/abandoned-carts/remove',
            method: 'DELETE',
            data: { hash: item.hash },
        }).then(() => {
            setIsDeleting(false);
            toast.success(__( 'Abandoned cart deleted successfully', 'easycommerce' ));
            onDeleted ? onDeleted() : onClose();
        }).catch(() => {
            toast.error(__( 'Failed to delete abandoned cart. Please try again.', 'easycommerce' ));
            setIsDeleting(false);
        });
    };

    const handleSendEmail = () => {
        const currentMessage = window.tinymce?.get(EDITOR_ID)?.getContent() ?? message;
        if (!subject.trim() || !currentMessage.trim()) return;

        setIsSending(true);
        setSendStatus(null);

        apiFetch({
            path: '/easycommerce/v1/dashboard/abandoned-cart/send-reminder',
            method: 'POST',
            data: { hash: item.hash, subject, message: currentMessage },
        }).then((data) => {
            const sent = data.data?.sent;
            if (sent) {
                toast.success(data.data?.message || __( 'Reminder email sent successfully.', 'easycommerce' ));
                if (onSent) onSent();
                onClose();
            } else {
                toast.error(data.data?.message || __( 'Failed to send reminder email.', 'easycommerce' ));
                setSendStatus('error');
                setSendMessage(data.data?.message || '');
            }
            setIsSending(false);
        }).catch(() => {
            toast.error(__( 'Something went wrong. Please try again.', 'easycommerce' ));
            setSendStatus('error');
            setSendMessage(__( 'Something went wrong. Please try again.', 'easycommerce' ));
            setIsSending(false);
        });
    };

    const formatLastActivity = (updatedAt) => {
        if (!updatedAt) return '—';
        const diff = Math.floor((Date.now() - new Date(updatedAt).getTime()) / 1000);
        // translators: %d: number of seconds.
        if (diff < 60) return sprintf( __( '%ds ago', 'easycommerce' ), diff );
        // translators: %d: number of minutes.
        if (diff < 3600) return sprintf( __( '%dm ago', 'easycommerce' ), Math.floor(diff / 60) );
        // translators: %d: number of hours.
        if (diff < 86400) return sprintf( __( '%dh ago', 'easycommerce' ), Math.floor(diff / 3600) );
        // translators: %d: number of days.
        return sprintf( __( '%dd ago', 'easycommerce' ), Math.floor(diff / 86400) );
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-[#0000003B] backdrop-blur-sm">
            <div className="bg-white rounded-lg w-[850px] relative max-h-[90vh]">
                <button
                    className="absolute top-[-20px] right-[-23px] group w-6 h-6 rounded-full bg-white hover:bg-[#fa4109] transition-colors duration-200 flex items-center justify-center"
                    onClick={onClose}
                    aria-label={__( 'Close', 'easycommerce' )}
                >
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path className="fill-[#3C3C42] group-hover:fill-white transition-colors duration-300" fillRule="evenodd" clipRule="evenodd" d="M0.260418 0.260418C0.607642 -0.086806 1.17015 -0.086806 1.51731 0.260418L5 3.7431L8.48269 0.260418C8.82991 -0.086806 9.39241 -0.086806 9.73958 0.260418C10.0868 0.607642 10.0868 1.17015 9.73958 1.51731L6.2569 5L9.73958 8.48269C10.0868 8.82991 10.0868 9.39241 9.73958 9.73958C9.39236 10.0868 8.82985 10.0868 8.48269 9.73958L5 6.2569L1.51731 9.73958C1.17009 10.0868 0.607587 10.0868 0.260418 9.73958C-0.0867505 9.39236 -0.086806 8.82985 0.260418 8.48269L3.7431 5L0.260418 1.51731C-0.086806 1.17009 -0.086806 0.607587 0.260418 0.260418Z" />
                    </svg>
                </button>

                <h2 className="text-xl font-medium text-[#272435] p-4 border-b border-[#EEF0FF]">{__( 'Abandoned Cart Details', 'easycommerce' )}</h2>

                <div>
                    <div className="p-4">
                        <div className="grid grid-cols-4 bg-[#F8FAFC] rounded-lg p-4 mb-4 border border-[#EEF0FF]">
                            <div>
                                <p className="text-xs font-normal text-[#7A7A99] uppercase mb-1">{__( 'Customer Name', 'easycommerce' )}</p>
                                <p className="text-sm font-normal text-[#121216]">{item.name}</p>
                            </div>
                            <div>
                                <p className="text-xs font-normal text-[#7A7A99] uppercase mb-1">{__( 'Email Address', 'easycommerce' )}</p>
                                <p className="text-sm font-normal text-[#121216]">{item.email}</p>
                            </div>
                            <div>
                                <p className="text-xs font-normal text-[#7A7A99] uppercase mb-1">{__( 'Last Activity', 'easycommerce' )}</p>
                                <p className="text-sm font-normal text-[#121216]">
                                    {isLoading ? '—' : formatLastActivity(cartDetails?.updated_at)}
                                </p>
                            </div>
                            <div>
                                <p className="text-xs font-normal text-[#7A7A99] uppercase mb-1">{__( 'Total Products', 'easycommerce' )}</p>
                                <p className="text-sm font-bold text-ec-primary">
                                    {isLoading ? '—' : `${cartDetails?.item_count ?? item.items} (${cartDetails?.total ?? item.total})`}
                                </p>
                            </div>
                        </div>

                        <div className="flex bg-[#F3F4F6] rounded-xl p-1">
                            <button
                                className={`flex-1 p-3 text-sm font-normal rounded-lg transition-all duration-200 ${activeTab === "reminder" ? "bg-white text-ec-primary shadow-sm" : "text-[#4F5359]"}`}
                                onClick={() => setActiveTab("reminder")}
                            >
                                {__( 'Reminder', 'easycommerce' )}
                            </button>
                            <button
                                className={`flex-1 p-3 text-sm font-normal rounded-lg transition-all duration-200 ${activeTab === "summary" ? "bg-white text-ec-primary shadow-sm" : "text-[#4F5359]"}`}
                                onClick={() => setActiveTab("summary")}
                            >
                                {__( 'Cart Summary', 'easycommerce' )}
                            </button>
                        </div>
                    </div>

                    {activeTab === "reminder" && (
                        <div className="p-4 pt-0">
                            {isFetchingEmail ? (
                                <div className="flex justify-center items-center py-10">
                                    <div className="animate-spin w-7 h-7 border-4 border-ec-primary border-t-transparent rounded-full"></div>
                                </div>
                            ) : (<>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-[#272435] mb-2">{__( 'Subject Line', 'easycommerce' )}</label>
                                <input
                                    type="text"
                                    value={subject}
                                    onChange={(e) => setSubject(e.target.value)}
                                    placeholder={__( 'Complete your purchase - Items waiting in your cart!', 'easycommerce' )}
                                    className="w-full border border-[#EEF0FF] rounded-lg p-4 text-sm text-[#121216] outline-none focus:border-ec-primary placeholder:text-[#0A0A0A80]"
                                />
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-[#272435] mb-2">{__( 'Email Message', 'easycommerce' )}</label>
                                <div className="max-h-[350px] overflow-y-auto border border-[#EEF0FF] rounded-lg">
                                    <textarea
                                        id={EDITOR_ID}
                                        ref={editorRef}
                                    ></textarea>
                                </div>
                            </div>

                            {sendStatus === 'success' && (
                                <p className="text-sm text-green-600 mb-3">{sendMessage}</p>
                            )}
                            {sendStatus === 'error' && (
                                <p className="text-sm text-red-500 mb-3">{sendMessage}</p>
                            )}

                            <div className="flex justify-end gap-3">
                                <button
                                    onClick={handleDelete}
                                    disabled={isDeleting}
                                    className="px-6 py-3 text-sm font-medium text-red-500 border border-red-400 rounded-lg hover:bg-red-50 duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {isDeleting ? __( 'Deleting...', 'easycommerce' ) : __( 'Delete', 'easycommerce' )}
                                </button>
                                <button
                                    onClick={handleSendEmail}
                                    disabled={isSending || !subject.trim()}
                                    className="px-6 py-3 text-sm font-medium text-white bg-ec-primary rounded-lg hover:opacity-90 duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {isSending ? __( 'Sending...', 'easycommerce' ) : __( 'Send Email', 'easycommerce' )}
                                </button>
                            </div>
                            </>)}
                        </div>
                    )}

                    {activeTab === "summary" && (
                        <div className="mb-4">
                            <h3 className="text-lg font-medium text-[#3C3C42] mb-4 pl-4">{__( 'Cart Items', 'easycommerce' )}</h3>

                            <div className="grid grid-cols-[2fr_1.5fr_1fr_1fr_1fr] p-4 bg-[#F7F7F7] font-medium">
                                <p className="text-sm text-[#3C3C42]">{__( 'Product', 'easycommerce' )}</p>
                                <p className="text-sm text-[#3C3C42] text-center">{__( 'Variant', 'easycommerce' )}</p>
                                <p className="text-sm text-[#3C3C42] text-center">{__( 'Quantity', 'easycommerce' )}</p>
                                <p className="text-sm text-[#3C3C42] text-center">{__( 'Price', 'easycommerce' )}</p>
                                <p className="text-sm text-[#3C3C42] text-center">{__( 'Total', 'easycommerce' )}</p>
                            </div>

                            {isLoading ? (
                                <div className="px-4 py-6 text-sm text-[#7A7A99] text-center">{__( 'Loading cart items...', 'easycommerce' )}</div>
                            ) : (cartDetails?.items ?? []).length === 0 ? (
                                <div className="px-4 py-6 text-sm text-[#7A7A99] text-center">{__( 'No items found.', 'easycommerce' )}</div>
                            ) : (
                                (cartDetails.items).map((cartItem, index) => (
                                    <div key={index} className="grid grid-cols-[2fr_1.5fr_1fr_1fr_1fr] px-4 py-2.5 border-b border-[#F0EDFF] items-center">
                                        <div className="flex items-center gap-3">
                                            {cartItem.image ? (
                                                <img src={cartItem.image} alt={cartItem.name} className="w-[48px] h-[48px] rounded-[10px] object-cover" />
                                            ) : (
                                                <div className="w-[48px] h-[48px] rounded-[10px] bg-[#F3F3FF]" />
                                            )}
                                            <p className="text-sm text-[#121216]">{cartItem.name}</p>
                                        </div>
                                        <p className="text-sm text-[#7A7A99] text-center">{cartItem.variant || '—'}</p>
                                        <p className="text-sm text-[#121216] text-center">{cartItem.quantity}</p>
                                        <p className="text-sm text-[#121216] text-center">{cartItem.price}</p>
                                        <p className="text-sm text-[#121216] text-center">{cartItem.total}</p>
                                    </div>
                                ))
                            )}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default AbandonedCartModal;
