import React, { useEffect, useState } from 'react';
import Cookies from 'universal-cookie';

//components
import Feedback from './components/Header/Feedback';
import AiSearch from './components/AISearchButton/AiSearch';
// Modals
import APIScreen from './components/APIScreen';
import APIVarification from './components/APIScreen/elements/APIVarification';
import APICreateForm from './components/APIScreen/elements/APICreateForm';

const BreadcrumbIcon = `${EASYCOMMERCE.assets}admin/img/icons/breadcump.png`;
const rightDivider = `${EASYCOMMERCE.assets}admin/img/icons/header-right.png`;
const logo = `${EASYCOMMERCE.assets}common/img/logo.png`;
const BfcmDiscountImg = `${EASYCOMMERCE.assets}admin/img/banner-sale/discount.gif`;
const userIcon = `${EASYCOMMERCE.assets}admin/img/icons/header-default-user-icon.png`;

const profileIcon = `${EASYCOMMERCE.assets}admin/img/icons/profile-icon.png`;
const disconnectIcon = `${EASYCOMMERCE.assets}admin/img/icons/disconnect-icon.png`;

const proIcon = (
	<svg
		width="23"
		height="24"
		viewBox="0 0 23 24"
		fill="none"
		xmlns="http://www.w3.org/2000/svg"
	>
		<path
			d="M21.5505 4.41774C21.2209 4.25711 20.8466 4.21288 20.4886 4.29227C20.1306 4.37165 19.81 4.56996 19.5792 4.85487L15.7822 9.55186L12.7822 1.08784C12.6687 0.769501 12.4595 0.494067 12.1833 0.299307C11.9071 0.104548 11.5774 0 11.2394 0C10.9015 0 10.5718 0.104548 10.2956 0.299307C10.0193 0.494067 9.81014 0.769501 9.69663 1.08784L6.7053 9.50472L2.89971 4.85915C2.67024 4.57259 2.35068 4.37197 1.99288 4.28983C1.63507 4.2077 1.26001 4.24887 0.928537 4.40666C0.597067 4.56446 0.328636 4.82962 0.166794 5.15914C0.00495218 5.48865 -0.0408063 5.86318 0.0369423 6.22196L2.574 18.6159H19.9049L22.4419 6.22196C22.5208 5.86468 22.4755 5.49119 22.3134 5.16316C22.1513 4.83513 21.8822 4.57218 21.5505 4.41774Z"
			fill="#121216"
		/>
		<path
			d="M4.47986 20.3262H2.76563V21.3719C2.76517 22.0661 3.04017 22.7322 3.53028 23.2239C4.0204 23.7156 4.68557 23.9928 5.37983 23.9946H17.088C17.7823 23.9928 18.4475 23.7156 18.9376 23.2239C19.4277 22.7322 19.7027 22.0661 19.7022 21.3719V20.3262H4.47986Z"
			fill="#121216"
		/>
	</svg>
);

