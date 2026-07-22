import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';

import PanelTitle from '../common/PanelTitle';

const ProductSettings = ({ productTitle, productSlug, prevData }) => {
	const [isOpen, setIsOpen] = useState(true);
	const [slug, setSlug] = useState(productSlug || '');
	const [hideFromShop, setHideFromShop] = useState(prevData?.hide_from_shop || false);
	const [noindex, setNoindex] = useState(prevData?.noindex || false);
	const [showReview, setShowReview] = useState(prevData?.show_review ?? true);
	const [reviewTextMandatory, setReviewTextMandatory] = useState(prevData?.review_text_mandatory || false);

	useEffect(() => {
		if (productTitle && !productSlug) {
			const formattedSlug = productTitle
				.toLowerCase()
				.replace(/[^a-z0-9]+/g, '-')
				.replace(/^-|-$/g, '');

			setSlug(formattedSlug);
		}
	}, [productTitle, productSlug]);

	useEffect(() => {
		if (productSlug) {
			setSlug(productSlug);
		}
	}, [productSlug]);
	

	return (
		<div className="bg-white rounded-xl border-ec-table-stock border border-solid">
			<div className="py-[14px] px-6 flex items-center justify-between border-b border-ec-table-stock border-solid">
				<PanelTitle title={__('Settings', 'easycommerce')} notice={__('Configure general product settings like visibility, URL slug, and reviews.', 'easycommerce')} />

				<div className="panel-actions">
					<button
						className="panel-collapse"
						type="button"
						onClick={() => setIsOpen(!isOpen)}
					>
						<svg
							className={`transition-transform duration-300 ${
								isOpen ? '' : 'rotate-180'
							}`}
							xmlns="http://www.w3.org/2000/svg"
							width="11"
							height="6"
							viewBox="0 0 11 6"
							fill="none"
						>
							<path
								d="M1.12891 4.28906L5.28516 0.378906C5.43099 0.251302 5.58594 0.1875 5.75 0.1875C5.91406 0.1875 6.0599 0.251302 6.1875 0.378906L10.3438 4.28906C10.6172 4.59896 10.6263 4.90885 10.3711 5.21875C10.0794 5.49219 9.76953 5.5013 9.44141 5.24609L5.75 1.74609L2.03125 5.24609C1.72135 5.5013 1.42057 5.5013 1.12891 5.24609C0.873698 4.91797 0.873698 4.59896 1.12891 4.28906Z"
								fill="#3C3C42"
							/>
						</svg>
					</button>
				</div>
			</div>

			<motion.div
				initial={false}
				animate={{
					height: isOpen ? 'auto' : 0,
					opacity: isOpen ? 1 : 0,
					overflow: 'hidden',
					transition: { duration: 0.3, ease: 'easeInOut' }
				}}
				style={{
					visibility: isOpen ? 'visible' : 'hidden'
				}}
			>
				<div className="p-6">
					<div className="flex flex-col gap-6">
						<div>
							<h4 className="text-ec-title font-inter font-medium text-xl lg:text-base mb-4 lg:mb-2">
								{__('Slug', 'easycommerce')}
							</h4>
							<div className="h-10 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out overflow-hidden flex ">
								<div className="h-full flex items-center justify-center text-ec-light-black pl-4 rtl:pl-0">
									<span className="rtl:pr-4">/</span>
								</div>
								<input
									type="text"
									name="product_slug"
									className="h-full w-full border-none outline-none shadow-none p-0 text-ec-body font-inter text-[14px] leading-[20px] pr-2 rtl:pr-0"
									value={slug}
									onChange={(e) => setSlug(e.target.value)}
								/>
							</div>
							<p className="text-sm text-ec-body mt-4 mb-2">{__('URL:', 'easycommerce')}</p>
							<p className="text-sm text-ec-body break-all focus:shadow-none focus:outline-none">{EASYCOMMERCE.home_url}/products/<strong>{slug}</strong></p>

						</div>

						<div>
							<h4 className="text-ec-title font-inter font-medium text-xl lg:text-base">
								{__('Visibility', 'easycommerce')}
							</h4>
							<div className="flex justify-between items-center py-[20px] border-b border-ec-table-stock">
								<label htmlFor="hideFromShop" className="text-sm text-ec-body font-inter">
									{__('Hide from Shop', 'easycommerce')}
								</label>
								<label className="easycommerce-switch" htmlFor="hideFromShop">
									<input
										id="hideFromShop"
										type="checkbox"
										name="hide_from_shop"
										checked={hideFromShop}
										onChange={() => setHideFromShop(!hideFromShop)}
									/>
									<span className="easycommerce-slider easycommerce-round"></span>
								</label>
							</div>
							<div className="flex justify-between items-center py-[20px] border-b border-ec-table-stock">
								<label htmlFor="hideFromSearchEngines" className="text-sm text-ec-body font-inter">
									{__('Hide from Search Engines', 'easycommerce')}
								</label>
								<label className="easycommerce-switch" htmlFor="hideFromSearchEngines">
									<input
										id="hideFromSearchEngines"
										type="checkbox"
										name="hide_from_search_engines"
										checked={noindex}
										onChange={() => setNoindex(!noindex)}
									/>
									<span className="easycommerce-slider easycommerce-round"></span>
								</label>
							</div>
						</div>
						<div>
							<h4 className="text-ec-title font-inter font-medium text-xl lg:text-base">
								{__('Review', 'easycommerce')}
							</h4>
							<div className="flex justify-between items-center py-[20px]">
								<label htmlFor="showReview" className="text-sm text-ec-body font-inter">
									{__('Enable Review', 'easycommerce')}
								</label>
								<label className="easycommerce-switch" htmlFor="showReview">
									<input
										id="showReview"
										type="checkbox"
										name="set_review"
										checked={showReview}
										onChange={(e) => setShowReview(e.target.checked)}
									/>
									<span className="easycommerce-slider easycommerce-round"></span>
								</label>
							</div>
							{/* {showReview && (
								<div className="flex justify-between items-center py-[20px] border-ec-table-stock">
									<label htmlFor="setReviewMandatory" className="text-sm text-ec-body font-inter">
										{__('Set Review Text Mandatory', 'easycommerce')}
									</label>
									<label className="easycommerce-switch" htmlFor="setReviewMandatory">
										<input
											id="setReviewMandatory"
											type="checkbox"
											name="set_review_mandatory"
											checked={reviewTextMandatory}
											onChange={() => setReviewTextMandatory(!reviewTextMandatory)}
										/>
										<span className="easycommerce-slider easycommerce-round"></span>
									</label>
								</div>
							)} */}
						</div>
					</div>
				</div>
			</motion.div>
		</div>
	);
};

export default ProductSettings;