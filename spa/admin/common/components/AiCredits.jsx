import React from 'react';
import { __, _n, sprintf } from '@wordpress/i18n';

const AiCredits = ({ usage }) => {
	return (
        <>
            <span className="text-ec-light-black">
                {
                    // translators: %d: number of AI credits this action uses.
                    sprintf( _n( 'Uses %d credit.', 'Uses %d credits.', usage, 'easycommerce' ), usage )
                }
            </span>{' '}
            <span className="text-ec-title">
                {
                    // translators: %d: number of AI credits remaining.
                    sprintf( _n( '%d Credit', '%d Credits', EASYCOMMERCE.credits, 'easycommerce' ), EASYCOMMERCE.credits )
                }
            </span>{' '}
            <span className="text-ec-light-black">
                {__('Remaining', 'easycommerce')}
            </span>
        </>
    );
};

export default AiCredits;
