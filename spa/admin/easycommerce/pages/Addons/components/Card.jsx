import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Bounce, toast } from 'react-toastify';
import { applyFilters } from '@wordpress/hooks';
import StarRating from './StarRating';
import ProModal from '../../../../common/ProModal';

const MANUAL_ADDONS_URL = 'https://easycommerce.dev/addons';

const Card = ({ addon }) => {
	/**
	 * Filters the addon data for the card display.
	 *
	 * @since 1.0.0
	 * @param {Object} addon The addon object.
	 */
	const filteredAddon = applyFilters('easycommerce.addons.card', addon);

	const [active, setActive] = useState(filteredAddon.status === 'active');
	const [showPopup, setShowPopup] = useState(false);
	const [loading, setLoading] = useState(false);

	const showToast = (type, message, onClose = () => {}) => {
		toast[type](
		    <div dangerouslySetInnerHTML={{ __html: message }} />,
		    {
			position: 'top-right',
			style: {
				margin: '30px 0 0 0',
				fontSize: '16px',
				fontWeight: '500',
				lineHeight: '26px',
				color: '#fff',
			},
			autoClose: 1000,
			onClose: onClose,
			hideProgressBar: false,
			closeOnClick: true,
			pauseOnHover: true,
			draggable: false,
			progress: undefined,
			theme: 'colored',
			transition: Bounce,
		});
	};

 	const handleAddonAction = async (addon) => {
 		if (!filteredAddon.slug) return;

 		if (!filteredAddon.is_free && !filteredAddon.accessible && !EASYCOMMERCE.pro.licensed) {
 			setShowPopup(true);
 			return;
 		}

 		setLoading(true);

 		try {
 			const res = await fetch(`${EASYCOMMERCE.rest_base}/addons`, {
 				method: 'POST',
 				headers: {
 					'Content-Type': 'application/json',
 					'X-WP-Nonce': EASYCOMMERCE.nonce,
 				},
 				body: JSON.stringify({
 					action: active ? 'deactivate' : 'activate',
 					addon: filteredAddon.slug,
 				}),
 			});

			const data = await res.json();
			setLoading(false);

			if (data.success && data.data.status) {
				showToast('success', data.data.message);
				setActive(!active);
			} else {
				showToast(
					'error',
					data.data.message
				);
			}
		} catch (error) {
			setLoading(false);
			showToast(
				'error',
				__(
					`Addon not installed. <a href="${MANUAL_ADDONS_URL}" target="_blank" rel="noopener noreferrer" style="color:#fff;text-decoration:underline;">Install it manually</a>.`,
					'easycommerce'
				)
			);
		}
	};

	return (
		<div className="flex flex-col border border-ec-border rounded-lg hover:shadow-[2px_22px_54.6px_0px_#EFEBFF]">
			<div className="relative">
				<a href={filteredAddon.url} target="_blank">
					<img
						className="w-full h-auto rounded-t-lg"
						src={filteredAddon.thumbnail}
						alt=""
					/>
				</a>

				{filteredAddon.is_free && <div className="px-4 py-1 text-xs font-normal text-black bg-[#FFC400] rounded absolute top-2 right-2">
					{__('Free', 'easycommerce')}
				</div>}
			</div>

			<div className="p-4 h-full flex flex-col justify-between">
				<div>
					<a className="block mb-2" target="_blank" href={filteredAddon.url}>
						<h2 className="text-ec-title text-xl leading-[32px] font-medium font-inter">
							{filteredAddon.name.slice(0, 30)}
						</h2>
					</a>
					<p className="text-ec-body font-inter text-sm leading-[24px] mb-3 line-clamp-2">
						{filteredAddon.desc}
					</p>
				</div>
				<div className="flex items-center justify-between">
					{active && filteredAddon.menu_slug ? (
						<a
							className="text-ec-body text-sm underline"
							target="_blank"
							href={`admin.php?page=${filteredAddon.menu_slug}`}
						>
							{__('Settings', 'easycommerce')}
						</a>
						) : (
						<StarRating rating={filteredAddon.rating} />
					)}				

					{loading ? (
						<img className='h-7' src={`${EASYCOMMERCE.assets}admin/img/loader.gif`} alt={__('Loading...', 'easycommerce')} />
					) : (
						<button
							className={`p-1 text-[10px] leading-3 text-white 
								font-inter font-normal transition-all duration-300 rounded-full w-[76px] h-7 flex items-center justify-between ${
									active
										? 'bg-ec-primary flex-row-reverse pl-2'
										: 'bg-black/30 pr-2'
								}`}
							onClick={() => {
								handleAddonAction(addon);
							}}
						>
							<div className="h-5 w-5 rounded-full bg-white"></div>

							{active ? __('Enabled', 'easycommerce') : __('Disabled', 'easycommerce')}
						</button>
					)}
				</div>
			</div>

			{showPopup && <ProModal setShowPopup={setShowPopup} addonName={filteredAddon.name} addonDescription={filteredAddon.desc} />}
		</div>
	);
};

export default Card;
