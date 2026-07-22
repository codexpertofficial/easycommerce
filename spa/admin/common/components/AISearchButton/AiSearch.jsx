import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { __ } from '@wordpress/i18n';
import AiChat from './ai-chat/AiChat';
import './aisearch.css';

const AiSearch = ({ user, setShowAPIModal }) => {
	const [isOpen, setIsOpen] = useState(false);
	const [isHidden, setIsHidden] = useState(false);
	const [isBotTyping, setIsBotTyping] = useState(false);
	const [pendingBotMessage, setPendingBotMessage] = useState(null);
	const [messages, setMessages] = useState([]);

	const closeIcon = (
		<svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path className='fill-[#3C3C42] group-hover:fill-white transition-colors duration-300' fill-rule="evenodd" clip-rule="evenodd" d="M0.260418 0.260418C0.607642 -0.086806 1.17015 -0.086806 1.51731 0.260418L5 3.7431L8.48269 0.260418C8.82991 -0.086806 9.39241 -0.086806 9.73958 0.260418C10.0868 0.607642 10.0868 1.17015 9.73958 1.51731L6.2569 5L9.73958 8.48269C10.0868 8.82991 10.0868 9.39241 9.73958 9.73958C9.39236 10.0868 8.82985 10.0868 8.48269 9.73958L5 6.2569L1.51731 9.73958C1.17009 10.0868 0.607587 10.0868 0.260418 9.73958C-0.0867505 9.39236 -0.086806 8.82985 0.260418 8.48269L3.7431 5L0.260418 1.51731C-0.086806 1.17009 -0.086806 0.607587 0.260418 0.260418Z"/>
		</svg>
	);

	const btnIcon = (
		<svg width="13" height="15" viewBox="0 0 13 15" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M4.38754 3.08331C4.39486 3.05921 4.40973 3.0381 4.42996 3.0231C4.4502 3.0081 4.47472 3 4.49991 3C4.52509 3 4.54961 3.0081 4.56985 3.0231C4.59008 3.0381 4.60495 3.05921 4.61227 3.08331L5.03826 4.5117C5.2111 5.09142 5.5256 5.61896 5.95335 6.04672C6.38111 6.47448 6.90867 6.78898 7.48839 6.96184L8.91684 7.38781C8.94091 7.39515 8.96198 7.41003 8.97695 7.43025C8.99192 7.45047 9 7.47497 9 7.50013C9 7.52528 8.99192 7.54978 8.97695 7.57C8.96198 7.59022 8.94091 7.6051 8.91684 7.61244L7.48839 8.03841C6.90869 8.21127 6.38117 8.52577 5.95343 8.95351C5.52569 9.38125 5.2112 9.90877 5.03836 10.4885L4.61227 11.9168C4.60493 11.9409 4.59005 11.962 4.56983 11.9769C4.54961 11.9919 4.52511 12 4.49995 12C4.47479 12 4.4503 11.9919 4.43008 11.9769C4.40985 11.962 4.39498 11.9409 4.38763 11.9168L3.96164 10.4885C3.78877 9.90876 3.47425 9.38125 3.04647 8.95353C2.6187 8.5258 2.09114 8.21133 1.51142 8.03851L0.083157 7.61235C0.0590924 7.605 0.0380227 7.59013 0.0230517 7.5699C0.00808069 7.54968 0 7.52519 0 7.50003C0 7.47487 0.00808069 7.45038 0.0230517 7.43016C0.0380227 7.40994 0.0590924 7.39506 0.083157 7.38771L1.51151 6.96174C2.09126 6.7889 2.61883 6.4744 3.04661 6.04664C3.47438 5.61889 3.78889 5.09133 3.96174 4.51161L4.38754 3.08331Z" fill="url(#paint0_linear_7419_5198)"/>
			<path fill-rule="evenodd" clip-rule="evenodd" d="M10.4375 0.046283C10.4416 0.0328938 10.4498 0.0211667 10.4611 0.0128329C10.4723 0.00449901 10.486 0 10.4999 0C10.5139 0 10.5276 0.00449901 10.5388 0.0128329C10.55 0.0211667 10.5583 0.0328938 10.5624 0.046283L10.799 0.839835C10.8951 1.1619 11.0698 1.45498 11.3074 1.69262C11.5451 1.93027 11.8381 2.10499 12.1602 2.20102L12.9538 2.43767C12.9672 2.44175 12.9789 2.45002 12.9872 2.46125C12.9955 2.47248 13 2.48609 13 2.50007C13 2.51405 12.9955 2.52765 12.9872 2.53889C12.9789 2.55012 12.9672 2.55839 12.9538 2.56247L12.1602 2.79912C11.8382 2.89515 11.5451 3.06987 11.3075 3.3075C11.0698 3.54514 10.8951 3.8382 10.7991 4.16025L10.5624 4.9538C10.5583 4.96717 10.55 4.97888 10.5388 4.98719C10.5276 4.99551 10.514 5 10.5 5C10.486 5 10.4724 4.99551 10.4612 4.98719C10.4499 4.97888 10.4417 4.96717 10.4376 4.9538L10.2009 4.16025C10.1049 3.8382 9.93014 3.54514 9.69248 3.30751C9.45483 3.06989 9.16174 2.89519 8.83968 2.79917L8.0462 2.56242C8.03283 2.55834 8.02112 2.55007 8.01281 2.53884C8.00449 2.5276 8 2.51399 8 2.50002C8 2.48604 8.00449 2.47243 8.01281 2.4612C8.02112 2.44996 8.03283 2.4417 8.0462 2.43762L8.83973 2.20097C9.16181 2.10494 9.45491 1.93022 9.69256 1.69258C9.93021 1.45494 10.1049 1.16185 10.201 0.839782L10.4375 0.046283Z" fill="url(#paint1_linear_7419_5198)"/>
			<path fill-rule="evenodd" clip-rule="evenodd" d="M10.4375 10.0463C10.4416 10.0329 10.4498 10.0212 10.4611 10.0128C10.4723 10.0045 10.486 10 10.4999 10C10.5139 10 10.5276 10.0045 10.5388 10.0128C10.55 10.0212 10.5583 10.0329 10.5624 10.0463L10.799 10.8398C10.8951 11.1619 11.0698 11.455 11.3074 11.6926C11.5451 11.9303 11.8381 12.105 12.1602 12.201L12.9538 12.4377C12.9672 12.4418 12.9789 12.45 12.9872 12.4613C12.9955 12.4725 13 12.4861 13 12.5001C13 12.514 12.9955 12.5277 12.9872 12.5389C12.9789 12.5501 12.9672 12.5584 12.9538 12.5625L12.1602 12.7991C11.8382 12.8952 11.5451 13.0699 11.3075 13.3075C11.0698 13.5451 10.8951 13.8382 10.7991 14.1603L10.5624 14.9538C10.5583 14.9672 10.55 14.9789 10.5388 14.9872C10.5276 14.9955 10.514 15 10.5 15C10.486 15 10.4724 14.9955 10.4612 14.9872C10.4499 14.9789 10.4417 14.9672 10.4376 14.9538L10.2009 14.1603C10.1049 13.8382 9.93014 13.5451 9.69248 13.3075C9.45483 13.0699 9.16174 12.8952 8.83968 12.7992L8.0462 12.5624C8.03283 12.5583 8.02112 12.5501 8.01281 12.5388C8.00449 12.5276 8 12.514 8 12.5C8 12.486 8.00449 12.4724 8.01281 12.4612C8.02112 12.45 8.03283 12.4417 8.0462 12.4376L8.83973 12.201C9.16181 12.1049 9.45491 11.9302 9.69256 11.6926C9.93021 11.4549 10.1049 11.1619 10.201 10.8398L10.4375 10.0463Z" fill="url(#paint2_linear_7419_5198)"/>
			<defs>
			<linearGradient id="paint0_linear_7419_5198" x1="4.5" y1="3" x2="4.5" y2="12" gradientUnits="userSpaceOnUse">
			<stop offset="0.2" stop-color="#7500FF"/>
			<stop offset="0.466346" stop-color="#AE00FF"/>
			<stop offset="1" stop-color="#FF076A"/>
			</linearGradient>
			<linearGradient id="paint1_linear_7419_5198" x1="10.5" y1="0" x2="10.5" y2="5" gradientUnits="userSpaceOnUse">
			<stop offset="0.2" stop-color="#7500FF"/>
			<stop offset="0.466346" stop-color="#AE00FF"/>
			<stop offset="1" stop-color="#FF07A9"/>
			</linearGradient>
			<linearGradient id="paint2_linear_7419_5198" x1="10.5" y1="10" x2="10.5" y2="15" gradientUnits="userSpaceOnUse">
			<stop offset="0.2" stop-color="#7500FF"/>
			<stop offset="0.466346" stop-color="#AE00FF"/>
			<stop offset="1" stop-color="#FF07A9"/>
			</linearGradient>
			</defs>
		</svg>
	);

	const closeModal = () => {
		setIsHidden(true);

		setTimeout(() => {
			setIsOpen(false); 
			setIsHidden(false); 
			setPendingBotMessage(null);
			setIsBotTyping(false);
			setMessages([]);
		}, 300); 
	};

	const openModal = () => {
		if (!user && !EASYCOMMERCE.pro.licensed) {
			setShowAPIModal(true);
			return;
		}

		setIsHidden(true);   
		setIsOpen(true);  

		requestAnimationFrame(() => {
			setIsHidden(false); 
		});
	};

	const AIChatBox = (
		<div
			className={`font-inter backdrop-blur-[10px] fixed top-0 left-0 w-full h-full bg-[#00000063] z-[9999] transition-opacity duration-300 ease-out ${
				isHidden ? 'opacity-0 pointer-events-none' : 'opacity-100'
			}`}
		>
			<div className={`fixed w-[675px] h-[85%] top-[8%] left-1/2 -translate-x-1/2 flex flex-col transition-all duration-300 ease-out ${isHidden ? 'opacity-0 scale-95 translate-y-4' : 'opacity-100 scale-100 translate-y-0'}`}>
				<button
					data-tooltip-id="ai-close-btn"
					className="absolute top-[-20px] right-[-20px] p-[7px] bg-white rounded-full hover:bg-[#fa4109] transition-colors duration-200 group"
					onClick={closeModal}
				>
					{closeIcon}
				</button>
				<div className="grow w-full h-full bg-[#EEF0FF] rounded-xl">
					<div className="bg-white w-full h-full rounded-[10px] overflow-y-auto scrollbar-hide">
						<AiChat
							user={user || null}
							isBotTyping={isBotTyping}
							setIsBotTyping={setIsBotTyping}
							pendingBotMessage={pendingBotMessage}
							setPendingBotMessage={setPendingBotMessage}
							messages={messages}
							setMessages={setMessages}
						/>
					</div>
				</div>
			</div>
		</div>
	);

	return (
		<>
			<button
				className="ai-search-btn flex items-center gap-[6px] justify-center rounded-[20px] px-3 py-[7px] text-black text-xs font-medium"
				onClick={openModal}
			>
				{btnIcon}
				<span className="pt-px">{ __( 'Store Copilot', 'easycommerce' ) }</span>
			</button>

			{isOpen && createPortal(AIChatBox, document.body)}
		</>
	);
};
export default AiSearch;
