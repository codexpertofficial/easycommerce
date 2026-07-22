import { useState, useEffect } from 'react';

const SideBar = () => {
	const [currentPage, setCurrentPage] = useState('');
	const [openMenus, setOpenMenus] = useState({});

	const normalizeSlug = (slug) => {
		// Bare trailing '#' (no route) = empty hash = dashboard root
		if (slug.endsWith('#')) {
			slug = slug.slice(0, -1);
		}

		if (slug.startsWith('easycommerce#/products/edit')) {
			return 'easycommerce#/products';
		}
		if (slug.startsWith('easycommerce#/coupons/edit')) {
			return 'easycommerce#/coupons';
		}
		
		const dynamicPatterns = [
		{ regex: /^easycommerce-store#\/orders\/\d+$/, to: 'easycommerce#/orders' },
		{ regex: /^easycommerce-store#\/customers\/\d+$/, to: 'easycommerce#/customers' },
		{ regex: /^easycommerce#\/reports\/products\/\d+$/, to: 'easycommerce#/reports/products' },
		];

		for (const { regex, to } of dynamicPatterns) {
			if (regex.test(slug)) return to;
		}

		return slug;
	};

	useEffect(() => {
		const handleLocationChange = () => {
			const url = new URL(window.location.href);
			const page = url.searchParams.get('page') || '';
			const fullPage = page + window.location.hash;
			const normalizedSlug = normalizeSlug(fullPage);

			setCurrentPage(normalizedSlug);

			const menus = EASYCOMMERCE.admin.menus || [];
			const storeMenu = menus.find(menu => menu.slug === 'easycommerce');

			let toOpen = {};

			const openMatchingMenus = (items = [], parents = []) => {
				for (const item of items) {
					if (item.slug === normalizedSlug) {
						parents.forEach(p => toOpen[p] = true);
						toOpen[item.slug] = true;
						return true;
					}
					if (item.submenus && openMatchingMenus(item.submenus, [...parents, item.slug])) {
						return true;
					}
				}
				return false;
			};

			if (storeMenu?.submenus) {
				openMatchingMenus(storeMenu.submenus);
			}


			setOpenMenus(toOpen);
		};

		handleLocationChange();
		window.addEventListener('hashchange', handleLocationChange);
		return () => window.removeEventListener('hashchange', handleLocationChange);
	}, []);

	const toggleMenu = slug => {
		setOpenMenus(prev => ({ ...prev, [slug]: !prev[slug] }));
	};

	const menus = EASYCOMMERCE.admin.menus || [];
	const storeMenu = menus.find(menu => menu.slug === 'easycommerce');
	if (!storeMenu) return null;
	const hasActiveChild = (items) => {
		return items.some(child =>
			child.slug === currentPage || 
			(child.submenus && hasActiveChild(child.submenus))
		);
	};

	const renderMenuItems = (items, level = 0) => {
		return items.map((item, index) => {
			const hasSubmenus 	= Array.isArray(item.submenus) && item.submenus.length > 0;
			const isActive 		= item.slug === currentPage || (item.submenus && hasActiveChild(item.submenus));
			const isOpen 		= openMenus[item.slug] || false;
			const menuUrl 		= `admin.php?page=${item.slug}${level === 0 && index === 0 ? '#' : ''}`;
	
			return (
				<li key={`${item.slug}-${index}`}>
					<a
						href={menuUrl}
						onClick={e => {
							if (hasSubmenus) {
								e.preventDefault();
								toggleMenu(item.slug);
							}
						}}
						className={`hover:bg-[var(--color-ec-active)] hover:text-inherit hover:rounded-[8px] flex items-center text-sm p-3 my-2 gap-2 text-ec-title focus:ring-0 ${
							hasSubmenus ? 'submenu-toggle' : ''
						} ${isActive ? 'easycommerce-active-menu !text-ec-primary focus:text-ec-primary' : ''}`}
					>
						{item.icon && (
							<img
								src={isActive && item.hover_icon ? item.hover_icon : item.icon}
								alt=""
								className="menu-icon"
								style={{ width: 20, height: 20 }}
							/>
						)}
						{item.menu_title?.replace(/&nbsp;/g, '\u00A0')}
						{hasSubmenus && (
							<img
								src={`${EASYCOMMERCE.assets}admin/img/menu/up.png`}
								className={`ml-auto arrow-icon transition-transform duration-300 ${
									isOpen ? '' : 'rotate-180'
								}`}
								style={{ width: 11 }}
							/>
						)}
					</a>
					{hasSubmenus && isOpen && (
						<ul className="submenu-items border-l border-[#ECE6FF] ml-[16px] pl-3">
							{renderMenuItems(item.submenus, level + 1)}
						</ul>
					)}
				</li>
			);
		});
	};

	return (
		<div className="py-2 px-4 w-[240px] bg-white items-center gap-4 font-inter min-h-screen">
			<ul className="menu-title">{renderMenuItems(storeMenu.submenus)}</ul>
		</div>
	);
};

export default SideBar;
