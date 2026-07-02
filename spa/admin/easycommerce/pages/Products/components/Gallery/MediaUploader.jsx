import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
const loader = `${EASYCOMMERCE.assets}admin/img/loader.gif`;
import './style.css';
import AiEnhanceImage from '../common/AiGenerate/AiEnhanceImage';

const MediaUploader = ({ wpNonce, prevFiles, prevThumbnail, aiImage }) => {
	const [isDragging, setIsDragging] = useState(false);
	const [uploading, setUploading] = useState(false);
	const [uploadedImages, setUploadedImages] = useState(prevFiles || []);
	const [selectedThumbnail, setSelectedThumbnail] = useState(prevThumbnail?.id || null);
	const [uploadCount, setUploadCount] = useState({ current: 0, total: 0 });
	const [enhanceOpen, setEnhanceOpen] = useState(null);

	useEffect(() => {
		if (!window.wp || !window.wp.media) {
			console.error(
				'wp.media is not available. Make sure you enqueue media scripts.'
			);
		}
	}, []);

	useEffect(() => {
		aiImage && setUploadedImages((prev) => [...prev, { ...aiImage, id: aiImage.attachment_id }]);
	}, [aiImage]);

	const handleDrop = async (e) => {
		e.preventDefault();
		setIsDragging(false);

		const files = Array.from(e.dataTransfer.files).filter((file) =>
			file.type.startsWith('image/')
		);

		if (files.length === 0) return;

		setUploading(true);
		setUploadCount({ current: 0, total: files.length });

		for (let i = 0; i < files.length; i++) {
			const file = files[i];
			const formData = new FormData();
			formData.append('file', file);

			try {
				const response = await fetch('/wp-json/wp/v2/media', {
					method: 'POST',
					headers: {
						'X-WP-Nonce': wpNonce,
						'Content-Disposition': `attachment; filename="${file.name}"`,
					},
					body: formData,
				});

				const result = await response.json();
				if (response.ok) {
					setUploadedImages((prev) => { 
						const updated = [...prev, { id: result.id, url: result.source_url }]
						if (!selectedThumbnail && updated.length > 0) {
							setSelectedThumbnail(result.id);
						}
						return updated;
					});
				} else {
					console.error('Upload failed:', result);
				}
			} catch (error) {
				console.error('Upload error:', error);
			}

			// Update current count after each file
			setUploadCount((prev) => ({ ...prev, current: prev.current + 1 }));
		}

		setUploading(false);
		setUploadCount({ current: 0, total: 0 });
	};

	const handleDragOver = (e) => {
		e.preventDefault();
		setIsDragging(true);
	};

	const handleDragLeave = () => setIsDragging(false);

	const handleClick = () => {
		if (!window.wp || !window.wp.media) return;

		const mediaFrame = window.wp.media({
			title: 'Select Images',
			button: { text: 'Insert' },
			multiple: true,
			library: {
				type: 'image',
			},
		});

		mediaFrame.on('open', () => {
			const selection = mediaFrame.state().get('selection');
			uploadedImages.forEach((item) => {
				if (item.id) {
					const attachment = wp.media.attachment(item.id);
					if (attachment) {
						attachment.fetch();
						selection.add(attachment);
					}
				}
			});
		});

		mediaFrame.on('select', () => {
			const selection = mediaFrame.state().get('selection');
			const newSelections = [];

			selection.forEach((attachment) => {
				const image = attachment.toJSON();
				if (image.mime && image.mime.startsWith('image/')) {
					const isDuplicate = uploadedImages.some((item) => item.id === image.id);
					if (!isDuplicate) {
						newSelections.push({ id: image.id, url: image.url });
					}
				}
			});

			if (newSelections.length > 0) {
				setUploadedImages((prev) => {
					const updated = [...prev, ...newSelections];
					if (!selectedThumbnail && updated.length > 0) {
						setSelectedThumbnail(updated[0].id);
					}
					return updated;
				});
			}
		});

		mediaFrame.open();
	};

	const closeIcon = (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			width="6"
			height="6"
			viewBox="0 0 6 6"
			fill="none"
		>
			<path
				d="M0.132551 5.16996L2.337 2.96552L0.132551 0.761073C0.0907546 0.719902 0.0575231 0.670862 0.0347735 0.616783C0.0120239 0.562704 0.000206374 0.504656 2.67897e-06 0.445988C-0.000201016 0.387319 0.0112131 0.329191 0.0335867 0.274955C0.0559602 0.22072 0.0888503 0.171451 0.13036 0.12999C0.17187 0.0885289 0.221178 0.0556967 0.275439 0.0333871C0.329701 0.0110775 0.387843 -0.000268032 0.446512 4.80643e-06C0.50518 0.000277645 0.563214 0.0121635 0.617266 0.0349768C0.671318 0.0577901 0.720318 0.0910795 0.76144 0.132925L2.96515 2.33737L5.16959 0.132925C5.21071 0.0910795 5.25971 0.0577901 5.31377 0.0349768C5.36782 0.0121635 5.42585 0.000277645 5.48452 4.80643e-06C5.54319 -0.000268032 5.60133 0.0110775 5.65559 0.0333871C5.70985 0.0556967 5.75916 0.0885289 5.80067 0.12999C5.84218 0.171451 5.87507 0.22072 5.89745 0.274955C5.91982 0.329191 5.93123 0.387319 5.93103 0.445988C5.93083 0.504656 5.91901 0.562704 5.89626 0.616783C5.87351 0.670862 5.84028 0.719902 5.79848 0.761073L3.59329 2.96552L5.79774 5.16996C5.83954 5.21114 5.87277 5.26018 5.89552 5.31425C5.91827 5.36833 5.93008 5.42638 5.93029 5.48505C5.93049 5.54372 5.91908 5.60185 5.8967 5.65608C5.87433 5.71032 5.84144 5.75959 5.79993 5.80105C5.75842 5.84251 5.70911 5.87534 5.65485 5.89765C5.60059 5.91996 5.54245 5.93131 5.48378 5.93103C5.42511 5.93076 5.36708 5.91887 5.31303 5.89606C5.25897 5.87325 5.20997 5.83996 5.16885 5.79811L2.96515 3.59367L0.7607 5.79811C0.67717 5.88039 0.564511 5.92633 0.447263 5.92593C0.330015 5.92552 0.217678 5.8788 0.134722 5.79594C0.0517653 5.71308 0.00491153 5.6008 0.00436627 5.48355C0.00382101 5.3663 0.0496285 5.25359 0.13181 5.16996H0.132551Z"
				className="fill-[#7F7F98] group-hover:fill-white"
			/>
		</svg>
	);

	return (
		<>
			{uploadedImages.length > 0 && (
				<div className="p-4 border border-ec-table-stock rounded-lg mb-4">
					<div className="easycommerce-gallery-container grid grid-cols-2 gap-3">
						{uploadedImages.map((item, i) => {
							const isThumbnail = item.id === selectedThumbnail;

							return (
								<>
									<div
										key={item.id || i}
										className="group relative border rounded-lg border-ec-table-stock mt-2"
									>
										<img
											src={item.url}
											alt={`Uploaded ${i}`}
											className="w-full h-[122px] rounded-lg object-cover"
										/>

										{/* Only show delete button if NOT thumbnail */}
										{!isThumbnail && (
											<button
												type="button"
												onClick={(e) => {
													e.stopPropagation();
													setUploadedImages((prev) =>
														prev.filter((_, index) => index !== i)
													);
												}}
												className="z-50 w-[19px] h-[19px] flex items-center justify-center rounded-full border border-ec-light-black duration-300 group-hover:border-ec-red absolute -top-2 -right-2 bg-white group-hover:bg-ec-red"
											>
												{closeIcon}
											</button>
										)}

										{/* Thumbnail checkbox - always visible if selected */}
										<div
											className={`text-white absolute top-0 left-0 bg-[#0000009f] w-full h-full flex flex-col gap-2.5 items-center justify-center rounded-lg px-2 py-1 text-xs font-medium
											${isThumbnail ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'}
											transition`}
										>
											<label className="flex items-center cursor-pointer gap-1.5">
												<input
													type="checkbox"
													checked={isThumbnail}
													onChange={() =>
														setSelectedThumbnail(isThumbnail ? null : item.id)
													}
													className="mr-1 easycommerce-input-checkoutbox"
												/>
												{__('Set as thumbnail', 'easycommerce')}
											</label>
											
											<button
												onClick={(e) => { 
													e.preventDefault(); 
													setEnhanceOpen(item.id);
												}}
												className='flex items-center gap-1.5'
											>
												<svg
													xmlns="http://www.w3.org/2000/svg"
													xmlnsXlink="http://www.w3.org/1999/xlink"
													fill="#fff"
													height="15px"
													width="15px"
													version="1.1"
													viewBox="0 0 403.893 403.893"
													enableBackground="new 0 0 403.893 403.893"
												>
													<g>
														<path d="m129.339,63.189c5.523,0 10-4.477 10-10v-43.189c0-5.523-4.477-10-10-10s-10,4.477-10,10v43.189c0,5.523 4.477,10 10,10z" />
														<path d="m129.339,195.488c-5.523,0-10,4.477-10,10v43.189c0,5.523 4.477,10 10,10s10-4.477 10-10v-43.189c0-5.523-4.478-10-10-10z" />
														<path d="m52.024,37.883c-3.905-3.905-10.237-3.905-14.142,0s-3.905,10.237 0,14.142l30.539,30.539c1.953,1.953 4.512,2.929 7.071,2.929s5.119-0.976 7.071-2.929c3.905-3.905 3.905-10.237 0-14.142l-30.539-30.539z" />
														<path d="m63.189,129.338c0-5.523-4.477-10-10-10h-43.189c-5.523,0-10,4.477-10,10s4.477,10 10,10h43.189c5.523,0 10-4.477 10-10z" />
														<path d="m195.488,129.338c0,5.523 4.477,10 10,10h43.189c5.523,0 10-4.477 10-10s-4.477-10-10-10h-43.189c-5.523,0-10,4.477-10,10z" />
														<path d="m68.422,176.113l-30.54,30.539c-3.905,3.905-3.906,10.237 0,14.142 1.953,1.953 4.512,2.929 7.071,2.929 2.559,0 5.119-0.977 7.071-2.929l30.54-30.539c3.905-3.905 3.906-10.237 0-14.142s-10.237-3.904-14.142,0z" />
														<path d="m183.184,85.493c2.559,0 5.119-0.977 7.071-2.929l30.539-30.539c3.905-3.905 3.906-10.237 0-14.142s-10.237-3.905-14.142,0l-30.54,30.539c-3.905,3.905-3.906,10.237 0,14.142 1.954,1.953 4.513,2.929 7.072,2.929z" />
														<path d="m400.964,359.951l-256.68-256.68c-11.308-11.307-29.705-11.307-41.013,0-11.307,11.307-11.307,29.706 0,41.013l256.68,256.68c1.953,1.953 4.512,2.929 7.071,2.929s5.119-0.976 7.071-2.929l26.87-26.87c3.906-3.906 3.906-10.238 0.001-14.143zm-283.55-242.537c1.754-1.755 4.059-2.632 6.364-2.632s4.609,0.877 6.364,2.632l40.305,40.305-12.728,12.728-40.305-40.305c-3.51-3.509-3.51-9.219 1.42109e-14-12.728zm249.608,262.336l-195.161-195.161 12.728-12.728 195.161,195.161-12.728,12.728z" />
													</g>
												</svg>
												{__('Enhance With AI', 'easycommerce')}
											</button>
										</div>
									</div>

									{enhanceOpen === item.id && (
										<AiEnhanceImage
											image={item}
											setEnhanceOpen={setEnhanceOpen}
											setEnhancedContent={(content) => {
												setUploadedImages(() => {
													const updatedImages = uploadedImages.filter(img => img.id !== item.id);
													updatedImages.push({ id: content.attachment_id, url: content.url });
													return updatedImages;
												});
												if ((!selectedThumbnail && uploadedImages.length === 0) || selectedThumbnail === item.id) {
													setSelectedThumbnail(content.attachment_id);
												}
											}}
										/>
									)}
								</>
							);
						})}
					</div>
				</div>
			)}

			<div
				onDrop={handleDrop}
				onDragOver={handleDragOver}
				onDragLeave={handleDragLeave}
				className={
					'h-[207px] border-2 border-dashed border-ec-primary rounded-xl flex flex-col items-center justify-center transition-colors' +
					(isDragging ? ' bg-[#7351fd2a]' : ' bg-[#7351FD08]')
				}
			>
				{uploading ? (
					<>
						<img
							src={loader}
							alt="Loading..."
							style={{ width: '50px', height: '50px' }}
						/>
						<p className="text-base text-ec-primary font-inter mt-4">
							{__('Uploading images...', 'easycommerce')} ({uploadCount.current}
							/{uploadCount.total})
						</p>
					</>
				) : (
					<div className="flex flex-col items-center justify-center">
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="37"
							height="33"
							viewBox="0 0 37 33"
							fill="none"
						>
							<path
								d="M22.4895 8.70408C24.1291 8.70408 25.4634 7.36984 25.4634 5.73022C25.4634 4.0906 24.1291 2.75635 22.4895 2.75635C20.8499 2.75635 19.5156 4.0906 19.5156 5.73022C19.5156 7.36984 20.8499 8.70408 22.4895 8.70408ZM22.4895 4.4557C23.1898 4.4557 23.764 5.02991 23.764 5.73022C23.764 6.43052 23.1898 7.00473 22.4895 7.00473C21.7892 7.00473 21.215 6.43052 21.215 5.73022C21.215 5.02991 21.7892 4.4557 22.4895 4.4557Z"
								fill="#7351FD"
							/>
							<path
								d="M28.1762 22.9362C28.0102 22.7703 27.7978 22.6807 27.5821 22.6641C27.5522 22.6591 27.5273 22.6558 27.4975 22.6558C27.4676 22.6558 27.4377 22.6558 27.4128 22.6641C27.1954 22.6856 26.983 22.7703 26.8187 22.9362L24.6016 25.1534C24.4141 25.3409 24.4141 25.6463 24.6016 25.8371C24.7891 26.0246 25.0945 26.0246 25.2853 25.8371L27.0179 24.1046V29.5463C27.0179 29.8135 27.2353 30.0309 27.5024 30.0309C27.7696 30.0309 27.987 29.8135 27.987 29.5463V24.1046L29.7195 25.8371C29.8125 25.93 29.9369 25.9765 30.0597 25.9765C30.1825 25.9765 30.3053 25.93 30.3999 25.8371C30.5875 25.6496 30.5875 25.3442 30.3999 25.1534L28.1828 22.9362H28.1762Z"
								fill="#7351FD"
							/>
							<path
								d="M27.4844 20.1802C24.0857 20.1802 21.3242 22.9416 21.3242 26.3403C21.3242 29.739 24.0857 32.5005 27.4844 32.5005C30.8831 32.5005 33.6445 29.739 33.6445 26.3403C33.6445 22.9416 30.8831 20.1802 27.4844 20.1802ZM27.4844 30.8011C25.0249 30.8011 23.0236 28.7998 23.0236 26.3403C23.0236 23.8809 25.0249 21.8795 27.4844 21.8795C29.9438 21.8795 31.9452 23.8809 31.9452 26.3403C31.9452 28.7998 29.9438 30.8011 27.4844 30.8011Z"
								fill="#7351FD"
							/>
							<path
								d="M34.6472 0.000127625H2.35951C1.42023 0.000127625 0.660156 0.760206 0.660156 1.69948V27.4404C0.660156 28.3797 1.42023 29.1398 2.35951 29.1398H19.5192C19.9872 29.1398 20.3688 28.7581 20.3688 28.2901C20.3688 27.8221 19.9871 27.4404 19.5192 27.4404H6.82031H6.77717H2.3597V21.3648L13.9314 12.5826L19.7645 17.4899L25.1646 13.892L34.6423 20.545V24.0582C34.6423 24.5262 35.024 24.9079 35.492 24.9079C35.96 24.9079 36.3417 24.5262 36.3417 24.0582V1.69935C36.3417 0.760078 35.5816 0 34.6423 0L34.6472 0.000127625ZM34.6423 11.3212V11.3511V18.462L25.1896 11.8307L19.8842 15.3688L13.9879 10.4101L2.35963 19.234V1.69966H34.6474V11.3214L34.6423 11.3212Z"
								fill="#7351FD"
							/>
						</svg>

						<p className="text-sm text-ec-body font-inter mt-2">
							<span className="text-ec-primary">
								{__('Click to update', 'easycommerce')}
							</span>{' '}
							{__('or Drag and drop', 'easycommerce')}
						</p>

						<button
							type="button"
							className="easycommerce-outline-button mt-3 h-[43px]"
							onClick={handleClick}
						>
							{__('Upload Images', 'easycommerce')}
						</button>
					</div>
				)}
			</div>

			<input
				type="hidden"
				name="product_gallery"
				value={JSON.stringify(uploadedImages)}
			/>

            <input
                type="hidden"
                name="product_thumbnail"
                value={selectedThumbnail ? selectedThumbnail : null}
            />

			
		</>
	);
};

export default MediaUploader;