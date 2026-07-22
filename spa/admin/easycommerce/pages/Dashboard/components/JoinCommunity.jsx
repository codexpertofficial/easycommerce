import React from 'react';
import { __ } from '@wordpress/i18n';
import Container from './common/Container';

const communityUrl = EASYCOMMERCE.community_url;

const JoinCommunity = () => {
    return (
        <Container title={__('Join Community', 'easycommerce')}>
            <p className="text-ec-body text-sm leading-relaxed">
                {__('Join the community and post your issue to get a faster response.', 'easycommerce')}
            </p>
            <a
                href={communityUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="mt-4 inline-flex items-center justify-center rounded-lg bg-ec-primary text-white text-sm py-3 px-5 duration-300 hover:opacity-90"
            >
                {__('Join Community', 'easycommerce')}
            </a>
        </Container>
    );
};

export default JoinCommunity;
