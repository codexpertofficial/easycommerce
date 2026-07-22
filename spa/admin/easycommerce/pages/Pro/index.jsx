import React, { useState, useEffect } from 'react';
import { applyFilters } from '@wordpress/hooks';
import FreeComponent from './components/FreeComponent';

const Pro = () => {
	const [ , forceUpdate ] = useState( 0 );

	useEffect( () => {
		if ( window.__ecProContentReady ) {
			forceUpdate( ( n ) => n + 1 );
		}

		const handler = () => forceUpdate( ( n ) => n + 1 );
		window.addEventListener( 'easycommerce-pro-ready', handler );
		return () => window.removeEventListener( 'easycommerce-pro-ready', handler );
	}, [] );

	const content = applyFilters( 'easycommerce_pro_content', null );

	return (
		<div className="mt-3 bg-white max-w-full px-[48px] rounded-xl font-inter">
			{ EASYCOMMERCE.pro.activated ? content : <FreeComponent /> }
		</div>
	);
};

export default Pro;