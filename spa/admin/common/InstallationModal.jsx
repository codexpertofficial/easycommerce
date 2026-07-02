import React from 'react';
import { __ } from '@wordpress/i18n';
import { toast } from 'react-toastify';

const InstallationModal = ({ addon, setShowPopup }) => {
	const [loading, setLoading] = React.useState(false);
	const closeButton = (
		<svg
			width="16"
			height="16"
			viewBox="0 0 24 24"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
			class="w-4 h-4"
		>
			<path
				fill-rule="evenodd"
				clip-rule="evenodd"
				d="M7.26042 7.26042C7.60764 6.91319 8.17015 6.91319 8.51731 7.26042L12 10.7431L15.4827 7.26042C15.8299 6.91319 16.3924 6.91319 16.7396 7.26042C17.0868 7.60764 17.0868 8.17015 16.7396 8.51731L13.2569 12L16.7396 15.4827C17.0868 15.8299 17.0868 16.3924 16.7396 16.7396C16.3924 17.0868 15.8299 17.0868 15.4827 16.7396L12 13.2569L8.51731 16.7396C8.17009 17.0868 7.60759 17.0868 7.26042 16.7396C6.91325 16.3924 6.91319 15.8299 7.26042 15.4827L10.7431 12L7.26042 8.51731C6.91319 8.17009 6.91319 7.60759 7.26042 7.26042Z"
				class="fill-[#3C3C42] group-hover:fill-white transition-colors duration-300"
			/>
		</svg>
	);
	const proImg = (
		<svg
			width="30"
			height="32"
			viewBox="0 0 30 32"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
		>
			<path
				d="M28.7358 5.89067C28.2963 5.67649 27.7971 5.61752 27.3198 5.72337C26.8424 5.82922 26.415 6.09365 26.1072 6.47355L21.0442 12.7366L17.044 1.45054C16.8927 1.02606 16.6137 0.658795 16.2454 0.3991C15.8771 0.139405 15.4375 0 14.9868 0C14.5362 0 14.0966 0.139405 13.7282 0.3991C13.3599 0.658795 13.081 1.02606 12.9296 1.45054L8.94095 12.6737L3.86651 6.47926C3.56054 6.09716 3.13443 5.82965 2.65733 5.72013C2.18023 5.6106 1.68011 5.6655 1.23812 5.87591C0.796138 6.08631 0.438209 6.43989 0.222406 6.87927C0.00660331 7.31864 -0.0544117 7.81805 0.0492595 8.29646L3.43221 24.8227H26.5415L29.9244 8.29646C30.0296 7.82005 29.9691 7.32203 29.753 6.88463C29.5369 6.44724 29.1781 6.09662 28.7358 5.89067Z"
				fill="#121216"
			/>
			<path
				d="M5.98109 27.1025H3.69531V28.4969C3.6947 29.4226 4.0614 30.3108 4.71492 30.9664C5.36844 31.6221 6.2554 31.9917 7.18113 31.9941H22.793C23.7187 31.9917 24.6057 31.6221 25.2592 30.9664C25.9127 30.3108 26.2794 29.4226 26.2788 28.4969V27.1025H5.98109Z"
				fill="#121216"
			/>
		</svg>
	);

	const activateAddon = async (slug) => {
		setLoading(true);
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
				toast.success('Addon activated successfully.')
				setTimeout(() => {
					window.location.reload();
				}, 1500);
			} else {
				toast.error('Error activating addon.');
			}
		} catch (error) {
			toast.error('Error activating addon.');
			console.error('Error activating addon:', error);
		} finally {
			setLoading(false);
			setShowPopup(false);
		}
	};

	return (
		<div className="fixed inset-0 flex items-center justify-center z-[9999] bg-[#0000003B] backdrop-blur-sm">
			<div className="bg-white p-6 shadow-xl text-center relative w-[468px] py-[30px] rounded-xl">
				<button
					className="absolute top-[-18px] right-[-23px] group w-6 h-6 rounded-full bg-white hover:bg-[#fa4109] transition-colors duration-200 flex items-center justify-center"
					onClick={() => setShowPopup(false)}
				>
					{closeButton}
				</button>
				<div className="flex flex-col items-center">
					<div className="pro-img flex items-center justify-center w-[70px] h-[70px] mx-auto">
						{proImg}
					</div>
					<h2 className="mt-6 text-[#121216] text-2xl font-medium">
						{__(`Install A New Addon?`, 'easycommerce')}
					</h2>
					<p className="text-[#3C3C42] text-base font-normal text-center px-3 py-2">
						{__(
							`This will install ${addon.name}. Are you sure to proceed?`
						)}
					</p>
					<div className="mt-4 flex items-center w-full gap-2 justify-center">
						{!loading && <button
							onClick={() => setShowPopup(false)}
							className="w-full h-12 flex flex-1 justify-center items-center font-medium font-inter text-[#3C3C42] hover:text-white text-base leading-[26px] bg-white hover:bg-[#3C3C42] rounded-lg border  border-[#3C3C42] transition-all ease-in-out duration-300"
						>
							{__('Cancel', 'easycommerce')}
						</button>}
						<button
							onClick={(e) => {
								e.preventDefault();
								activateAddon(addon.slug);
							}}
							className="w-full h-12 flex flex-1 justify-center items-center font-medium font-inter text-base leading-[26px] text-white bg-ec-primary rounded-lg hover:bg-ec-secondary transition-all ease-in-out duration-300"
						>
							{loading ? (
								<img src={`${EASYCOMMERCE.assets}admin/img/loading.gif`} className='w-9' alt="loading" />
							) : __('Install & Activate', 'easycommerce')}
						</button>
					</div>
				</div>
			</div>
		</div>
	);
};

export default InstallationModal;
