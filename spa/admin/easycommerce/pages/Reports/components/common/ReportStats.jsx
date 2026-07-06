import React, { useState, useEffect, useRef } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import CardsSkeleton from './CardsSkeleton';
import Tooltip from './Tooltip';

const ReportStats = ({ endpoint, params = {}, skeletonCount = 6 }) => {
	const [statsData, setStatsData] = useState([]);
	const [loading, setLoading] = useState(true);
	const [activeDropdown, setActiveDropdown] = useState(null);
	const [hideDigits, setHideDigits] = useState([]);
	const dropdownRefs = useRef([]);

	useEffect(() => {
		const handleClickOutside = (event) => {
			const isInsideAnyDropdown = dropdownRefs.current.some(
				(ref) => ref && ref.contains(event.target)
			);
			if (!isInsideAnyDropdown) {
				setActiveDropdown(null);
			}
		};

		document.addEventListener('mousedown', handleClickOutside);
		return () => document.removeEventListener('mousedown', handleClickOutside);
	}, []);

	const fetchStats = async () => {
		apiFetch({
			path: addQueryArgs(endpoint, params),
		}).then((data) => {
			setStatsData(data.data.stats);
			setLoading(false);
		});
	};

	useEffect(() => {
		fetchStats();
	}, [endpoint, JSON.stringify(params)]);

	return loading ? (
		<CardsSkeleton count={skeletonCount} />
	) : (
		<div className="grid grid-cols-3 ec-db-lg:grid-cols-4 gap-6">
			{statsData.map((data, index) => {
				const vibe =
					data.comparison?.vibe ||
					(data.comparison?.type === 'increment' ? 'positive' : 'negative');

				return (
					<div
						key={index}
						className="flex flex-col justify-between h-[144px] bg-white rounded-lg border border-transparent hover:border-ec-table-stock duration-300 p-5 hover:shadow-[0px_4px_10px_0px_#0000001A]"
					>
						<div className="flex justify-between">
							<div className="flex gap-3">
								<div className="rounded-[10px] bg-[#F3F3FF] flex items-center justify-center w-11 h-11">
									<img
										src={
											EASYCOMMERCE.assets +
											'admin/img/reports/' +
											data.icon +
											'.svg'
										}
										alt=""
									/>
								</div>
								<h6 className="text-lg font-medium text-[#3C3C42]">
									{data.title}
								</h6>

								<div className="mt-1.5">
									{data.tooltip && <Tooltip text={data.tooltip} />}
								</div>
								
							</div>

							<div ref={(el) => (dropdownRefs.current[index] = el)} className="relative h-max">
								<button
									className="w-6 h-6 flex items-center justify-center"
									onClick={() =>
										setActiveDropdown(activeDropdown === index ? null : index)
									}
								>
									<svg
										width="24"
										height="5"
										viewBox="0 0 24 5"
										fill="none"
										xmlns="http://www.w3.org/2000/svg"
									>
										<path
											d="M2.40002 4.80003C3.72551 4.80003 4.80003 3.72551 4.80003 2.40002C4.80003 1.07452 3.72551 0 2.40002 0C1.07452 0 0 1.07452 0 2.40002C0 3.72551 1.07452 4.80003 2.40002 4.80003Z"
											fill="#767676"
										/>
										<path
											d="M12.0016 4.80003C13.3271 4.80003 14.4016 3.72551 14.4016 2.40002C14.4016 1.07452 13.3271 0 12.0016 0C10.6761 0 9.60156 1.07452 9.60156 2.40002C9.60156 3.72551 10.6761 4.80003 12.0016 4.80003Z"
											fill="#767676"
										/>
										<path
											d="M21.5992 4.80003C22.9247 4.80003 23.9993 3.72551 23.9993 2.40002C23.9993 1.07452 22.9247 0 21.5992 0C20.2737 0 19.1992 1.07452 19.1992 2.40002C19.1992 3.72551 20.2737 4.80003 21.5992 4.80003Z"
											fill="#767676"
										/>
									</svg>
								</button>

								{activeDropdown === index && (
									<div className="absolute right-0 bg-white top-full w-max flex flex-col border border-[#F0EDFB] shadow-[0px_4px_6px_0px_#0000001A] rounded-lg overflow-hidden">
										<button
											className="text-left text-sm flex items-center gap-2 text-[#3C3C42] px-3 py-2 hover:bg-[#F0EDFB] duration-300"
											onClick={() => {
												setHideDigits((prev) => {
													const newHideDigits = [...prev];
													newHideDigits[index] = !newHideDigits[index];
													return newHideDigits;
												});
												setActiveDropdown(null);
											}}
										>
											{!hideDigits[index] ? (
												<>
													<svg
														width="18"
														height="18"
														viewBox="0 0 18 18"
														fill="none"
														xmlns="http://www.w3.org/2000/svg"
													>
														<path
															d="M14.2884 5.70508L12.224 7.77002C12.4771 8.25939 12.6178 8.81627 12.6178 9.40127C12.6178 11.3925 10.9916 13.0181 9.00031 13.0181C8.41531 13.0181 7.85844 12.8775 7.36906 12.6244L5.86719 14.1268C6.86281 14.4587 7.92031 14.6387 9.00031 14.6387C12.2403 14.6387 15.2778 13.0468 17.1284 10.3862C17.3371 10.0881 17.4384 9.74495 17.4384 9.40183C17.4384 9.0587 17.3371 8.71558 17.129 8.41745C16.3734 7.33231 15.4074 6.40955 14.2884 5.70508Z"
															fill="#3C3C42"
														/>
														<path
															d="M8.99922 11.8933C10.3717 11.8933 11.4911 10.7739 11.4911 9.40141C11.4911 9.13141 11.4461 8.87266 11.3673 8.62516L8.22297 11.7695C8.47047 11.8483 8.72922 11.8933 8.99922 11.8933ZM14.7767 2.82466C14.7246 2.77251 14.6628 2.73114 14.5948 2.70291C14.5267 2.67469 14.4538 2.66016 14.3801 2.66016C14.3064 2.66016 14.2335 2.67469 14.1654 2.70291C14.0974 2.73114 14.0356 2.77251 13.9835 2.82466L12.1329 4.67528C11.1373 4.34341 10.0792 4.16341 8.99922 4.16341C5.75922 4.16341 2.72116 5.75528 0.870536 8.41647C0.669974 8.70555 0.5625 9.049 0.5625 9.40084C0.5625 9.75269 0.669974 10.0961 0.870536 10.3852C1.64116 11.4877 2.60866 12.4046 3.71116 13.0965L2.42304 14.3852C2.34454 14.4637 2.29116 14.5638 2.26968 14.6728C2.24819 14.7817 2.25957 14.8946 2.30237 14.9971C2.34516 15.0995 2.41745 15.187 2.51004 15.2483C2.60264 15.3096 2.71136 15.342 2.82241 15.3414C2.96866 15.3414 3.10929 15.2908 3.22179 15.1783L14.7767 3.62341C14.996 3.40403 14.996 3.04403 14.7767 2.82466ZM9.77547 7.03216C9.52797 6.95341 9.26922 6.90897 8.99922 6.90897C7.62672 6.90897 6.50735 8.02834 6.50735 9.40084C6.50735 9.67084 6.55235 9.92959 6.6311 10.1771L5.7761 11.0321C5.52297 10.5427 5.38235 9.98584 5.38235 9.40084C5.38235 7.40959 7.00797 5.78397 8.99922 5.78397C9.58422 5.78397 10.1411 5.92459 10.6305 6.17772L9.77547 7.03216Z"
															fill="#3C3C42"
														/>
													</svg>
													Hide Digits
												</>
											) : (
												<>
													<svg
														width="18"
														height="11"
														viewBox="0 0 18 11"
														fill="none"
														xmlns="http://www.w3.org/2000/svg"
													>
														<path
															d="M9 0C5.56091 0 2.44216 1.88156 0.140841 4.93771C-0.0469469 5.18809 -0.0469469 5.5379 0.140841 5.78828C2.44216 8.84811 5.56091 10.7297 9 10.7297C12.4391 10.7297 15.5578 8.84811 17.8592 5.79196C18.0469 5.54158 18.0469 5.19178 17.8592 4.94139C15.5578 1.88156 12.4391 0 9 0ZM9.2467 9.14268C6.96379 9.28628 5.07855 7.40472 5.22215 5.11813C5.33998 3.23289 6.86806 1.70482 8.7533 1.58699C11.0362 1.44339 12.9214 3.32495 12.7778 5.61154C12.6563 7.4931 11.1283 9.02117 9.2467 9.14268ZM9.13256 7.39736C7.90273 7.47468 6.88647 6.4621 6.96747 5.23228C7.03007 4.21602 7.85486 3.39491 8.87113 3.32863C10.101 3.2513 11.1172 4.26388 11.0362 5.49371C10.9699 6.51365 10.1451 7.33476 9.13256 7.39736Z"
															fill="#3C3C42"
														/>
													</svg>
													Show Digits
												</>
											)}
										</button>
									</div>
								)}
							</div>
						</div>

						<div className="flex justify-between items-end">
							<h5 className="text-xl font-medium text-[#282828] flex items-baseline gap-1">
								{!hideDigits[index] ? data.value : '*****'}

								{data.secondary_value && !hideDigits[index] && (
									<span className="text-[#282828] text-sm">
										({data.secondary_value})
									</span>
								)}
							</h5>

							{data.comparison && (
								<div className="flex items-center gap-1">
									{data.comparison.type === 'decrement' ? (
										<svg
											width="16"
											height="16"
											viewBox="0 0 16 16"
											fill="none"
											xmlns="http://www.w3.org/2000/svg"
										>
											<path
												d="M10.668 11.334H14.668V7.33398"
												stroke={vibe === 'positive' ? '#00A63E' : '#FF6161'}
												stroke-width="1.33333"
												stroke-linecap="round"
												stroke-linejoin="round"
											/>
											<path
												d="M14.6654 11.334L8.9987 5.66732L5.66536 9.00065L1.33203 4.66732"
												stroke={vibe === 'positive' ? '#00A63E' : '#FF6161'}
												stroke-width="1.33333"
												stroke-linecap="round"
												stroke-linejoin="round"
											/>
										</svg>
									) : (
										<svg
											width="16"
											height="16"
											viewBox="0 0 16 16"
											fill="none"
											xmlns="http://www.w3.org/2000/svg"
										>
											<path
												d="M10.668 4.66602H14.668V8.66602"
												stroke={vibe === 'positive' ? '#00A63E' : '#FF6161'}
												stroke-width="1.33333"
												stroke-linecap="round"
												stroke-linejoin="round"
											/>
											<path
												d="M14.6654 4.66602L8.9987 10.3327L5.66536 6.99935L1.33203 11.3327"
												stroke={vibe === 'positive' ? '#00A63E' : '#FF6161'}
												stroke-width="1.33333"
												stroke-linecap="round"
												stroke-linejoin="round"
											/>
										</svg>
									)}

									<span
										className={`${vibe === 'positive' ? 'text-[#00A63E]' : 'text-[#FF6161]'} font-semibold text-sm`}
									>
										{data.comparison.value}
									</span>
									<span className="text-[#62748E] text-xs">
										vs prev. period
									</span>
								</div>
							)}
						</div>
					</div>
				);
			})}
		</div>
	);
};

export default ReportStats;
