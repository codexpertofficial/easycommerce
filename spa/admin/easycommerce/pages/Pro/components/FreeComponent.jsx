import React from 'react';
import { __ } from '@wordpress/i18n';

// components
import ContentGrid from './ContentGrid';
import CompareTable from './CompareTable';
import Reviews from './Reviews';
import FAQ from './FAQ';

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

const FreeComponent = () => {
	return (
		<div className="py-[32px]">
			<div className="flex items-center justify-between">
				<h1 className="capitalize text-5xl leading-[58px] font-medium text-ec-title">
					{__('Free is perfect for starters', 'easycommerce')}
					<span className="block text-ec-primary">{__('But growth needs Pro 🚀', 'easycommerce')}</span>
				</h1>

				<div className="flex gap-4">
					<a
						href="https://easycommerce.dev/pricing?utm_source=inplugin&utm_medium=propage&utm_campaign=BFCM"
						target="_blank"
						className="group flex items-center gap-2.5 h-[60px] px-8 text-ec-title text-base font-medium rounded-lg border border-[#F99D1D] bg-[#F99D1D]"
					>
						{proIcon} {__('Upgrade to PRO', 'easycommerce')}
					</a>
				</div>
			</div>

			<ContentGrid />

			<CompareTable />

			<Reviews />

			<FAQ />

			<div 
				className="w-full mx-auto py-20 mt-[100px] rounded-xl" 
				style={{
					backgroundImage: `url(${EASYCOMMERCE.assets}admin/img/pro-cta-bg-2.png)`,
					backgroundSize: 'cover',
					backgroundRepeat: 'no-repeat',
					backgroundPosition: 'center',
				}}
			>
				<h1 className="text-white text-4xl font-medium text-center">
					{__('Ready to Upgrade?', 'easycommerce')}
				</h1>
				<p className="text-center text-[#E3E3E3] text-lg w-[580px] mx-auto mt-3">
					{__('Enhance your experience with powerful features to increase performance and flexibility.', 'easycommerce')}
				</p>
				<a
					href="https://easycommerce.dev/pricing?utm_source=inplugin&utm_medium=propage&utm_campaign=BFCM"
					target="_blank"
					className="group mt-8 flex items-center w-max gap-2.5 h-[60px] px-8 text-ec-title text-base font-medium rounded-lg border border-[#F99D1D] bg-[#F99D1D] mx-auto"
				>
					{proIcon} {__('Upgrade Now', 'easycommerce')}
				</a>
			</div>
		</div>
	);
};

export default FreeComponent;
