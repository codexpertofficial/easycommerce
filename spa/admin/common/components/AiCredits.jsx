import React from 'react';
import { __ } from '@wordpress/i18n';

const pluralize = (word, count) => {
    return count > 1 ? `${word}s` : word;
}

const AiCredits = ({ usage }) => {
	return (
        <>
            <span className="text-ec-light-black">
                {__(`Uses ${usage} ${pluralize('credit', usage)}.`, 'easycommerce')}
            </span>{' '}
            <span className="text-ec-title">
                {EASYCOMMERCE.credits + ' ' + pluralize('Credit', EASYCOMMERCE.credits)}
            </span>{' '}
            <span className="text-ec-light-black">
                {__('Remaining', 'easycommerce')}
            </span>
        </>
    );
};

export default AiCredits;
