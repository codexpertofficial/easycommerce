import React, { useEffect, useState } from 'react';
import Cookies from 'universal-cookie';
import { __ } from '@wordpress/i18n';
import { toast } from 'react-toastify';

import AiCredits from '../../../../../../common/components/AiCredits';
import APIScreen from '../../../../../../common/components/APIScreen';
import APIVarification from '../../../../../../common/components/APIScreen/elements/APIVarification';
import APICreateForm from '../../../../../../common/components/APIScreen/elements/APICreateForm';
import NumberField from '../../../../../../common/components/inputs/NumberField';

import { aiIcon, generateIcon, upgradeIcon } from './icons';

const loadingGifURL = EASYCOMMERCE.assets + 'admin/img/loading.gif';

const AiGenerateAttributes = ({ productTitle, setAiOpen, setAiContent }) => {
	const [isLoading, setIsLoading] = useState(false);
	const [showAPIModal, setShowAPIModal] = useState(false);
	const [currentAPIModalTab, setCurrentAPIModalTab] = useState('');
	const [user, setUser] = useState(null);
	const [error, setError] = useState('');

	const isLicensed = EASYCOMMERCE.pro.activated && EASYCOMMERCE.pro.licensed;

	const [numberOfAttributes, setNumberOfAttributes] = useState(2);
	const [numberOfValues, setNumberOfValues] = useState(3);

	useEffect(() => {
		if (EASYCOMMERCE.credits === '') {
			return;
		}

		const cookies = new Cookies(null, { path: '/' });

		fetch(`${EASYCOMMERCE.rest_base}/connectivity/check`, {
			method: 'GET',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success && data.data.connected) {
					setUser(data.data.user);

					cookies.set('easycommerce-user', JSON.stringify(data.data.user), {
						path: '/',
						maxAge: 86400 * 30,
					});
				} else {
					cookies.remove('easycommerce-user', { path: '/' });
					setShowAPIModal(true);
				}
			})
			.catch(() => {
				cookies.remove('easycommerce-user', { path: '/' });
				setShowAPIModal(true);
			});
	}, []);

	const handleModalClose = () => {
		setShowAPIModal(false);
		setAiOpen(false);
		setCurrentAPIModalTab('');
	};

	const handleSend = async () => {
		setIsLoading(true);
		setError('');
		try {
			await sendToAi();
			setError('');
		} catch (error) {
			setError(
				error.message ||
					__('An error occurred. Please try again.', 'easycommerce'),
			);
		} finally {
			setIsLoading(false);
			setAiOpen(false);
		}
	};

	const sendToAi = async () => {
		const attrRequestBody = {
			product: productTitle,
			num_attributes: numberOfAttributes,
			num_values: numberOfValues,
		};

		const requestEndPoint = `${EASYCOMMERCE.rest_base}/ai/generate-attributes`;

		try {
			const response = await fetch(requestEndPoint, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					email: user?.email || '',
					'X-WP-Nonce': EASYCOMMERCE.nonce,
				},
				body: JSON.stringify({
					...attrRequestBody,
				}),
			});

			const data = await response.json();

			if (!response.ok) {
				toast.error(data.data.message);
				throw new Error(data.data.message);
			}

			const responseData = data.data.attributes;

			if (data && responseData) {
				setAiContent(responseData);
				setError('');
				if (EASYCOMMERCE.credits !== '') {
					EASYCOMMERCE.credits -= 1;
				}
			} else {
				throw new Error(
					__(
						'Invalid response from AI service. Please try again.',
						'easycommerce',
					),
				);
			}

			return responseData || 'No answer found';
		} catch (error) {
			console.error('Error sending AI request:', error);
			throw error;
		}
	};

	const sectionTitle = __('AI Attributes Generator', 'easycommerce');

	const sectionSubTitle = __(
		'AI-powered attribute generator to create product attributes',
		'easycommerce',
	);

	return user || isLicensed ? (
		<div className="font-inter backdrop-blur-[10px] fixed top-0 left-0 w-full h-full bg-[#00000063] z-[9999]">
			<div className="fixed w-[660px] top-[90px] left-[50%] -translate-x-1/2 rounded-xl overflow-hidden flex flex-col transition-height duration-300 ease-in-out h-max">
				<div className="px-6 py-4 flex items-center justify-between border-b border-ec-table-stock bg-white">
					<div className="flex items-center gap-3">
						{aiIcon}
						<div>
							<h2 className="text-base text-ec-allText">{sectionTitle}</h2>
							<p className="text-sm text-ec-light-black font-normal">
								{sectionSubTitle}
							</p>
						</div>
					</div>
					{!isLoading && (
						<button onClick={handleModalClose}>
							<svg
								xmlns="http://www.w3.org/2000/svg"
								width="10"
								height="10"
								viewBox="0 0 10 10"
								fill="none"
							>
								<path
									fillRule="evenodd"
									clipRule="evenodd"
									d="M0.260418 0.260418C0.607642 -0.086806 1.17015 -0.086806 1.51731 0.260418L5 3.7431L8.48269 0.260418C8.82991 -0.086806 9.39241 -0.086806 9.73958 0.260418C10.0868 0.607642 10.0868 1.17015 9.73958 1.51731L6.2569 5L9.73958 8.48269C10.0868 8.82991 10.0868 9.39241 9.73958 9.73958C9.39236 10.0868 8.82985 10.0868 8.48269 9.73958L5 6.2569L1.51731 9.73958C1.17009 10.0868 0.607587 10.0868 0.260418 9.73958C-0.0867505 9.39236 -0.086806 8.82985 0.260418 8.48269L3.7431 5L0.260418 1.51731C-0.086806 1.17009 -0.086806 0.607587 0.260418 0.260418Z"
									fill="#7F7F98"
								/>
							</svg>
						</button>
					)}
				</div>
				<div className="grow w-full bg-white p-6">
					{String(EASYCOMMERCE.credits) === '0' && (
							<div className="mb-4 text-sm border bg-[#7351FD08] border-[#7351FD08] rounded-lg">
								<div className="flex items-center justify-between bg-ec-error-bg p-2 rounded-lg">
									<div className="flex items-center gap-2">
										{upgradeIcon}
										<span>
											{__(
												'You have used all your free credits.',
												'easycommerce',
											)}
										</span>
									</div>
									<a
										href="admin.php?page=easycommerce"
										target="_blank"
										rel="noopener noreferrer"
										className="ai-builder-upgrade text-white px-3 py-2 font-normal text-[14px]"
									>
										{__('Upgrade Now', 'easycommerce')}
									</a>
								</div>
							</div>
						)}

					<div className="flex flex-col gap-4">
						<div>
							<div className="w-full grid grid-cols-2 gap-6">
								<div className="flex flex-col">
									<label className="text-sm text-ec-body mb-2.5 block">
										{__('Number of Attributes to Generate', 'easycommerce')}
									</label>
									<NumberField
										value={numberOfAttributes}
										min={1}
										max={10}
										onChange={(e) => setNumberOfAttributes(e.target.value)}
										className="h-10"
									/>
								</div>

								<div className="flex flex-col">
									<label className="text-sm text-ec-body mb-2.5 block">
										{__('Number of Values Per Attribute', 'easycommerce')}
									</label>
									<NumberField
										value={numberOfValues}
										min={1}
										max={10}
										onChange={(e) => setNumberOfValues(e.target.value)}
										className="h-10"
									/>
								</div>
							</div>
						</div>
						{error && (
							<div className="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-2">
								{error}
							</div>
						)}
						<div className="flex items-center">
							{EASYCOMMERCE.credits !== '' && (
								<h2 className="text-sm font-normal">
									{Number(EASYCOMMERCE.credits) > 0 ? (
										<AiCredits usage={1} />
									) : (
										<span className="text-ec-light-black">
											{__('No credits available', 'easycommerce')}
										</span>
									)}
								</h2>
							)}
							<button
								className={
									'ml-auto flex items-center gap-4 text-[14px] justify-center w-[20%] h-ec-input rounded-[8px] text-white ' +
									(String(EASYCOMMERCE.credits) === '0'
										? 'ai-builder-disable cursor-not-allowed'
										: 'ai-builder') +
									(isLoading ? ' bg-gray-400 cursor-not-allowed' : '')
								}
								onClick={handleSend}
								type="button"
								disabled={
									isLoading || String(EASYCOMMERCE.credits) === '0'
								}
							>
								{isLoading ? (
									<img src={loadingGifURL} alt={__('loading', 'easycommerce')} className="h-8" />
								) : (
									<>
										{generateIcon}
										{__('Generate', 'easycommerce')}
									</>
								)}
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	) : showAPIModal && currentAPIModalTab === '' ? (
		<APIScreen
			onClose={handleModalClose}
			switchVariationModalTab={() => setCurrentAPIModalTab('apiVarification')}
			switchCreateModalTab={() => setCurrentAPIModalTab('apiCreate')}
		/>
	) : showAPIModal && currentAPIModalTab === 'apiVarification' ? (
		<APIVarification
			onClose={handleModalClose}
			switchModalTab={() => setCurrentAPIModalTab('apiCreate')}
			setUserAfterVarification={(userData) => setUser(userData)}
		/>
	) : showAPIModal && currentAPIModalTab === 'apiCreate' ? (
		<APICreateForm
			onClose={handleModalClose}
			isSettings={false}
			switchModalTab={() => setCurrentAPIModalTab('apiVarification')}
		/>
	) : null;
};

export default AiGenerateAttributes;
