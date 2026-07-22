import React from "react";
import { __ } from "@wordpress/i18n";

const dummyUser = `${EASYCOMMERCE.assets}public/img/icons/dashboard-default-user.png`;
const cameraIcon = `${EASYCOMMERCE.assets}public/img/icons/camera-icon.png`;

const UserImage = ({ userImage, setUserImage }) => {
	const openMediaLibrary = () => {
		const frame = wp.media({
			title: __( "Select Image", "easycommerce" ),
			button: {
				text: __( "Use selected image", "easycommerce" ),
			},
			multiple: false,
		});

		frame.on("open", () => {
			const selection = frame.state().get("selection");
			const attachment = wp.media.attachment(userImage.id);
			attachment.fetch();
			selection.add(attachment);
		});

		frame.on("select", () => {
			const attachments = frame.state().get("selection").toJSON();
			const selectedImage = attachments.map((attachment) => ({
				id: attachment.id,
				url: attachment.url,
			}));

			setUserImage(selectedImage[0]);
		});

		frame.open();
	};

	return (
		<button
			className='easycommerce-sidebar-image-btn relative w-[98px] h-[98px] 
            mx-auto p-[6px] border border-[#DBDBDB] rounded-full cursor-pointer'
			onClick={openMediaLibrary}>
			<img
				src={userImage.url || dummyUser}
				alt={__( 'user', 'easycommerce' )}
				className='w-full h-full rounded-full pointer-events-none'
			/>

			<span
				className='w-[33px] h-[33px] flex justify-center items-center 
                backdrop-blur-sm rounded-full absolute bottom-0 -right-1 border border-[#EEEEEE]'>
				<img
					src={cameraIcon}
					alt={__( 'camera', 'easycommerce' )}
					className='w-[14px] h-[12px] pointer-events-none'
				/>
			</span>
		</button>
	);
};

export default UserImage;
