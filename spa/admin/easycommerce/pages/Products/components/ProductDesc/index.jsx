import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';
import { Tooltip } from 'react-tooltip';

import PanelTitle from '../common/PanelTitle';
import TextEditor from '../../../../../common/components/inputs/TextEditor';
import AiGenerate from '../common/AiGenerate';

const ProductDesc = ({ productTitle, prevData }) => {
	const [desc, setDesc] = useState(prevData || '');
	const [isOpen, setIsOpen] = useState(true);
	const [aiOpen, setAiOpen] = useState(false);

	const handleEditorChange = (newContent) => {
		setDesc(newContent);
	};

	const handleMediaUpload = () => {
		const frame = wp.media({
			title: __('Select Image', 'easycommerce'),
			button: {
				text: __('Use selected image', 'easycommerce'),
			},
			multiple: false, // Allow multiple image selection
		});

		frame.on('select', () => {
			const attachments = frame.state().get('selection').toJSON();
			const imgHtml = `<img src="${attachments[0].url}" alt="${attachments[0].name}" style="max-width:100%;">`;

			const editor = window.tinymce.get('easycommerce-classic-editor');
			if (editor) {
				editor.insertContent(imgHtml);
			}
		});

		frame.open();
	};

	const handleDescChange = async (value) => {
		setDesc('');

		for (let i = 0; i < value.length; i++) {
			await new Promise((resolve) => setTimeout(resolve, 5));
			setDesc((prev) => prev + value[i]);
		}
	};

	return (
		<>
			{aiOpen && (
				<AiGenerate
					contentType="description"
					productTitle={productTitle}
					setAiOpen={setAiOpen}
					setAiContent={handleDescChange}
				/>
			)}
			<div className="bg-white rounded-xl border-ec-table-stock border border-solid overflow-hidden">
				<div className="py-[14px] px-6 flex items-center justify-between border-b border-ec-table-stock border-solid">
					<PanelTitle
						title={__('Product Description', 'easycommerce')}
						notice={__(
							'Provide an in-depth product description of 600–900 words, outlining key features, benefits, and usage instructions.',
							'easycommerce'
						)}
					/>
					<div className="panel-actions">
						<div>
							<button
								data-tooltip-id={!productTitle ? 'ai-gallery' : ''}
								data-tooltip-content={__('Add product title first!', 'easycommerce')}
								className={`ai-generate ${
									productTitle ? '' : 'grayscale opacity-50 cursor-not-allowed'
								}`}
								type="button"
								onClick={() => productTitle && setAiOpen(true)}
							>
								<svg
									width="17"
									height="17"
									viewBox="0 0 17 17"
									fill="none"
									xmlns="http://www.w3.org/2000/svg"
								>
									<path
										d="M13.4235 9.7689C9.65184 11.015 8.39589 12.2712 7.14976 16.0427C7.04724 16.3527 6.6088 16.3527 6.5063 16.0427C5.26021 12.271 4.00401 11.015 0.232545 9.7689C-0.0775151 9.66638 -0.0775151 9.22794 0.232545 9.12544C4.00422 7.87935 5.26017 6.62315 6.5063 2.85169C6.60882 2.54163 7.04725 2.54163 7.14976 2.85169C8.39585 6.62336 9.65205 7.87931 13.4235 9.12544C13.7336 9.22796 13.7336 9.6664 13.4235 9.7689Z"
										fill="url(#paint0_linear_5708_10443)"
									/>
									<path
										d="M16.1579 3.57531C14.2725 4.19795 13.6441 4.82641 13.0206 6.71261C12.9698 6.86764 12.7506 6.86764 12.6989 6.71261C12.0763 4.82722 11.4478 4.1988 9.56159 3.57531C9.40655 3.52447 9.40655 3.30526 9.56159 3.25358C11.447 2.63094 12.0754 2.00248 12.6989 0.116274C12.7497 -0.0387581 12.9689 -0.0387581 13.0206 0.116274C13.6433 2.00166 14.2717 2.63009 16.1579 3.25358C16.313 3.30442 16.313 3.52363 16.1579 3.57531Z"
										fill="url(#paint1_linear_5708_10443)"
									/>
									<defs>
										<linearGradient
											id="paint0_linear_5708_10443"
											x1="6.82803"
											y1="2.61914"
											x2="6.82803"
											y2="16.2752"
											gradientUnits="userSpaceOnUse"
										>
											<stop stop-color="#3200FF" />
											<stop offset="1" stop-color="#FF48E7" />
										</linearGradient>
										<linearGradient
											id="paint1_linear_5708_10443"
											x1="12.8598"
											y1="0"
											x2="12.8598"
											y2="6.82889"
											gradientUnits="userSpaceOnUse"
										>
											<stop stop-color="#3200FF" />
											<stop offset="1" stop-color="#FF48E7" />
										</linearGradient>
									</defs>
								</svg>
								<span className="easycommerce-ai-generate-text">
									{__('Generate with AI', 'easycommerce')}
								</span>
							</button>

							{!productTitle && (
								<Tooltip
									id="ai-gallery"
									place="top"
									style={{ backgroundColor: '#7351fd', color: 'white' }}
								/>
							)}
						</div>

						<button
							className="panel-collapse"
							type="button"
							onClick={() => setIsOpen(!isOpen)}
						>
							<svg
								className={`transition-transform duration-300 ${
									isOpen ? '' : 'rotate-180'
								}`}
								width="11"
								height="6"
								viewBox="0 0 11 6"
								fill="none"
								xmlns="http://www.w3.org/2000/svg"
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
						transition: { duration: 0.3, ease: 'easeInOut' },
					}}
					style={{
						visibility: isOpen ? 'visible' : 'hidden',
					}}
				>
					<div className="p-6 duration-300">
						<TextEditor
							content={desc}
							onChange={handleEditorChange}
							handleMediaUpload={handleMediaUpload}
						/>
					</div>
				</motion.div>
			</div>
			<input type="hidden" name="product_description" value={desc} />
		</>
	);
};

export default ProductDesc;