import { useState } from 'react';
import { __ } from '@wordpress/i18n';

const NOTICE_CONFIG = {
    error: {
        borderColor: 'border-[#F63B3B]',
        titleColor: 'text-[#F63B5A]',
        buttonBgColor: 'bg-[#F63B5A]',
        buttonTextColor: 'text-[#fff]',
        icon: 'error-icon.png',
        dismiss: 'error-dismiss.png'
    },
    warning: {
        borderColor: 'border-[#FFB109]',
        titleColor: 'text-[#f3791c]',
        buttonBgColor: 'bg-[#FFB109]',
        buttonTextColor: 'text-[#52440a]',
        icon: 'warning-icon.png',
        dismiss: 'warning-dismiss.png'
    },
    success: {
        borderColor: 'border-[#02C302]',
        titleColor: 'text-[#00a82a]',
        buttonBgColor: 'bg-[#30D459]',
        buttonTextColor: 'text-white',
        icon: 'success-icon.png',
        dismiss: 'success-dismiss.png'
    },
    info: {
        borderColor: 'border-[#A451FD]',
        titleColor: 'text-[#A451FD]',
        buttonBgColor: 'bg-[#A451FD]',
        buttonTextColor: 'text-white',
        icon: 'info-icon.png',
        dismiss: 'info-dismiss.png'
    }
};

const NoticeItem = ({ notice, onDismiss }) => {
    const [isDismissing, setIsDismissing] = useState(false);
    const config = NOTICE_CONFIG[notice.type] || NOTICE_CONFIG.info;
    const assetPath = `${EASYCOMMERCE.assets}admin/img/notices/`;

    const handleDismiss = async () => {
        if (!notice.dismissible || isDismissing) return;

        setIsDismissing(true);
        try {
            const response = await fetch(`${EASYCOMMERCE.rest_base}/notices/${notice.id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
                credentials: 'same-origin',
            });

            if (!response.ok) throw new Error(`Dismiss failed: ${response.status}`);
            
            await response.json();
            onDismiss?.(notice.id);
        } catch (err) {
            console.error('Failed to dismiss notice:', err);
        } finally {
            setIsDismissing(false);
        }
    };

    return (
        <div className={`notice bg-white border ${config.borderColor} relative flex justify-between items-center m-0 py-2 mb-3 rounded-2xl 2xl:max-h-[60px]`}>
            <div className="flex justify-between items-center gap-3">
                <img src={`${assetPath}${config.icon}`} alt="" />
                <div className="flex flex-col gap-[2px]">
                    {notice.title && (
                        <h4 className={`${config.titleColor} font-inter font-medium text-base leading-[22.4px]`}>
                            {notice.title}
                        </h4>
                    )}
                    <p className="font-inter font-normal text-sm leading-[22.4px] m-0 p-0 text-[#3C3C42]">
                        {notice.message}
                    </p>
                </div>
            </div>

            {(notice.button && notice.url) && (
                <a
                    href={notice.url}
                    target={notice.target || "_self"}
                    rel="noopener noreferrer"
                    className={`${config.buttonBgColor} block ${config.buttonTextColor} no-underline text-center text-sm py-2 px-3 rounded-[10px] 2xl:max-h-[36px]`}
                >
                    {notice.button}
                </a>
            )}

            {notice.dismissible && (
                <button
                    className="absolute top-[-5px] right-[-5px]"
                    onClick={handleDismiss}
                    disabled={isDismissing}
                    aria-label={__('Dismiss this notice', 'easycommerce')}
                >
                    <img src={`${assetPath}${config.dismiss}`} alt="" />
                </button>
            )}
        </div>
    );
};

export default NoticeItem;