import React, { useState, useEffect } from 'react';

import TextField from '../../../../../common/components/inputs/TextField';

const LicenseField = ({ errorMsg, setErrorMsg }) => {
	const [email, setEmail] = useState('');
	const [emailError, setEmailError] = useState(false);

	const [key, setKey] = useState('');
	const [keyError, setKeyError] = useState(false);
	const [keyVisible, setKeyVisible] = useState(false);

	const [loading, setLoading] = useState(false);

	useEffect(() => {
		if (errorMsg === 'You need to activate your license to use this feature.') {
			setEmailError(true);
			setKeyError(true);
		}
	}, [errorMsg]);

	const sendResponse = async () => {
		try {
			if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
				setEmailError(true);
				setErrorMsg('Please enter a valid email address.');
				return;
			}

			if (email === '' || key === '') {
				setEmailError(true);
				setKeyError(true);
				setErrorMsg('Please enter both email and license key.');
				return;
			}

			setLoading(true);

			const response = await fetch(`${EASYCOMMERCE.rest_base}/pro/license`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': EASYCOMMERCE.nonce,
				},
				body: JSON.stringify({
					email: email,
					key: key,
				}),
			});

			if (response.ok) {
				setErrorMsg('');
				window.location.reload();
			} else {
				setEmailError(true);
				setKeyError(true);
				setErrorMsg('Invalid email or license key. Please try again.');
			}
		} catch (error) {
			setErrorMsg(
				'An error occurred while verifying the license. Please try again.'
			);
		} finally {
			setLoading(false);
		}
	};

	return (
		<div className="border border-ec-table-stock rounded-xl py-14 px-16">
			<div className="flex flex-col items-center gap-4">
				<div className="rounded-full w-[78px] h-[78px] flex items-center justify-center bg-ec-primary/5">
					<svg
						width="39"
						height="39"
						viewBox="0 0 39 39"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
					>
						<path
							d="M29.603 5.94699C28.1751 4.51901 25.8604 4.51901 24.4327 5.94699C23.0049 7.37497 23.0049 9.6899 24.4327 11.1177L27.8802 14.5655C29.3081 15.9935 31.6228 15.9935 33.0505 14.5655C34.4784 13.1375 34.4784 10.8226 33.0505 9.3948L29.603 5.94699ZM31.3259 12.8412C30.85 13.3172 30.0789 13.3172 29.603 12.8412L26.1571 9.39493C25.6811 8.91892 25.6811 8.14782 26.1571 7.67181C26.633 7.1958 27.4041 7.1958 27.88 7.67181L31.3259 11.118C31.8035 11.594 31.8019 12.3651 31.3259 12.8412ZM36.4962 5.94699L33.0503 2.50076C29.7202 -0.831209 24.3213 -0.834377 20.9881 2.496C19.3857 4.09694 18.4861 6.26909 18.4878 8.53312V15.3416L1.07089 32.7584C-0.356964 34.1863 -0.356964 36.5013 1.07089 37.9291C2.49875 39.3569 4.81347 39.3571 6.24116 37.9291L8.82561 35.3428L11.4101 37.9275C12.8363 39.3555 15.151 39.3571 16.5791 37.9307C17.677 36.8343 17.9625 35.1651 17.2946 33.7673L18.8827 32.179C20.7041 33.0501 22.8871 32.2822 23.7581 30.4607C24.4276 29.0612 24.1421 27.3905 23.0442 26.294L20.4598 23.7093L23.6598 20.5091H30.4676C35.1781 20.5106 38.9984 16.6932 39 11.9824C38.9984 9.71987 38.0986 7.54776 36.4962 5.94699ZM21.3197 28.019C21.7956 28.495 21.7956 29.2661 21.3197 29.7422C20.8437 30.2182 20.0727 30.2182 19.5967 29.7422C19.1207 29.2661 18.3497 29.2661 17.8737 29.7422L14.8578 32.7584C14.3818 33.2344 14.3818 34.0055 14.8578 34.4815C15.3337 34.9575 15.3337 35.7286 14.8578 36.2046C14.3818 36.6806 13.6108 36.6806 13.1348 36.2046L10.5504 33.62L18.7367 25.4313L21.3197 28.019ZM34.7733 16.2888C33.6326 17.436 32.081 18.0786 30.4645 18.0738L23.1522 18.0722C22.8286 18.0722 22.5192 18.2008 22.2907 18.4292L9.68754 31.0368C9.21157 31.5128 8.43895 31.5128 7.96299 31.0368C7.48702 30.5608 7.48702 29.7881 7.96299 29.3121L20.5694 16.7062C20.7979 16.4777 20.9264 16.1683 20.9264 15.8462V8.53487C20.9248 5.16956 23.6504 2.44211 27.0154 2.44049C28.6321 2.44049 30.1837 3.08149 31.3258 4.22705L34.7733 7.67486C37.1214 10.0501 37.1024 13.8932 34.7733 16.2888Z"
							fill="#7351FD"
						/>
					</svg>
				</div>
				<div>
					<h2 className="text-ec-title text-2xl font-medium text-center">
						Activate License
					</h2>
					<p className="text-ec-body w-[295px] text-center text-base mt-1.5">
						Enter your email and license key to unlock premium features
					</p>
				</div>
			</div>

			<div className="mt-8">
				<div>
					<label htmlFor="license-email" className="text-base text-ec-title">
						Email
					</label>
					<TextField
						id="license-email"
						placeholder="Enter Your Email"
						className={`mt-3 ${emailError ? '[&]:!border-ec-red' : ''}`}
						type="email"
						value={email}
						onChange={(e) => {
							setErrorMsg('');
							setEmailError(false);
							setEmail(e.target.value);
						}}
						autofill="email"
					/>
				</div>
				<div className="mt-5">
					<label htmlFor="license-key" className="text-base text-ec-title">
						License Key
					</label>
					<div className="relative w-full h-max mt-3">
						<TextField
							id="license-key"
							placeholder="Enter your License Key"
							className={`${keyError ? '[&]:!border-ec-red' : ''}`}
							onChange={(e) => {
								setErrorMsg('');
								setKeyError(false);
								setKey(e.target.value);
							}}
							type={keyVisible ? 'text' : 'password'}
						/>
						<button
							onClick={() => setKeyVisible(!keyVisible)}
							className="absolute top-1/2 right-4 -translate-y-1/2"
						>
							{keyVisible ? (
								<svg
									xmlns="http://www.w3.org/2000/svg"
									width="24"
									height="24"
									viewBox="0 0 24 24"
									fill="none"
								>
									<path
										d="M12 5C8.24261 5 5.43602 7.4404 3.76737 9.43934C2.51521 10.9394 2.51521 13.0606 3.76737 14.5607C5.43602 16.5596 8.24261 19 12 19C15.7574 19 18.564 16.5596 20.2326 14.5607C21.4848 13.0606 21.4848 10.9394 20.2326 9.43934C18.564 7.4404 15.7574 5 12 5Z"
										className="stroke-black/40"
										stroke-width="1.5"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
									<path
										d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z"
										className="stroke-black/40"
										stroke-width="1.5"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
								</svg>
							) : (
								<svg
									xmlns="http://www.w3.org/2000/svg"
									width="24"
									height="24"
									viewBox="0 0 24 24"
									fill="none"
								>
									<path
										d="M9.76404 5.29519C10.4664 5.10724 11.2123 5 12 5C15.7574 5 18.564 7.4404 20.2326 9.43934C21.4848 10.9394 21.4846 13.0609 20.2324 14.5609C20.0406 14.7907 19.8337 15.0264 19.612 15.2635M12.5 9.04148C13.7563 9.25224 14.7478 10.2437 14.9585 11.5M3 3L21 21M11.5 14.9585C10.4158 14.7766 9.52884 14.0132 9.17072 13M4.34914 8.77822C4.14213 9.00124 3.94821 9.22274 3.76762 9.43907C2.51542 10.9391 2.51523 13.0606 3.76739 14.5607C5.43604 16.5596 8.24263 19 12 19C12.8021 19 13.5608 18.8888 14.2744 18.6944"
										className="stroke-black/40"
										stroke-width="1.5"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
								</svg>
							)}
						</button>
					</div>
				</div>

				{errorMsg && <p className="text-ec-red text-base mt-4">{errorMsg}</p>}

				<button
					onClick={sendResponse}
					className="h-[50px] flex items-center w-full mt-6 bg-ec-primary text-base font-medium text-white hover:bg-ec-title duration-300 rounded-lg justify-center"
				>
					{loading ? (
						<img
							src={`${EASYCOMMERCE.assets}/admin/img/loading.gif`}
							alt="loading"
							className="h-8 w-auto"
						/>
					) : (
						'Verify License'
					)}
				</button>
			</div>
		</div>
	);
};

export default LicenseField;
