import React, { useEffect, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { applyFilters } from '@wordpress/hooks';
import Header from '../../common/Header';
import './settings-menu.css';

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
		['Settings', currentMenu].map((item) =>
			item.split('-').map(
					(word) => word.charAt(0).toUpperCase() + word.slice(1)
				).join(' ')
		)
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
		</>
	);
};

const container = document.getElementById('easycommerce-settings-header');

const root = createRoot(container);
root.render(<App />);
