import React, { useEffect, useState } from 'react';
import Cookies from 'universal-cookie';
import { __, sprintf } from '@wordpress/i18n';
import { toast } from 'react-toastify';
import { twMerge } from 'tailwind-merge';

import APIScreen from '../../../../../../common/components/APIScreen';
import APIVarification from '../../../../../../common/components/APIScreen/elements/APIVarification';
import APICreateForm from '../../../../../../common/components/APIScreen/elements/APICreateForm';
import Dropdown from '../../../../../../common/components/inputs/Dropdown';
import AiCredits from '../../../../../../common/components/AiCredits';

// Fancybox
import useFancybox from '../../../../../../common/components/Fancybox';

import { aiIcon, generateIcon, upgradeIcon } from './icons';
const loadingGifURL = EASYCOMMERCE.assets + 'admin/img/loading.gif';

const AiGenerateImages = ({ productTitle, setAiOpen, setAiContent }) => {
	const [input, setInput] = useState(
	    // translators: %s: product title.
	    sprintf(__('A professional product photo of %s on a clean white background, studio lighting, sharp focus, high resolution, commercial photography style, no shadows, photorealistic.', 'easycommerce'), productTitle)
	);
	const [isLoading, setIsLoading] = useState(false);
	const [isImporting, setIsImporting] = useState(false);
	const [showAPIModal, setShowAPIModal] = useState(false);
	const [currentAPIModalTab, setCurrentAPIModalTab] = useState('');
	const [user, setUser] = useState(null);
	const [error, setError] = useState('');
	const [generatedContent, setGeneratedContent] = useState(null);
	const [verbIndex, setVerbIndex] = useState(0);

	const isLicensed = EASYCOMMERCE.pro.activated && EASYCOMMERCE.pro.licensed;

	const generatingVerbs = [
		__('Imagining', 'easycommerce'),
		__('Sketching', 'easycommerce'),
		__('Painting', 'easycommerce'),
		__('Rendering', 'easycommerce'),
		__('Coloring', 'easycommerce'),
		__('Polishing', 'easycommerce'),
	];

	const ratioOptions = [
		{ value: '1024x1024', label: __('1024x1024 Pixels', 'easycommerce') },
		{ value: '1792x1024', label: __('1792x1024 Pixels', 'easycommerce') },
		{ value: '1024x1792', label: __('1024x1792 Pixels', 'easycommerce') },
	];

	const [ratio, setRatio] = useState(ratioOptions[0]);

	const formatOptions = [
		{ value: 'standard', label: __('Standard', 'easycommerce') },
		{ value: 'hd', label: __('HD', 'easycommerce') },
	];

	const [format, setFormat] = useState(formatOptions[0]);

	const openFancybox = useFancybox();

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

	useEffect(() => {
		if (!isLoading) {
			setVerbIndex(0);
			return;
		}

		const interval = setInterval(() => {
			setVerbIndex((prev) => (prev + 1) % generatingVerbs.length);
		}, 7000);

		return () => clearInterval(interval);
	}, [isLoading]);

	const handleModalClose = () => {
		setShowAPIModal(false);
		setAiOpen(false);
		setCurrentAPIModalTab('');
	};

	const handleSend = async () => {
		if (input.trim() === '') return;
		setIsLoading(true);
		setError('');
		try {
			await sendToAi(input);
			setError('');
		} catch (error) {
			setError(
				error.message ||
					__('An error occurred. Please try again.', 'easycommerce'),
			);
		} finally {
			setIsLoading(false);
		}
	};

	const sendToAi = async (query) => {
		const imageRequestBody = {
			prompt: query,
			size: ratio.value,
			product: productTitle,
			quality: format.value,
		};

		try {
			const response = await fetch(`${EASYCOMMERCE.rest_base}/ai/draw`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': EASYCOMMERCE.nonce,
				},
				body: JSON.stringify({
					...imageRequestBody,
				}),
			});

			const data = await response.json();

			if (!response.ok) {
				toast.error(data.data.message);
				throw new Error(data.data.message);
			}

			var responseData = data.data.url;

			if (data && responseData) {
				setGeneratedContent(responseData);
				setError('');
				
				if (EASYCOMMERCE.credits !== '') {
					EASYCOMMERCE.credits -= 10
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
			console.error('Error generating image:', error);
			throw error;
		}
	};

	const handleChoose = async () => {
		setIsImporting(true);
		try {
			if (!generatedContent) {
				throw new Error(
					__('No generated image to choose from.', 'easycommerce'),
				);
			}

			const res = await fetch(`${EASYCOMMERCE.rest_base}/importer/sideload`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': EASYCOMMERCE.nonce,
				},
				body: JSON.stringify({
					url: generatedContent,
				}),
			});

			const data = await res.json();

			if (!res.ok) {
				throw new Error(
					data.data?.message ||
						__('Failed to choose image. Please try again.', 'easycommerce'),
				);
			}

			setAiContent(data.data.imported);
		} catch (error) {
			console.error('Error choosing image:', error);
		} finally {
			setIsImporting(false);
			setAiOpen(false);
		}
	};

	return user || isLicensed ? (
		<div className="font-inter backdrop-blur-[10px] fixed top-0 left-0 w-full h-full bg-[#00000063] z-[9999]">
			<div className="fixed w-[870px] top-[90px] left-[50%] -translate-x-1/2 flex flex-col transition-height duration-300 ease-in-out h-max">
				<div className="px-6 py-4 flex items-center rounded-t-xl justify-between border-b border-ec-table-stock bg-white">
					<div className="flex items-center gap-3">
						{aiIcon}
						<div>
							<h2 className="text-base text-ec-allText">
								{__('AI Image Generator', 'easycommerce')}
							</h2>
							<p className="text-sm text-ec-light-black font-normal">
								{__(
									'Describe what you want to see in the image',
									'easycommerce',
								)}
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
				<div className="grow w-full rounded-b-xl bg-white p-6">
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

					<div className="grid grid-cols-2 gap-5">
						<div
							className="flex flex-col gap-4"
						>
							<div>
								<label
									htmlFor="aitextarea"
									className="text-base font-medium text-ec-title mb-2.5 block"
								>
									{__('Instruction', 'easycommerce')}
								</label>

								<textarea
									className="h-[183px] p-4 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out w-full disabled:cursor-not-allowed disabled:bg-gray-100"
									id="aitextarea"
									rows="4"
									placeholder={__(
										'Please type your instructions here',
										'easycommerce',
									)}
									value={input}
									onChange={(e) => setInput(e.target.value)}
									disabled={
										isLoading || String(EASYCOMMERCE.credits) === '0'
									}
								/>
								<div
									className={twMerge(
										'w-full grid grid-cols-2 gap-6',
										EASYCOMMERCE.credits === '0' &&
											'opacity-60 pointer-events-none',
									)}
								>
									<div className="flex flex-col">
										<label className="text-sm text-ec-body mb-2.5 block">
											{__('Image Size', 'easycommerce')}
										</label>
										<Dropdown
											options={ratioOptions}
											value={ratio.value}
											onChange={(value) => setRatio(value)}
										/>
									</div>

									<div className="flex flex-col">
										<label className="text-sm text-ec-body mb-2.5 block">
											{__('Quality', 'easycommerce')}
										</label>
										<Dropdown
											options={formatOptions}
											value={format.value}
											onChange={(value) => setFormat(value)}
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
								<button
									className={twMerge(
										'ml-auto flex items-center gap-4 text-[14px] justify-center w-full h-ec-input rounded-[8px] text-white',
										String(EASYCOMMERCE.credits) === '0'
											? 'ai-builder-disable cursor-not-allowed'
											: 'ai-builder',
										isLoading || isImporting
											? 'bg-gray-400 cursor-not-allowed'
											: '',
									)}
									onClick={handleSend}
									type="button"
									disabled={
										isLoading ||
										isImporting ||
										!input.trim() ||
										String(EASYCOMMERCE.credits) === '0'
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
							{EASYCOMMERCE.credits !== '' && (
								<h2 className="text-sm font-normal">
									{Number(EASYCOMMERCE.credits) > 0 ? (
										<AiCredits usage={50} />
									) : (
										<span className="text-ec-light-black">
											{__('No credits available', 'easycommerce')}
										</span>
									)}
								</h2>
							)}
						</div>

						<div className="mt-[33px]">
							{generatedContent ? (
								<div
									className="rounded-lg relative flex flex-col items-center overflow-hidden justify-center gap-3 w-[401px] h-[319px]"
									style={{
										backgroundImage: `url(${generatedContent})`,
										backgroundSize: 'cover',
										backgroundPosition: 'center',
									}}
								>
									<button
										onClick={(e) => {
											e.preventDefault();
											openFancybox(generatedContent)
										}}
										className="absolute bg-white rounded-full w-10 h-10 flex items-center justify-center top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"
									>
										<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M13.125 0H9.40625C9.29022 0 9.17894 0.0460936 9.09689 0.128141C9.01484 0.210188 8.96875 0.321468 8.96875 0.4375C8.96875 0.553532 9.01484 0.664812 9.09689 0.746859C9.17894 0.828906 9.29022 0.875 9.40625 0.875H12.033L8.01041 4.71472C7.96884 4.75439 7.93549 4.80186 7.91227 4.85442C7.88905 4.90698 7.87641 4.9636 7.87507 5.02104C7.87373 5.07849 7.88371 5.13563 7.90446 5.18922C7.9252 5.2428 7.9563 5.29178 7.99597 5.33334C8.03564 5.37491 8.08311 5.40826 8.13567 5.43148C8.18823 5.4547 8.24485 5.46734 8.30229 5.46868C8.35974 5.47002 8.41688 5.46004 8.47047 5.43929C8.52405 5.41855 8.57303 5.38745 8.61459 5.34778L12.6875 1.45994V4.15625C12.6875 4.27228 12.7336 4.38356 12.8156 4.46561C12.8977 4.54766 13.009 4.59375 13.125 4.59375C13.241 4.59375 13.3523 4.54766 13.4344 4.46561C13.5164 4.38356 13.5625 4.27228 13.5625 4.15625V0.4375C13.5625 0.321468 13.5164 0.210188 13.4344 0.128141C13.3523 0.0460936 13.241 0 13.125 0ZM13.125 8.96875C13.009 8.96875 12.8977 9.01484 12.8156 9.09689C12.7336 9.17894 12.6875 9.29022 12.6875 9.40625V12.0689L8.62181 8.00319C8.5393 7.92349 8.42879 7.8794 8.31407 7.88039C8.19936 7.88139 8.08963 7.9274 8.00852 8.00852C7.9274 8.08963 7.88139 8.19936 7.88039 8.31407C7.8794 8.42879 7.92349 8.5393 8.00319 8.62181L12.0689 12.6875H9.40625C9.29022 12.6875 9.17894 12.7336 9.09689 12.8156C9.01484 12.8977 8.96875 13.009 8.96875 13.125C8.96875 13.241 9.01484 13.3523 9.09689 13.4344C9.17894 13.5164 9.29022 13.5625 9.40625 13.5625H13.125C13.241 13.5625 13.3523 13.5164 13.4344 13.4344C13.5164 13.3523 13.5625 13.241 13.5625 13.125V9.40625C13.5625 9.29022 13.5164 9.17894 13.4344 9.09689C13.3523 9.01484 13.241 8.96875 13.125 8.96875ZM4.72194 8.22194L0.875 12.0689V9.40625C0.875 9.29022 0.828906 9.17894 0.746859 9.09689C0.664812 9.01484 0.553532 8.96875 0.4375 8.96875C0.321468 8.96875 0.210188 9.01484 0.128141 9.09689C0.0460936 9.17894 0 9.29022 0 9.40625V13.125C0 13.241 0.0460936 13.3523 0.128141 13.4344C0.210188 13.5164 0.321468 13.5625 0.4375 13.5625H4.15625C4.27228 13.5625 4.38356 13.5164 4.46561 13.4344C4.54766 13.3523 4.59375 13.241 4.59375 13.125C4.59375 13.009 4.54766 12.8977 4.46561 12.8156C4.38356 12.7336 4.27228 12.6875 4.15625 12.6875H1.49363L5.34056 8.84056C5.42026 8.75805 5.46435 8.64754 5.46336 8.53282C5.46236 8.41811 5.41635 8.30838 5.33523 8.22727C5.25412 8.14615 5.14439 8.10014 5.02968 8.09914C4.91496 8.09815 4.80445 8.14224 4.72194 8.22194ZM1.49363 0.875H4.15625C4.27228 0.875 4.38356 0.828906 4.46561 0.746859C4.54766 0.664812 4.59375 0.553532 4.59375 0.4375C4.59375 0.321468 4.54766 0.210188 4.46561 0.128141C4.38356 0.0460936 4.27228 0 4.15625 0H0.4375C0.321468 0 0.210188 0.0460936 0.128141 0.128141C0.0460936 0.210188 0 0.321468 0 0.4375V4.15625C0 4.27228 0.0460936 4.38356 0.128141 4.46561C0.210188 4.54766 0.321468 4.59375 0.4375 4.59375C0.553532 4.59375 0.664812 4.54766 0.746859 4.46561C0.828906 4.38356 0.875 4.27228 0.875 4.15625V1.49363L4.72194 5.34056C4.80445 5.42026 4.91496 5.46435 5.02968 5.46336C5.14439 5.46236 5.25412 5.41635 5.33523 5.33523C5.41635 5.25412 5.46236 5.14439 5.46336 5.02968C5.46435 4.91496 5.42026 4.80445 5.34056 4.72194L1.49363 0.875Z" fill="#121216"/>
										</svg>
									</button>
									<button
										className="easycommerce-primary-button w-[143px] h-[41px] flex items-center justify-center absolute bottom-4 left-1/2 -translate-x-1/2 disabled:cursor-not-allowed disabled:opacity-80"
										onClick={(e) => {
											e.preventDefault();
											handleChoose();
										}}
										disabled={isImporting}
									>
										{isImporting
											? __('Importing...', 'easycommerce')
											: __('Use This Image', 'easycommerce')}
									</button>
								</div>
							) : isLoading ? (
								<div className="relative overflow-hidden border border-solid border-ec-table-stock rounded-lg flex flex-col items-center justify-center gap-3 h-full bg-[linear-gradient(135deg,#F3F0FF_0%,#FAF9FF_50%,#EDE8FF_100%)] animate-pulse">
									<svg
										width="44"
										height="44"
										viewBox="0 0 44 44"
										fill="none"
										xmlns="http://www.w3.org/2000/svg"
										className="relative z-10 animate-bounce"
									>
										<path
											d="M1.854 31.516L13.19 20.18C14.1171 19.2532 15.3743 18.7325 16.6853 18.7325C17.9962 18.7325 19.2534 19.2532 20.1805 20.18L31.5165 31.516M26.5728 26.5723L30.4931 22.6519C31.4202 21.7251 32.6775 21.2044 33.9884 21.2044C35.2993 21.2044 36.5565 21.7251 37.4836 22.6519L41.404 26.5723M26.5728 11.741H26.5975M6.79775 41.4035H36.4603C37.7714 41.4035 39.0289 40.8827 39.956 39.9555C40.8831 39.0284 41.404 37.7709 41.404 36.4598V6.79727C41.404 5.4861 40.8831 4.22864 39.956 3.30151C39.0289 2.37437 37.7714 1.85352 36.4603 1.85352H6.79775C5.48659 1.85352 4.22913 2.37437 3.30199 3.30151C2.37486 4.22864 1.854 5.4861 1.854 6.79727V36.4598C1.854 37.7709 2.37486 39.0284 3.30199 39.9555C4.22913 40.8827 5.48659 41.4035 6.79775 41.4035Z"
											stroke="#7351FD"
											strokeWidth="3.70781"
											strokeLinecap="round"
											strokeLinejoin="round"
										/>
									</svg>

									<p className="relative z-10 flex items-center gap-1 text-ec-primary text-center text-sm font-medium">
										{generatingVerbs[verbIndex]}
										<span className="animate-pulse">…</span>
									</p>
								</div>
							) : (
								<div className="border border-solid border-ec-table-stock rounded-lg flex flex-col items-center justify-center gap-3 h-full">
									<svg
										width="44"
										height="44"
										viewBox="0 0 44 44"
										fill="none"
										xmlns="http://www.w3.org/2000/svg"
									>
										<path
											d="M1.854 31.516L13.19 20.18C14.1171 19.2532 15.3743 18.7325 16.6853 18.7325C17.9962 18.7325 19.2534 19.2532 20.1805 20.18L31.5165 31.516M26.5728 26.5723L30.4931 22.6519C31.4202 21.7251 32.6775 21.2044 33.9884 21.2044C35.2993 21.2044 36.5565 21.7251 37.4836 22.6519L41.404 26.5723M26.5728 11.741H26.5975M6.79775 41.4035H36.4603C37.7714 41.4035 39.0289 40.8827 39.956 39.9555C40.8831 39.0284 41.404 37.7709 41.404 36.4598V6.79727C41.404 5.4861 40.8831 4.22864 39.956 3.30151C39.0289 2.37437 37.7714 1.85352 36.4603 1.85352H6.79775C5.48659 1.85352 4.22913 2.37437 3.30199 3.30151C2.37486 4.22864 1.854 5.4861 1.854 6.79727V36.4598C1.854 37.7709 2.37486 39.0284 3.30199 39.9555C4.22913 40.8827 5.48659 41.4035 6.79775 41.4035Z"
											stroke="#99A1AF"
											strokeWidth="3.70781"
											strokeLinecap="round"
											strokeLinejoin="round"
										/>
									</svg>

									<p className="text-ec-placeholder text-center text-sm">
										{__(
											'Generated image will appear here once created.',
											'easycommerce',
										)}
									</p>
								</div>
							)}
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

export default AiGenerateImages;