const Header = ({
	breadcrumb,
	showAPIModal,
	setShowAPIModal,
	user,
	setUser,
	children,
}) => {
	const [isLoading, setIsLoading] = useState(true);
	const [currentAPIModalTab, setCurrentAPIModalTab] = useState('');

	const handleModalClose = () => {
		setShowAPIModal(false);
		setCurrentAPIModalTab('');
	};

	const handleDisconnect = () => {
		easycommerce_modal(true);

		fetch(`${EASYCOMMERCE.rest_base}/connectivity/disconnect`, {
			method: 'DELETE',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success && !data.data?.connected) {
					const cookies = new Cookies(null, { path: '/' });
					cookies.remove('easycommerce-user', { path: '/' });

					window.location.reload();
				}

				easycommerce_modal(false);
			});
	};

	useEffect(() => {
		const cookies = new Cookies(null, { path: '/' });

		fetch(`${EASYCOMMERCE.rest_base}/connectivity/check`, {
			method: 'GET',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success && data.data.connected) {
					setUser(data.data.user);
					cookies.set('easycommerce-user', JSON.stringify(data.data.user), {
						path: '/',
						maxAge: 86400 * 30,
					});
				} else {
					cookies.remove('easycommerce-user', { path: '/' });
				}
				setIsLoading(false);
			})
			.catch(() => {
				setIsLoading(false);
			});
	}, []);

	return (
		<>
			{showAPIModal && currentAPIModalTab === '' ? (
				<APIScreen
					onClose={handleModalClose}
					switchVariationModalTab={() =>
						setCurrentAPIModalTab('apiVarification')
					}
					switchCreateModalTab={() => setCurrentAPIModalTab('apiCreate')}
				/>
			) : showAPIModal && currentAPIModalTab === 'apiVarification' ? (
				<APIVarification
					onClose={handleModalClose}
					switchModalTab={() => setCurrentAPIModalTab('apiCreate')}
					setUserAfterVarification={(userData) => setUser(userData)}
				/>
			) : showAPIModal && currentAPIModalTab === 'apiCreate' ? (
				<APICreateForm
					onClose={handleModalClose}
					switchModalTab={() => setCurrentAPIModalTab('apiVarification')}
				/>
			) : null}

			<div className="bg-[#FFFFFF] flex justify-start items-center h-[80px]">
				<div className="w-[240px] border-r border-b border-[#EEF4FF] h-full flex justify-center items-center">
					<img
						src={logo}
						alt="breadcrumb-icon"
						className="pointer-events-none w-[185px]"
					/>
				</div>

				<div className="px-[24px] flex justify-between items-center grow">
					<div className="flex items-center gap-8">
						<div className="flex items-center gap-3 flex-wrap">
							{Array.isArray(breadcrumb) &&
								breadcrumb.length > 0 &&
								breadcrumb.map((item, index) => {
									const isFirst = index === 0;
									const isLast = index === breadcrumb.length - 1;
									let slug = `#/${item.toLowerCase().replace(/\s+/g, '-')}`;

									if (isFirst) {
										slug = '#';
									}
									if (breadcrumb[index - 1] === 'Reports' && item === 'Products') {
										slug = '#/reports/products';
									}

									return (
										<div key={index} className="flex items-center gap-3">
											{isFirst && (
												<img
													src={BreadcrumbIcon}
													alt="breadcump-icon"
													className="w-4 h-4 pointer-events-none"
												/>
											)}

											{isLast ? (
												<h3 className="text-sm leading-5 font-medium text-ec-primary">
													{item.length > 80 ? item.slice(0, 80) + '...' : item}
												</h3>
											) : (
												<button
													className="text-sm leading-5 font-medium text-ec-placeholder hover:text-ec-primary"
													onClick={() => {
														window.location.hash = slug;
													}}
												>
													{item}
												</button>
											)}

											{!isLast && (
												<svg
													width="11"
													height="11"
													viewBox="0 0 11 11"
													fill="none"
													xmlns="http://www.w3.org/2000/svg"
												>
													<path
														d="M6.04297 2.04492C5.82292 1.77409 5.83138 1.49479 6.06836 1.20703C6.38997 0.986979 6.67773 1.00391 6.93164 1.25781L10.3594 5.11719C10.5625 5.38802 10.5625 5.65885 10.3594 5.92969L6.93164 9.78906C6.66081 10.043 6.37305 10.0599 6.06836 9.83984C5.94987 9.70443 5.89062 9.55208 5.89062 9.38281C5.89062 9.23047 5.94141 9.09505 6.04297 8.97656L9.14062 5.52344L6.04297 2.04492ZM5.53516 5.92969L2.05664 9.78906C1.78581 10.043 1.49805 10.0599 1.19336 9.83984C1.07487 9.70443 1.01562 9.55208 1.01562 9.38281C1.01562 9.23047 1.06641 9.09505 1.16797 8.97656L4.26562 5.52344L1.16797 2.04492C0.947917 1.77409 0.964844 1.49479 1.21875 1.20703C1.52344 0.986979 1.8112 1.00391 2.08203 1.25781L5.53516 5.11719C5.73828 5.38802 5.73828 5.65885 5.53516 5.92969Z"
														fill={isLast ? '#7351FD' : '#737791'}
													/>
												</svg>
											)}
										</div>
									);
								})}
						</div>
					</div>
					<div className="relative flex justify-end items-center gap-4">
						<AiSearch
							user={user}
							setShowAPIModal={setShowAPIModal}
						/>
						{!EASYCOMMERCE.pro.activated && !EASYCOMMERCE.pro.licensed ? (
							<>
								<div>
									<a
										href={`${EASYCOMMERCE.admin_url}?page=easycommerce#/get-pro`}
										className="flex items-center cursor-pointer p-2 font-inter font-medium text-sm leading-5 text-ec-primary focus:shadow-none focus:text-ec-primary"
									>
										Get Pro
									</a>
								</div>
								<span>|</span>
							</>
						) : EASYCOMMERCE.pro.activated && !EASYCOMMERCE.pro.licensed ? (
							<>
								<div>
									<a
										href="admin.php?page=easycommerce#/pro"
										className="flex items-center cursor-pointer p-2 font-inter font-medium text-sm leading-5 text-ec-primary focus:shadow-none focus:text-ec-primary"
									>
										Activate License
									</a>
								</div>
								<span>|</span>
							</>
						) : null}
						<Feedback />|
						<div>
							<a
								href="https://support.easycommerce.dev"
								target="_blank"
								className="flex items-center cursor-pointer p-2 font-inter font-medium text-sm leading-5 ec-primary hover:text-ec-primary focus:shadow-none focus:text-ec-primary"
							>
								Support
							</a>
						</div>
						{!isLoading && (
							<div className="relative group">
								<button
									className="w-12 h-12 flex justify-center items-center border border-ec-border rounded-md"
									onClick={
										!user ? () => setShowAPIModal(!showAPIModal) : () => {}
									}
								>
									<img
										src={!user ? userIcon : (user?.photo || userIcon)}
										alt="user-icon"
										className="pointer-events-none rounded"
										style={{
											width: user ? '38px' : '24px',
											height: user ? '38px' : '28px',
										}}
									/>
								</button>

								{user && (
									<div className="absolute pt-3 right-0 z-10 group-hover:block hidden">
										<div
											className="w-[220px] flex flex-col gap-3 bg-white p-4 border border-ec-border rounded-xl"
											style={{
												boxShadow: '0px 30px 34px -2px #0000001F',
											}}
										>
											<a
												href="https://my.easycommerce.dev/"
												className="w-full flex justify-start items-center gap-3 px-4 py-3
                                                rounded-md hover:bg-[#F8F8F8] focus:shadow-none focus:outline-none active:shadow-none"
											>
												<img src={profileIcon} className="w-[14px] h-4" />
												<span className="text-base leading-[26px] font-inter font-normal text-ec-body">
													Profile
												</span>
											</a>

											<button
												className="w-full flex justify-start items-center gap-3 px-4 py-3
                                                rounded-md hover:bg-[#F8F8F8]"
												onClick={() => handleDisconnect()}
											>
												<img src={disconnectIcon} className="w-5 h-4" />
												<span className="text-base leading-[26px] font-inter font-normal text-ec-body">
													Disconnect
												</span>
											</button>
										</div>
									</div>
								)}
							</div>
						)}
					</div>
				</div>
			</div>
		</>
	);
};

export default Header;