import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';

const FAQ = () => {
	const [openIndex, setOpenIndex] = useState(0);

	const faqs = [
		{
			question: __('What is EasyCommerce Pro?', 'easycommerce'),
			answer: __('EasyCommerce Pro is the premium addon that unlocks advanced features and capabilities within the EasyCommerce plugin. It serves as the key to accessing pro-level addons and enhancements that take your ecommerce store to the next level.', 'easycommerce'),
		},
		{
			question: __('Is there a free trial?', 'easycommerce'),
			answer: __('While we do not offer a traditional free trial for EasyCommerce Pro, we stand behind our product with a generous 30-day refund policy. This allows you to purchase and try Pro features risk-free, and if it doesn\'t meet your needs, you can request a full refund within 30 days.', 'easycommerce'),
		},
		{
			question: __('What does EasyCommerce Pro enable?', 'easycommerce'),
			answer: __('EasyCommerce Pro enables access to premium addons such as "License Manager" for managing digital product licenses and "Subscriptions" for handling recurring payments. These addons provide powerful tools to expand your store\'s functionality and revenue streams.', 'easycommerce'),
		},
		{
			question: __('Can customers shop through Messenger or WhatsApp?', 'easycommerce'),
			answer: __('Yes. The Messenger Integration addon connects your Facebook Page to the EasyCommerce AI shopping assistant, so customers can discover products, check stock, and place orders without leaving the chat. WhatsApp integration is coming soon and will work the same way.', 'easycommerce'),
		},
		{
			question: __('Are future pro addons included?', 'easycommerce'),
			answer: __('Yes, all future pro addons will be included automatically with your EasyCommerce Pro subscription. As we release new premium features and addons, you\'ll have immediate access without any additional purchases.', 'easycommerce'),
		},
		{
			question: __('What payment methods are supported?', 'easycommerce'),
			answer: __('EasyCommerce Pro supports a wide range of payment methods through Stripe integration, including all major credit and debit cards, as well as popular options like LINK for instant payments and Klarna for buy now, pay later services. This ensures your customers have flexible and secure payment choices.', 'easycommerce'),
		},
		{
			question: __('What support do you offer?', 'easycommerce'),
			answer: __('We provide top-class support through multiple channels including support tickets for detailed inquiries, email for direct communication, and phone support for urgent issues. Our expert team is dedicated to helping you succeed with EasyCommerce.', 'easycommerce'),
		},
		{
			question: __('Do you offer migration from other platforms?', 'easycommerce'),
			answer: __('Yes, we offer free migration services to help you seamlessly move your store from other ecommerce platforms to EasyCommerce. Our team will assist with transferring your products, customers, and order data at no extra cost.', 'easycommerce'),
		},
		{
			question: __('Where can I get additional help?', 'easycommerce'),
			answer: (
				<>
					{__('For additional help and resources, please visit our comprehensive support center at', 'easycommerce')}{' '}
					<a href="https://support.easycommerce.dev" target="_blank" rel="noopener noreferrer" className="text-ec-primary hover:underline">
						https://support.easycommerce.dev
					</a>
					{__(', where you can find documentation, tutorials, and contact our support team.', 'easycommerce')}
				</>
			),
		},
	];

	return (
		<div className="mt-24 py-[100px] px-[120px] rounded-xl bg-ec-table-stock">
			<h1 className="text-center text-4xl leading-[44px] font-medium text-ec-title">
				{__('Frequently Asked', 'easycommerce')} <span className="text-ec-primary">{__('Questions', 'easycommerce')}</span>
			</h1>

			<div className="flex flex-col gap-4 mt-[60px] max-w-[800px] mx-auto">
				{faqs.map((faq, index) => (
					<div
						key={index}
						className="bg-white p-5 rounded-lg border border-ec-table-stock"
					>
						<button
							onClick={() => {
								openIndex === index ? setOpenIndex(null) : setOpenIndex(index);
							}}
							className={`group flex items-center justify-between w-full ${openIndex === index ? 'text-ec-primary' : 'text-ec-title'} hover:text-ec-primary duration-300 font-medium text-xl`}
						>
							<span>{faq.question}</span>

							<span className={`flex items-center justify-center w-8 h-8 text-xl rounded-full group-hover:bg-ec-primary duration-300 group-hover:text-white ${openIndex === index ? 'bg-ec-primary text-white' : 'bg-ec-primary/10 text-ec-primary'}`}>
								{openIndex === index ? '-' : '+'}
							</span>
						</button>

						<motion.div
							initial={false}
							animate={{
								height: openIndex === index ? 'auto' : 0,
								opacity: openIndex === index ? 1 : 0,
								overflow: 'hidden',
								transition: { duration: 0.3, ease: 'easeInOut' },
							}}
							style={{
								visibility: openIndex === index ? 'visible' : 'hidden',
							}}
						>
							<p className="text-ec-body text-sm pt-5 mt-5 border-t border-ec-table-stock">
								{faq.answer}
							</p>
						</motion.div>
					</div>
				))}
			</div>
		</div>
	);
};

export default FAQ;
