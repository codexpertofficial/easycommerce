import React, { useEffect, useState } from 'react';
import Cookies from 'universal-cookie';

// Modals
import APIScreen from './APIScreen';
import APIVarification from './APIScreen/elements/APIVarification';
import APICreateForm from './APIScreen/elements/APICreateForm';
import Feedback from './Header/Feedback';

const BreadcumpIcon = `${EASYCOMMERCE.assets}admin/img/icons/breadcump.png`;
const rightDivider = `${EASYCOMMERCE.assets}admin/img/icons/header-right.png`;
const userIcon = `${EASYCOMMERCE.assets}admin/img/icons/header-default-user-icon.png`;
const logo = `${EASYCOMMERCE.assets}common/img/logo.png`;

const profileIcon = `${EASYCOMMERCE.assets}admin/img/icons/profile-icon.png`;
const disconnectIcon = `${EASYCOMMERCE.assets}admin/img/icons/disconnect-icon.png`;

const CommonHeader = ({
	parentSlug,
	parentLavel = 'EasyCommerce',
	breadcumpSlug,
	isSettings = false,
}) => {
	const [user, setUser] = useState(null);
	const [isLoading, setIsLoading] = useState(true);
	const [showAPIModal, setShowAPIModal] = useState(false);
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
					isSettings={isSettings}
					switchModalTab={() => setCurrentAPIModalTab('apiVarification')}
				/>
			) : null}

			<div className="relative bg-[#FFFFFF] pl-[27px] pr-6 flex justify-between items-center h-[80px]">
				<div className="flex items-center gap-[52px]">
					<img
						src={logo}
						alt="breadcump-icon"
						className="pointer-events-none w-[185px]"
					/>

					<div className="flex items-center gap-3">
						<img
							src={BreadcumpIcon}
							alt="breadcump-icon"
							className="w-4 h-4 pointer-events-none"
						/>

						<h3>
							<a
								className="text-sm leading-5 font-medium text-ec-placeholder
								hover:text-ec-primary focus:text-ec-primary active:text-ec-primary
								focus:shadow-none active:shadow-none focus:outline-none"
								href={`?page=${parentSlug.toLowerCase()}`}
							>
								{parentLavel}
							</a>
						</h3>
						{isSettings && (
							<>
								<img
									src={rightDivider}
									alt="divider-icon"
									className="w-3 h-3 pointer-events-none"
								/>

								<h3>
									<a
										className="text-sm leading-5 font-medium text-ec-placeholder hover:text-ec-primary"
										href="?page=easycommerce-settings"
									>
										Settings
									</a>
								</h3>
							</>
						)}
						{/* <img
                            src={rightDivider}
                            alt="divider-icon"
                            className="w-3 h-3 pointer-events-none"
                        /> */}
						<svg
							width="11"
							height="11"
							viewBox="0 0 11 11"
							fill="none"
							xmlns="http://www.w3.org/2000/svg"
						>
							<path
								d="M6.04297 2.04492C5.82292 1.77409 5.83138 1.49479 6.06836 1.20703C6.38997 0.986979 6.67773 1.00391 6.93164 1.25781L10.3594 5.11719C10.5625 5.38802 10.5625 5.65885 10.3594 5.92969L6.93164 9.78906C6.66081 10.043 6.37305 10.0599 6.06836 9.83984C5.94987 9.70443 5.89062 9.55208 5.89062 9.38281C5.89062 9.23047 5.94141 9.09505 6.04297 8.97656L9.14062 5.52344L6.04297 2.04492ZM5.53516 5.92969L2.05664 9.78906C1.78581 10.043 1.49805 10.0599 1.19336 9.83984C1.07487 9.70443 1.01562 9.55208 1.01562 9.38281C1.01562 9.23047 1.06641 9.09505 1.16797 8.97656L4.26562 5.52344L1.16797 2.04492C0.947917 1.77409 0.964844 1.49479 1.21875 1.20703C1.52344 0.986979 1.8112 1.00391 2.08203 1.25781L5.53516 5.11719C5.73828 5.38802 5.73828 5.65885 5.53516 5.92969Z"
								fill="#737791"
							/>
						</svg>

						<h3 className="text-sm leading-5 font-medium text-ec-primary">
							{breadcumpSlug}
						</h3>
					</div>
				</div>
				<div className="relative flex justify-end items-center gap-4">
                    {(!EASYCOMMERCE.pro.activated && !EASYCOMMERCE.pro.licensed) ? (
                        <>
                            <div>
                                <a
                                    href={`${EASYCOMMERCE.admin_url}?page=easycommerce`}
                                    className="flex items-center cursor-pointer p-2 font-inter font-medium text-sm leading-5 text-ec-primary focus:shadow-none focus:text-ec-primary"
                                >
                                    Get Pro
                                </a>
                            </div>
                            <span>|</span>
                        </>
                    ) : (EASYCOMMERCE.pro.activated && !EASYCOMMERCE.pro.licensed) ? (
                        <>
                            <div>
                                <a
                                    href="admin.php?page=easycommerce#/get-pro"
                                    className="flex items-center cursor-pointer p-2 font-inter font-medium text-sm leading-5 text-ec-primary focus:shadow-none focus:text-ec-primary"
                                >
                                    Activate License
                                </a>
                            </div>
                            <span>|</span>
                        </>
                    ) : null}
					<Feedback />
                    |
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
		</>
	);
};

export default CommonHeader;
