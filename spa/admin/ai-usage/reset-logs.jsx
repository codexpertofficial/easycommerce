import { createRoot } from 'react-dom/client';
import { __ } from '@wordpress/i18n';  
import DeletePopup from '../common/components/DeletePopup';

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('ec-ai-reset-logs-btn');
    const mountEl = document.getElementById('ec-ai-reset-logs-modal-root');
    if (!btn || !mountEl) return;

    const root = createRoot(mountEl);
    const close = () => root.render(null);

    btn.addEventListener('click', () => {
        root.render(
        
            <DeletePopup
                title={__('Remove all AI usage logs?', 'easycommerce')}
                description={__("This permanently deletes your AI usage history. This can't be undone.", 'easycommerce')}
                confirmLabel={__('Yes, Remove', 'easycommerce')}
                cancelLabel={__('No, Keep it', 'easycommerce')}
                onClose={close}
                onConfirm={async () => {
                    btn.disabled = true;
                    btn.style.opacity = '0.6';
                    try {
                        const res = await fetch(ajaxurl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'ec_ai_reset_logs',
                                nonce: btn.dataset.nonce,
                            }),
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.location.reload();
                        } else {
                            close();
                            btn.disabled = false;
                            btn.style.opacity = '';
                            alert(data.data?.message || 'Failed to reset logs.');
                        }
                    } catch (e) {
                        close();
                        btn.disabled = false;
                        btn.style.opacity = '';
                        alert('Something went wrong. Please try again.');
                    }
                }}
            />
        );
    });
});