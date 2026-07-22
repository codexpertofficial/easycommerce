import React from 'react';
import { __ } from '@wordpress/i18n';

const uploadIcon = (
	<svg
		className='w-full max-w-[60%]'
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 36 33"
		fill="none"
	>
		<path
			d="M21.8332 8.70408C23.4729 8.70408 24.8071 7.36984 24.8071 5.73022C24.8071 4.0906 23.4729 2.75635 21.8332 2.75635C20.1936 2.75635 18.8594 4.0906 18.8594 5.73022C18.8594 7.36984 20.1936 8.70408 21.8332 8.70408ZM21.8332 4.4557C22.5335 4.4557 23.1078 5.02991 23.1078 5.73022C23.1078 6.43052 22.5335 7.00473 21.8332 7.00473C21.1329 7.00473 20.5587 6.43052 20.5587 5.73022C20.5587 5.02991 21.1329 4.4557 21.8332 4.4557Z"
			fill="#7F7F98"
		/>
		<path
			d="M27.5121 22.9362C27.3462 22.7703 27.1338 22.6807 26.918 22.6641C26.8882 22.6591 26.8633 22.6558 26.8334 22.6558C26.8035 22.6558 26.7737 22.6558 26.7488 22.6641C26.5314 22.6856 26.319 22.7703 26.1547 22.9362L23.9375 25.1534C23.75 25.3409 23.75 25.6463 23.9375 25.8371C24.125 26.0246 24.4304 26.0246 24.6213 25.8371L26.3538 24.1046V29.5463C26.3538 29.8135 26.5712 30.0309 26.8384 30.0309C27.1055 30.0309 27.3229 29.8135 27.3229 29.5463V24.1046L29.0555 25.8371C29.1484 25.93 29.2729 25.9765 29.3957 25.9765C29.5185 25.9765 29.6413 25.93 29.7359 25.8371C29.9234 25.6496 29.9234 25.3442 29.7359 25.1534L27.5187 22.9362H27.5121Z"
			fill="#7F7F98"
		/>
		<path
			d="M26.8242 20.1802C23.4255 20.1802 20.6641 22.9416 20.6641 26.3403C20.6641 29.739 23.4255 32.5005 26.8242 32.5005C30.2229 32.5005 32.9844 29.739 32.9844 26.3403C32.9844 22.9416 30.2229 20.1802 26.8242 20.1802ZM26.8242 30.8011C24.3648 30.8011 22.3634 28.7998 22.3634 26.3403C22.3634 23.8809 24.3648 21.8795 26.8242 21.8795C29.2837 21.8795 31.285 23.8809 31.285 26.3403C31.285 28.7998 29.2837 30.8011 26.8242 30.8011Z"
			fill="#7F7F98"
		/>
		<path
			d="M33.9871 0.000127625H1.69935C0.760078 0.000127625 0 0.760206 0 1.69948V27.4404C0 28.3797 0.760078 29.1398 1.69935 29.1398H18.859C19.327 29.1398 19.7087 28.7581 19.7087 28.2901C19.7087 27.8221 19.327 27.4404 18.859 27.4404H6.16016H6.11701H1.69954V21.3648L13.2713 12.5826L19.1043 17.4899L24.5044 13.892L33.9822 20.545V24.0582C33.9822 24.5262 34.3639 24.9079 34.8318 24.9079C35.2998 24.9079 35.6815 24.5262 35.6815 24.0582V1.69935C35.6815 0.760078 34.9214 0 33.9822 0L33.9871 0.000127625ZM33.9821 11.3212V11.3511V18.462L24.5294 11.8307L19.2241 15.3688L13.3277 10.4101L1.69947 19.234V1.69966H33.9872V11.3214L33.9821 11.3212Z"
			fill="#7F7F98"
		/>
	</svg>
);

/**
 * Thumbnail component for displaying and selecting a product price item's thumbnail image using the WordPress media library.
 *
 * @component
 * @param {Object} props
 * @param {Object} props.priceItem - The price item object containing metadata, including the current thumbnail.
 * @param {number} props.index - The index of the price item in the list.
 * @param {Function} props.setPriceItem - Function to update the price item state.
 *
 * @returns {JSX.Element} Button element that displays the current thumbnail or an upload icon, and opens the media library on click.
 */
const Thumbnail = ({ priceItem, index, setPriceItem, width = '67px', height = '67px' }) => {
	const openMediaLibrary = () => {
		const frame = wp.media({
			title: __('Select Image', 'easycommerce'),
			button: { text: __('Use selected image', 'easycommerce') },
			multiple: false,
		});

		frame.on('open', () => {
			const selection = frame.state().get('selection');
			const currentID = priceItem.meta?.thumbnail?.id;
			if (currentID) {
				const attachment = wp.media.attachment(currentID);
				attachment.fetch();
				selection.add(attachment ? [attachment] : []);
			}
		});

		frame.on('select', () => {
			const attachments = frame.state().get('selection').toJSON();
			if (attachments.length > 0) {
				const selectedImage = {
					id: attachments[0].id,
					url: attachments[0].url,
				};
				setPriceItem((prev) => ({
					...prev,
					meta: {
						...prev.meta,
						thumbnail: selectedImage,
					},
				}));
			}
		});

		frame.open();
	};

	return (
		<button
			type="button"
			className="rounded-lg border border-ec-table-stock flex items-center justify-center overflow-hidden"
			style={{
				width,
				height
			}}
			onClick={() => openMediaLibrary(index)}
		>
			{priceItem.meta?.thumbnail?.url ? (
				<img
					src={priceItem.meta.thumbnail.url}
					alt=""
					className="w-full h-full object-cover"
				/>
			) : (
				uploadIcon
			)}
		</button>
	);
};

export default Thumbnail;
