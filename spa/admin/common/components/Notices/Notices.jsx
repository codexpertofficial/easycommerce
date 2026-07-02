import { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import NoticeItem from './NoticeItem';

const Notices = ({ screen = null, noticeType = null }) => {
    const [notices, setNotices] = useState([]);

    const removeNotice = (noticeId) => {
        setNotices(prev => prev.filter(n => n.id !== noticeId));
    };

    useEffect(() => {
        const params = new URLSearchParams();
        if (noticeType) params.append('type', noticeType);
        if (screen)     params.append('screen', screen);

        const queryString = params.toString();
        const apiUrl = `${EASYCOMMERCE.rest_base}/notices${queryString ? `?${queryString}` : ''}`;

        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': EASYCOMMERCE.nonce,
            },
            credentials: 'same-origin',
        })
            .then(res => res.json())
            .then(data => {
                if (data?.success && data?.data?.notices) {
                    const rawNotices = data.data.notices;

                    let noticesArray = [];
                    if (Array.isArray(rawNotices)) {
                        noticesArray = rawNotices;
                    } else if (typeof rawNotices === 'object' && rawNotices !== null) {
                        noticesArray = Object.values(rawNotices);
                    }

                    setNotices(noticesArray);
                }
            });
    }, [screen, noticeType]);

    if (notices.length === 0) {
        return null;
    }

    return (
        notices.map(notice => (
            <NoticeItem
                key={notice.id}
                notice={notice}
                onDismiss={removeNotice}
            />
        ))
    );
};

export default Notices;