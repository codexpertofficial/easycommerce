import { useState, useEffect } from '@wordpress/element';
import { dispatch, useSelect } from '@wordpress/data';
import Cookies from 'universal-cookie';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { twMerge } from "tailwind-merge";
import { applyFilters } from '@wordpress/hooks';
import { Slot } from '@wordpress/components';
import AiCredits from '../common/components/AiCredits.jsx';
import APIScreen from '../common/components/APIScreen/index.jsx';
import APIVarification from '../common/components/APIScreen/elements/APIVarification.jsx';
import APICreateForm from '../common/components/APIScreen/elements/APICreateForm.jsx';

const EditorApp = () => {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [content, setContent] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState('');
    const [user, setUser] = useState(null);
	const [showAPIModal, setShowAPIModal] = useState(false);
	const [currentAPIModalTab, setCurrentAPIModalTab] = useState('');

    const isLicensed = EASYCOMMERCE.pro.activated && EASYCOMMERCE.pro.licensed;

    const { postId, postType } = useSelect( ( select ) => {
        const editor = select('core/editor');
        return {
            postId: editor.getCurrentPostId(),
            postType: editor.getCurrentPostType(),
        };
    }, [] );

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
        // Insert the button into the WordPress editor header
        const insertButton = () => {
            const pinnedItems = document.querySelector('.interface-pinned-items');
            if ('product' === postType && pinnedItems && !document.getElementById('ai-content-generator-btn')) {
                const button = document.createElement('button');
                button.id = 'ai-content-generator-btn';
                button.type = 'button';
                button.className = 'components-button is-compact has-icon !w-fit';
                button.setAttribute('aria-label', __('AI Template Builder', 'easycommerce'));
                button.innerHTML = `
                    <div class="ai-builder flex items-center gap-2 justify-center w-[123px] h-ec-input rounded-[8px]">
                        <svg width="512" height="510" viewBox="0 0 512 510" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M212.704 118.977L225.552 154.657C232.602 174.217 243.88 191.981 258.582 206.683C273.284 221.385 291.048 232.663 310.608 239.713L346.288 252.561C346.993 252.819 347.601 253.288 348.031 253.903C348.461 254.518 348.691 255.25 348.691 256.001C348.691 256.751 348.461 257.483 348.031 258.099C347.601 258.714 346.993 259.182 346.288 259.441L310.608 272.289C291.048 279.339 273.284 290.616 258.582 305.318C243.88 320.02 232.602 337.785 225.552 357.345L212.704 393.025C212.446 393.729 211.978 394.337 211.362 394.767C210.747 395.197 210.015 395.427 209.264 395.427C208.514 395.427 207.782 395.197 207.166 394.767C206.551 394.337 206.083 393.729 205.824 393.025L192.976 357.345C185.927 337.785 174.649 320.02 159.947 305.318C145.245 290.616 127.481 279.339 107.92 272.289L72.2404 259.441C71.5359 259.182 70.9277 258.714 70.498 258.099C70.0683 257.483 69.8379 256.751 69.8379 256.001C69.8379 255.25 70.0683 254.518 70.498 253.903C70.9277 253.288 71.5359 252.819 72.2404 252.561L107.92 239.713C127.481 232.663 145.245 221.385 159.947 206.683C174.649 191.981 185.927 174.217 192.976 154.657L205.824 118.977C206.076 118.265 206.541 117.649 207.157 117.213C207.774 116.777 208.51 116.543 209.264 116.543C210.019 116.543 210.755 116.777 211.371 117.213C211.987 117.649 212.453 118.265 212.704 118.977ZM373.312 33.2327L379.824 51.2967C383.397 61.2003 389.109 70.1945 396.554 77.6391C403.999 85.0838 412.993 90.7958 422.896 94.3687L440.96 100.881C441.318 101.011 441.627 101.248 441.846 101.56C442.064 101.872 442.181 102.244 442.181 102.625C442.181 103.006 442.064 103.377 441.846 103.689C441.627 104.001 441.318 104.238 440.96 104.369L422.896 110.881C412.993 114.454 403.999 120.166 396.554 127.61C389.109 135.055 383.397 144.049 379.824 153.953L373.312 172.017C373.182 172.375 372.945 172.684 372.633 172.902C372.321 173.121 371.949 173.238 371.568 173.238C371.188 173.238 370.816 173.121 370.504 172.902C370.192 172.684 369.955 172.375 369.824 172.017L363.312 153.953C359.74 144.049 354.027 135.055 346.583 127.61C339.138 120.166 330.144 114.454 320.24 110.881L302.176 104.369C301.819 104.238 301.509 104.001 301.291 103.689C301.073 103.377 300.955 103.006 300.955 102.625C300.955 102.244 301.073 101.872 301.291 101.56C301.509 101.248 301.819 101.011 302.176 100.881L320.24 94.3687C330.144 90.7958 339.138 85.0838 346.583 77.6391C354.027 70.1945 359.74 61.2003 363.312 51.2967L369.824 33.2327C369.955 32.8749 370.192 32.5657 370.504 32.3473C370.816 32.1289 371.188 32.0117 371.568 32.0117C371.949 32.0117 372.321 32.1289 372.633 32.3473C372.945 32.5657 373.182 32.8749 373.312 33.2327ZM373.312 340.001L379.824 358.065C383.397 367.968 389.109 376.962 396.554 384.407C403.999 391.852 412.993 397.564 422.896 401.137L440.96 407.649C441.318 407.779 441.627 408.016 441.846 408.328C442.064 408.64 442.181 409.012 442.181 409.393C442.181 409.774 442.064 410.145 441.846 410.457C441.627 410.769 441.318 411.006 440.96 411.137L422.896 417.649C412.993 421.222 403.999 426.934 396.554 434.378C389.109 441.823 383.397 450.817 379.824 460.721L373.312 478.785C373.182 479.143 372.945 479.452 372.633 479.67C372.321 479.889 371.949 480.006 371.568 480.006C371.188 480.006 370.816 479.889 370.504 479.67C370.192 479.452 369.955 479.143 369.824 478.785L363.312 460.721C359.74 450.817 354.027 441.823 346.583 434.378C339.138 426.934 330.144 421.222 320.24 417.649L302.176 411.137C301.819 411.006 301.509 410.769 301.291 410.457C301.073 410.145 300.955 409.774 300.955 409.393C300.955 409.012 301.073 408.64 301.291 408.328C301.509 408.016 301.819 407.779 302.176 407.649L320.24 401.137C330.144 397.564 339.138 391.852 346.583 384.407C354.027 376.962 359.74 367.968 363.312 358.065L369.824 340.001C370.416 338.369 372.736 338.369 373.312 340.001Z" fill="#8533FF"/>
                        </svg>
                        ${ __('AI Assistant', 'easycommerce') }
                    </div>
                `;
                button.addEventListener('click', () => setIsModalOpen(true));
                
                // Insert as first child
                pinnedItems.insertBefore(button, pinnedItems.firstChild);
            }
        };

        // Try to insert immediately
        insertButton();

        // Also try after a short delay in case DOM isn't ready
        const timeoutId = setTimeout(insertButton, 100);

        // Observer to handle dynamic content
        const observer = new MutationObserver(() => {
            insertButton();
        });

        const targetNode = document.querySelector('.edit-post-header') || document.body;
        observer.observe(targetNode, {
            childList: true,
            subtree: true
        });

        return () => {
            clearTimeout(timeoutId);
            observer.disconnect();
            const existingButton = document.getElementById('ai-content-generator-btn');
            if (existingButton) {
                existingButton.remove();
            }
        };
    }, [ postType ]);

    /**
     * Filters the AI design request data.
     *
     * @since 1.0.0
     * @param {Object} requestData The request data object.
     */
    const requestData = applyFilters('easycommerce.editor.ai.request', { prompt: content.trim(), product: postId });

    const handleSubmit = async () => {
        if (!content.trim()) return;
        setIsLoading(true);
        setError('');

        try {
            const result = await apiFetch({
                path: '/easycommerce/v1/ai/design',
                method: 'POST',
                data: requestData
            });

            if (result && result.data && result.data.message) {
                dispatch('core/editor').editPost({ content: result.data.message });
                setIsModalOpen(false);
                setContent('');
                setError('');
                
                if (EASYCOMMERCE.credits !== '') {
                    EASYCOMMERCE.credits = String(EASYCOMMERCE.credits) - 1;
                }
            } else {
                throw new Error(__('Invalid response from AI service. Please try again.', 'easycommerce'));
            }
        } catch (error) {
            const errorMessage = error.data.message || __('Failed to generate content. Please try again.', 'easycommerce');
            setError(errorMessage);
        } finally {
            setIsLoading(false);
        }
    };

    const handleCancel = () => {
        setIsModalOpen(false);
        setContent('');
        setError('');
    };

    const handleModalClose = () => {
		setCurrentAPIModalTab('');
        setIsModalOpen(false);
		setContent('');
		setError('');
	};

    // SVG Icons matching the design
    const aiIcon = (
        <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="44" height="44" rx="8" fill="url(#paint0_linear_4128_3475)"/>
            <path d="M29.4573 24.4327C24.2713 26.1461 22.5443 27.8734 20.8309 33.0591C20.69 33.4855 20.0871 33.4855 19.9462 33.0591C18.2328 27.8731 16.5055 26.1462 11.3197 24.4327C10.8934 24.2918 10.8934 23.6889 11.3197 23.548C16.5058 21.8346 18.2327 20.1073 19.9462 14.9216C20.0871 14.4952 20.69 14.4952 20.8309 14.9216C22.5443 20.1076 24.2716 21.8345 29.4573 23.548C29.8837 23.6889 29.8837 24.2918 29.4573 24.4327Z" fill="white"/>
            <path d="M33.2142 15.9161C30.6218 16.7722 29.7577 17.6363 28.9004 20.2298C28.8305 20.443 28.5291 20.443 28.458 20.2298C27.6019 17.6374 26.7378 16.7734 24.1443 15.9161C23.9311 15.8461 23.9311 15.5447 24.1443 15.4737C26.7367 14.6175 27.6007 13.7534 28.458 11.1599C28.528 10.9467 28.8294 10.9467 28.9004 11.1599C29.7566 13.7523 30.6207 14.6164 33.2142 15.4737C33.4274 15.5436 33.4274 15.845 33.2142 15.9161Z" fill="white"/>
            <defs>
                <linearGradient id="paint0_linear_4128_3475" x1="22" y1="0" x2="22" y2="44" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#7351FD"/>
                    <stop offset="1" stop-color="#A33BFF"/>
                </linearGradient>
            </defs>
        </svg>
    );

    const generateIcon = (
        <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M13.9235 10.4935C10.1518 11.7396 8.89589 12.9958 7.64976 16.7673C7.54724 17.0773 7.1088 17.0773 7.0063 16.7673C5.76021 12.9956 4.50401 11.7396 0.732545 10.4935C0.422485 10.391 0.422485 9.95255 0.732545 9.85005C4.50422 8.60396 5.76017 7.34776 7.0063 3.5763C7.10882 3.26623 7.54725 3.26623 7.64976 3.5763C8.89585 7.34797 10.1521 8.60392 13.9235 9.85005C14.2336 9.95257 14.2336 10.391 13.9235 10.4935Z" fill="white"/>
            <path d="M16.6579 4.29967C14.7725 4.92231 14.1441 5.55078 13.5206 7.43698C13.4698 7.59201 13.2506 7.59201 13.1989 7.43698C12.5763 5.55159 11.9478 4.92317 10.0616 4.29967C9.90655 4.24883 9.90655 4.02962 10.0616 3.97794C11.947 3.3553 12.5754 2.72684 13.1989 0.840639C13.2497 0.685607 13.4689 0.685607 13.5206 0.840639C14.1433 2.72603 14.7717 3.35445 16.6579 3.97794C16.813 4.02879 16.813 4.24799 16.6579 4.29967Z" fill="white"/>
        </svg>
    );

    const upgradeIcon = (
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="20" height="20" rx="6" fill="#FF3A52"/>
            <path d="M9.40042 9.59935C9.36441 9.58975 9.33321 9.58135 9.30081 9.57415C9.15539 9.55129 9.02412 9.47395 8.93363 9.35784C8.84315 9.24172 8.80023 9.09553 8.8136 8.94893C8.82464 8.79839 8.89303 8.65781 9.00467 8.55622C9.11632 8.45462 9.2627 8.39975 9.41362 8.40292C9.58402 8.40292 9.75562 8.41372 9.92483 8.40292C10.2572 8.37652 10.586 8.60692 10.5824 9.06294C10.5824 9.60055 10.5824 10.143 10.5824 10.6758C10.5824 11.2086 10.5824 11.7426 10.5824 12.2754C10.5824 12.3126 10.5824 12.3498 10.5824 12.3954L10.6904 12.4194C10.8357 12.4459 10.9659 12.5256 11.0554 12.6429C11.145 12.7602 11.1876 12.9068 11.1749 13.0539C11.1621 13.201 11.0949 13.338 10.9865 13.4382C10.878 13.5383 10.7361 13.5944 10.5884 13.5954C10.1884 13.6018 9.78843 13.6018 9.38842 13.5954C9.24062 13.5974 9.09745 13.5439 8.98709 13.4456C8.87674 13.3473 8.80719 13.2112 8.79214 13.0641C8.77709 12.9171 8.81763 12.7697 8.90577 12.6511C8.99392 12.5324 9.12329 12.4511 9.26841 12.423C9.30441 12.4146 9.34161 12.4086 9.38842 12.399V9.59935H9.40042Z" fill="white"/>
            <path d="M9.00782 7.19481C9.01126 6.98395 9.09704 6.78279 9.24684 6.63435C9.39663 6.4859 9.59855 6.40193 9.80944 6.40039C10.0174 6.40633 10.2149 6.49314 10.3599 6.64239C10.5049 6.79163 10.5859 6.99154 10.5859 7.19961C10.5855 7.30323 10.5648 7.40577 10.5249 7.50137C10.4849 7.59698 10.4266 7.68378 10.3531 7.75683C10.2796 7.82987 10.1924 7.88773 10.0966 7.92709C10.0007 7.96645 9.89805 7.98655 9.79444 7.98623C9.69082 7.98592 9.58828 7.96519 9.49267 7.92525C9.39707 7.88531 9.31026 7.82693 9.23722 7.75343C9.16417 7.67994 9.10632 7.59279 9.06696 7.49694C9.0276 7.40109 9.0075 7.29843 9.00782 7.19481Z" fill="white"/>
            <path d="M10.0194 4.00003C13.3135 4.01083 16.0196 6.7193 16.0004 9.99058C15.9994 10.7807 15.8429 11.563 15.5396 12.2926C15.2363 13.0223 14.7923 13.685 14.233 14.2431C13.6736 14.8012 13.0097 15.2436 12.2794 15.5451C11.549 15.8466 10.7664 16.0013 9.97622 16.0003C6.67973 16.0063 3.98447 13.2775 4.00007 9.94738C4.01401 8.36188 4.65495 6.84635 5.78284 5.73195C6.91073 4.61756 8.43386 3.9949 10.0194 4.00003ZM10.047 5.13526C9.40581 5.12907 8.76974 5.25009 8.17559 5.4913C7.58145 5.73252 7.04104 6.08915 6.58561 6.54056C6.13018 6.99197 5.76878 7.52921 5.52231 8.12119C5.27584 8.71317 5.14919 9.34814 5.1497 9.98938C5.1197 12.6402 7.31935 14.8531 9.97742 14.8591C10.6185 14.8605 11.2535 14.7351 11.8458 14.4899C12.4382 14.2448 12.9761 13.8848 13.4287 13.4308C13.8813 12.9768 14.2395 12.4377 14.4828 11.8446C14.726 11.2514 14.8494 10.616 14.8459 9.97498C14.8303 7.31692 12.6703 5.13886 10.047 5.13526Z" fill="white"/>
        </svg>
    )

    if (!isModalOpen) return null;

    return (
		<div className="font-inter backdrop-blur-[10px] fixed top-0 left-0 w-full h-full bg-[#00000063] z-[9999]">
			{user || isLicensed ? (
				<div
					className={twMerge(
						'fixed w-[660px] top-[90px] left-[50%] -translate-x-1/2 rounded-xl overflow-hidden flex flex-col transition-height duration-300 ease-in-out',
						error && String(EASYCOMMERCE.credits) === '0'
							? 'h-[532px]'
							: error
								? 'h-[472px]'
								: String(EASYCOMMERCE.credits) === '0'
									? 'h-[488px]'
									: 'h-[412px]',
					)}
				>
					<div className="px-6 py-4 flex items-center justify-between border-b border-ec-table-stock bg-white">
						<div className="flex items-center gap-3">
							{aiIcon}
							<div>
								<h2 className="text-base text-ec-allText">
									{__('AI Template Builder', 'easycommerce')}
								</h2>
								<p className="text-sm text-ec-light-black font-normal">
									{__('Describe what you want to build', 'easycommerce')}
								</p>
							</div>
						</div>
						<button onClick={handleCancel}>
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
										href="#"
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
								<label
									htmlFor="aitextarea"
									className="text-base font-medium text-ec-title mb-2.5 block"
								>
									{__('Instruction', 'easycommerce')}
								</label>
								<textarea
									className="h-[183px] p-4 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out w-full"
									id="aitextarea"
									rows="4"
									placeholder={__(
										'Example: Make it 2 columns with a big gallery section at left.',
										'easycommerce',
									)}
									value={content}
									onChange={(e) => setContent(e.target.value)}
								/>

								{/* Slot for additional modal content */}
								<Slot
									name="easycommerce.editor.ai.modal.extra"
									props={{ content, postId }}
								/>
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
									className={twMerge(
										'ml-auto flex items-center gap-4 text-[14px] justify-center w-[20%] h-ec-input rounded-[8px] text-white',
										String(EASYCOMMERCE.credits) === '0'
											? 'ai-builder-disable cursor-not-allowed'
											: 'ai-builder',
										isLoading ? 'bg-gray-400 cursor-not-allowed' : '',
									)}
									onClick={handleSubmit}
									type="button"
									// disabled={isLoading || !content.trim() || (!isECPro && EASYCOMMERCE.credits <= 0)}
								>
									{isLoading ? (
										__('Building...', 'easycommerce')
									) : (
										<>
											{generateIcon}
											{__('Build', 'easycommerce')}
										</>
									)}
								</button>
							</div>
						</div>
					</div>
				</div>
			) : showAPIModal && currentAPIModalTab === '' ? (
				<APIScreen
					onClose={handleModalClose}
					switchVariationModalTab={() =>
						setCurrentAPIModalTab('apiVarification')
					}
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
			) : null}
		</div>
	);
};

export default EditorApp;
