import React, { useState, useEffect } from 'react';
import { Bounce, toast } from 'react-toastify';

// components
import LicenseField from './LicenseField';
import LicenseInfo from './LicenseInfo';

const addonIcon = (
	<svg
		width="23"
		height="23"
		viewBox="0 0 23 23"
		fill="none"
		xmlns="http://www.w3.org/2000/svg"
	>
		<path
			d="M4.25062 19.951C5.12739 20.7219 6.26512 21.1295 7.43197 21.0908C8.59882 21.052 9.70698 20.5698 10.5306 19.7423L13.223 17.0499L11.8768 15.7037L13.8961 13.6844L11.9913 11.7796L9.97197 13.7989L8.51135 12.3382L10.5306 10.3189L8.62577 8.41406L6.60647 10.4334L5.26027 9.08716L2.56788 11.7796C1.73998 12.6028 1.25714 13.7107 1.21774 14.8776C1.17835 16.0444 1.58536 17.1824 2.35585 18.0596L0 20.4154L1.89478 22.3203L4.25062 19.951ZM4.47275 13.6844L5.26027 12.8969L6.60647 14.2431L8.0671 15.7037L9.4133 17.0499L8.62577 17.8375C8.2686 18.1942 7.78441 18.3946 7.27957 18.3946C6.77474 18.3946 6.29055 18.1942 5.93337 17.8375L4.47275 16.3768C4.11597 16.0197 3.91557 15.5355 3.91557 15.0306C3.91557 14.5258 4.11597 14.0416 4.47275 13.6844Z"
			fill="#7351FD"
		/>
		<path
			d="M18.0427 2.36931C17.166 1.59839 16.0282 1.19078 14.8614 1.22955C13.6945 1.26832 12.5864 1.75055 11.7627 2.57797L9.07031 5.27037L17.0331 13.2331L19.7255 10.5407C20.5534 9.71752 21.0362 8.60959 21.0756 7.44274C21.115 6.27588 20.708 5.13791 19.9375 4.26072L22.2934 1.90487L20.3986 0L18.0427 2.36931ZM17.8206 8.63587L17.0331 9.4234L12.8801 5.27037L13.6676 4.48284C14.0248 4.12607 14.5089 3.92567 15.0138 3.92567C15.5186 3.92567 16.0028 4.12607 16.36 4.48284L16.498 4.62083L17.824 5.94347C18.1807 6.30064 18.3812 6.78483 18.3812 7.28967C18.3812 7.79451 18.1807 8.2787 17.824 8.63587H17.8206Z"
			fill="#7351FD"
		/>
		<path
			d="M2.89552 3.58791H6.26102V0.895508H2.89552C2.18146 0.895508 1.49663 1.17917 0.99171 1.68409C0.486788 2.18902 0.203125 2.87384 0.203125 3.58791V6.95341H2.89552V3.58791Z"
			fill="#7351FD"
		/>
		<path
			d="M21.0813 19.0686V15.7031H18.3889V19.0686H15.0234V21.761H18.3889C19.103 21.761 19.7878 21.4774 20.2927 20.9724C20.7977 20.4675 21.0813 19.7827 21.0813 19.0686Z"
			fill="#7351FD"
		/>
	</svg>
);

