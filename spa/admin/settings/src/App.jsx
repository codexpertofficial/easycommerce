import React, { useEffect, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import Header from '../../common/Header';
import RootToast from '../../common/RootToast';
import './settings-menu.css';

/**
 * Translated labels for the settings menu slugs.
 *
 * The slug itself stays the lookup key, only the label is translated.
 */
const menuLabels = {
	'general': __( 'General', 'easycommerce' ),
	'payment': __( 'Payment', 'easycommerce' ),
	'order': __( 'Orders', 'easycommerce' ),
	'checkout': __( 'Checkout', 'easycommerce' ),
	'email': __( 'Emails', 'easycommerce' ),
	'shipping': __( 'Shipping', 'easycommerce' ),
	'tax': __( 'Taxation', 'easycommerce' ),
	'abandoned-cart': __( 'Cart Recovery', 'easycommerce' ),
	'ai': __( 'AI', 'easycommerce' ),
};

/**
 * Resolves a menu slug to its translated label.
 *
 * Unknown slugs (added by third parties) fall back to the slug, title cased.
 *
 * @param {string} slug The menu slug.
 * @return {string} The label to display.
 */
const getMenuLabel = ( slug ) => {
	const key = String( slug ).toLowerCase();

	return (
		menuLabels[ key ] ||
		String( slug )
			.split( '-' )
			.map( ( word ) => word.charAt( 0 ).toUpperCase() + word.slice( 1 ) )
			.join( ' ' )
	);
};

const App = () => {
	/**
	 * Filters the default current menu.
	 *
	 * @since 1.0.0
	 * @param {string} defaultMenu The default menu.
	 */
	const defaultMenu = applyFilters(
		'easycommerce.settings.default.menu',
		'General'
	);

	const [currentMenu, setCurrentMenu] = useState(defaultMenu);
	const [showAPIModal, setShowAPIModal] = useState(false);
	const [user, setUser] = useState(null);

	const userRef = useRef(user);
	useEffect(() => { userRef.current = user; }, [user]);

	useEffect(() => {
		const queryParams = new URLSearchParams(window.location.search);
		const menu = queryParams.get('menu');
		setCurrentMenu(menu || currentMenu);

		if (menu !== 'ai') return;

		const handleClick = (e) => {
			if (userRef.current) return;

			const interactable = e.target.closest('input, select, textarea, button[type="submit"]');
			if (!interactable) return;

			e.preventDefault();
			e.stopPropagation();
			setShowAPIModal(true);
		};

		const content = document.getElementById('easycommerce-settings-content');
		if (content) {
			content.addEventListener('click', handleClick, true);
		}

		return () => {
			if (content) {
				content.removeEventListener('click', handleClick, true);
			}
		};
	}, []);


	/**
	 * Filters the breadcrumbs.
	 *
	 * @since 1.0.0
	 * @param {Array} breadcrumbs The breadcrumbs array.
	 */
	const breadcrumbs = applyFilters(
		'easycommerce.settings.breadcrumbs',
		[__( 'Settings', 'easycommerce' ), getMenuLabel( currentMenu )]
	);

	return (
		<>
			<Header
				breadcrumb={breadcrumbs}
				showAPIModal={showAPIModal}
				setShowAPIModal={setShowAPIModal}
				user={user}
				setUser={setUser}
			/>
			<RootToast />
		</>
	);
};

const container = document.getElementById('easycommerce-settings-header');

const root = createRoot(container);
root.render(<App />);