const Activated = () => {
	const [errorMsg, setErrorMsg] = useState('');
	const [loading, setLoading] = useState([]);
	const [addons, setAddons] = useState(EASYCOMMERCE.pro.addons);

	const showToast = (type, message) => {
		toast[type](message, {
			position: 'top-right',
			style: {
				margin: '30px 0 0 0',
				fontSize: '16px',
				fontWeight: '500',
				lineHeight: '26px',
				color: '#fff',
			},
			autoClose: 2000,
			hideProgressBar: false,
			closeOnClick: true,
			pauseOnHover: true,
			draggable: false,
			progress: undefined,
			theme: 'colored',
			transition: Bounce,
		});
	};

	const addonActionHandler = (addon) => {
		const isActive = addon.is_active;

		if (isActive) {
			deactivateAddon(addon.slug);
		} else {
			activateAddon(addon.slug);
		}
	};

	const activateAddon = async (slug) => {
		setLoading((prev) => [...prev, slug]);
		try {
			const res = await fetch(`${EASYCOMMERCE.rest_base}/addons`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': EASYCOMMERCE.nonce,
				},
				body: JSON.stringify({
					action: 'activate',
					addon: slug,
				}),
			});

			const data = await res.json();
			if (data.data.status) {
				showToast('success', 'Addon activated successfully.');
				setAddons((prev) =>
					prev.map((addon) =>
						addon.slug === slug ? { ...addon, is_active: true } : addon
					)
				);
			} else {
				showToast('error', 'Error activating addon.');
			}
		} catch (error) {
			showToast('error', 'Error activating addon.');
			console.error('Error activating addon:', error);
		} finally {
			setLoading((prev) => prev.filter((item) => item !== slug));
		}
	};

	const deactivateAddon = async (slug) => {
		setLoading((prev) => [...prev, slug]);
		try {
			const res = await fetch(`${EASYCOMMERCE.rest_base}/addons`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': EASYCOMMERCE.nonce,
				},
				body: JSON.stringify({
					action: 'deactivate',
					addon: slug,
				}),
			});

			const data = await res.json();
			if (data.data.status) {
				showToast('success', 'Addon deactivated.');
				setAddons((prev) =>
					prev.map((addon) =>
						addon.slug === slug ? { ...addon, is_active: false } : addon
					)
				);
			} else {
				showToast('error', 'Error deactivating addon.');
			}
		} catch (error) {
			showToast('error', 'Error deactivating addon.');
			console.error('Error deactivating addon:', error);
		} finally {
			setLoading((prev) => prev.filter((item) => item !== slug));
		}
	};

	return (
		<div className={EASYCOMMERCE.pro.licensed ? 'py-[100px]' : 'py-10'}>
			<div>
				<h1 className="text-5xl text-ec-title font-medium text-center">
					Welcome to <span className="text-ec-primary">EasyCommerce PRO</span>
				</h1>
				<p className="text-center mt-3 text-base text-ec-body">
					Manage all your premium features from a single dashboard.
				</p>
			</div>

			<div className="mt-[60px] grid grid-cols-2 gap-8">
				{EASYCOMMERCE.pro.licensed ? (
					<LicenseInfo />
				) : (
					<LicenseField errorMsg={errorMsg} setErrorMsg={setErrorMsg} />
				)}

				<div className="border border-ec-table-stock rounded-xl p-8">
					<h2 className="text-ec-title text-2xl font-medium mb-8">
						Pro Features in Use
					</h2>

					<div className="flex flex-col gap-4 max-h-[400px] overflow-y-auto pr-3">
						{addons.map((addon, index) => (
							<button
								key={index}
								className={`border border-ec-table-stock ${
									EASYCOMMERCE.pro.licensed
										? 'hover:border-ec-primary'
										: 'cursor-not-allowed opacity-60'
								} rounded-lg p-2.5 duration-300 w-full flex items-center justify-between`}
								onClick={() =>
									EASYCOMMERCE.pro.licensed
										? addonActionHandler(addon)
										: setErrorMsg(
												'You need to activate your license to use this feature.'
										  )
								}
							>
								<div className="flex items-center gap-3">
									<div className="w-10 h-10 rounded-md bg-ec-primary/5 flex items-center justify-center">
										{addonIcon}
									</div>

									<h3 className="text-ec-body text-base">{addon.name}</h3>
								</div>

								{loading.includes(addon.slug) ? (
									<img className='w-6 mx-4' src={`${EASYCOMMERCE.assets}admin/img/loader.gif`} alt="loading" />
								) : (
									<div
										className={`w-[55px] h-[25px] relative rounded-full duration-300 ${
											addon.is_active
												? 'justify-end bg-ec-primary'
												: 'justify-start bg-[#7F7F98]'
										}`}
									>
										<div
											className={`w-[19px] h-[19px] rounded-full bg-white absolute top-[3px] duration-300 ${
												addon.is_active ? 'left-[33px]' : 'left-[3px]'
											}`}
										></div>
									</div>
								)}
							</button>
						))}
					</div>
				</div>
			</div>
		</div>
	);
};

export default Activated;
