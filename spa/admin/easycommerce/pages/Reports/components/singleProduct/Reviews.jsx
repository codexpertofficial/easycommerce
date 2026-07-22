import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __, _n, sprintf } from '@wordpress/i18n';

import StarRating from '../common/StarRating';

const Reviews = ({ endpoint, params = {} }) => {
	const [reviewsData, setReviewsData] = useState(null);
	const [focusedReview, setFocusedReview] = useState(null);
	const [loading, setLoading] = useState(false);

	const fetchReviewsData = async () => {
		setLoading(true);
		try {
			const data = await apiFetch({
				path: addQueryArgs(endpoint, params),
			});
			setReviewsData(data.data);
			setFocusedReview(data.data.reviews[0]);
		} catch (error) {
			console.error('Error fetching reviews:', error);
		} finally {
			setLoading(false);
		}
	};

	useEffect(() => {
		if (endpoint) {
			fetchReviewsData();
		}
	}, [endpoint, JSON.stringify(params)]);

	return reviewsData && (
		console.log(reviewsData) ||
		<div className="flex gap-6">
			<div className="w-2/3">
				<div className="flex items-center justify-between mt-6 mb-8 gap-10">
					<div className="flex flex-col items-center p-4 w-[200px]">
						<h3 className="text-6xl font-bold text-[#3C3C42] text-center">
							{reviewsData?.rating}
						</h3>

						<div
							className="relative overflow-hidden my-4"
							style={{
								width: '112px',
								height: '21px',
								backgroundImage: `url(${EASYCOMMERCE.assets}admin/img/reports/stars-mask.svg)`,
								backgroundSize: 'cover',
								backgroundRepeat: 'no-repeat',
							}}
						>
							<div
								style={{
									width: `${(reviewsData.rating / 5) * 100}%`,
									height: '100%',
									backgroundColor: '#f5b301',
									WebkitMaskImage: `url(${EASYCOMMERCE.assets}admin/img/reports/stars-mask.svg)`,
									WebkitMaskRepeat: 'no-repeat',
									WebkitMaskSize: 'cover',
									maskImage: `url(${EASYCOMMERCE.assets}admin/img/reports/stars-mask.svg)`,
									maskRepeat: 'no-repeat',
									maskSize: 'cover',
								}}
							/>
						</div>

						<span className="text-base text-[#6A7282]">
							{
								// translators: %d: total number of reviews.
								sprintf( _n( 'Total %d Review', 'Total %d Reviews', reviewsData.rating_count, 'easycommerce' ), reviewsData.rating_count )
							}
						</span>
					</div>

					<div className="flex items-center gap-3 flex-1">
						<div className="flex flex-col gap-4">
							{[5, 4, 3, 2, 1].map((star) => (
								<div key={star} className="h-5 flex items-center">
									<span className="text-sm text-[#364153]">
										{
											// translators: %d: star rating value (1-5).
											sprintf( __( '%d Star', 'easycommerce' ), star )
										}
									</span>
								</div>
							))}
						</div>

						<div className="flex flex-col gap-4 flex-1">
							{[5, 4, 3, 2, 1].map((star) => {
								const count = reviewsData?.rating_counts?.[star] ?? 0;
								const total = Number(reviewsData?.rating_count) || 0;

								const percentage = total > 0 ? (count / total) * 100 : 0;

								return (
									<div className="h-5 flex items-center" key={star}>
										<div className="relative bg-[#f3f3f3] rounded-full w-full h-3">
											<div
												className="absolute left-0 top-0 h-full rounded-full bg-[#F9CE5B]"
												style={{ width: `${percentage}%` }}
											/>
										</div>
									</div>
								);
							})}
						</div>

						<div className="flex flex-col gap-4">
							{[5, 4, 3, 2, 1].map((star) => {
								const count = reviewsData?.rating_counts?.[star] ?? 0;

								return (
									<div className="h-5 flex items-center" key={star}>
										<span className="text-sm text-[#364153]">
											{
												// translators: %d: number of reviews for this star rating.
												sprintf( _n( '%d review', '%d reviews', count, 'easycommerce' ), count )
											}
										</span>
									</div>
								);
							})}
						</div>
					</div>
				</div>

				{Number(reviewsData?.rating_count) > 0 && (
					<div className="text-base text-[#1B2538]">
						<div className="bg-[#F7F7F7] rounded-lg flex items-center gap-4 px-4 py-3 mb-[2px]">
							<div className="flex items-center justify-center w-[15%]">
								<h6 className="font-medium">{__( 'Date', 'easycommerce' )}</h6>
							</div>
							<div className="flex items-center justify-center w-[25%]">
								<h6 className="font-medium">{__( 'Customer Name', 'easycommerce' )}</h6>
							</div>
							<div className="flex items-center justify-center w-[45%]">
								<h6 className="font-medium">{__( 'Reviews', 'easycommerce' )}</h6>
							</div>
							<div className="flex items-center justify-center w-[15%]">
								<h6 className="font-medium">{__( 'Rating', 'easycommerce' )}</h6>
							</div>
						</div>

						{reviewsData.reviews.slice(0, 4).map((review, index) => (
							<button
								key={index}
								onClick={() => setFocusedReview(review)}
								className="w-full flex items-center gap-4 px-4 py-3 min-h-[70px] border-b border-[#EEF0FF] hover:bg-[#f7f7f7] duration-300"
							>
								<div className="flex items-center justify-center w-[15%]">
									{review.date}
								</div>
								<div className="flex items-center justify-center gap-2 w-[25%]">
									<img
										src={review.avatar}
										alt={review.name}
										className="rounded-full w-6 h-6"
									/>
									{review.name}
								</div>
								<div className="flex items-center justify-center w-[45%]">
									<span className="truncate">{review.review}</span>
								</div>
								<div className="flex items-center justify-center w-[15%]">
									<StarRating rating={review.rating} size="small" />
								</div>
							</button>
						))}
					</div>
				)}
			</div>

			<div className="w-1/3 border border-[#EEF0FF] rounded-lg p-6">
				{focusedReview ? (
					<>
						<div className="flex flex-col items-center mt-6">
							<img
								src={focusedReview.avatar}
								alt={focusedReview.name}
								className="rounded-full w-[108px] h-[108px]"
							/>

							<div className="flex items-center mt-2.5 gap-1">
								<StarRating rating={focusedReview.rating} size="large" />
							</div>

							<h6 className="text-base text-[#1B2538] font-medium mt-4">
								{focusedReview.name}
							</h6>

							<div className="flex items-center gap-1">
								<svg
									width="16"
									height="16"
									viewBox="0 0 16 16"
									fill="none"
									xmlns="http://www.w3.org/2000/svg"
								>
									<path
										d="M5.33203 1.33398V4.00065"
										stroke="#62748E"
										stroke-width="1.33333"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
									<path
										d="M10.668 1.33398V4.00065"
										stroke="#62748E"
										stroke-width="1.33333"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
									<path
										d="M12.6667 2.66602H3.33333C2.59695 2.66602 2 3.26297 2 3.99935V13.3327C2 14.0691 2.59695 14.666 3.33333 14.666H12.6667C13.403 14.666 14 14.0691 14 13.3327V3.99935C14 3.26297 13.403 2.66602 12.6667 2.66602Z"
										stroke="#62748E"
										stroke-width="1.33333"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
									<path
										d="M2 6.66602H14"
										stroke="#62748E"
										stroke-width="1.33333"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
								</svg>

								<span className="text-[#62748E] text-sm">{focusedReview.date}</span>
							</div>
						</div>

						<div className="bg-[#EEF0FF] w-[70%] max-w-[382px] h-[1px] mx-auto my-8" />

						<div className="w-full rounded-lg bg-[#F8FAFC] py-6 px-4">
							<p className="text-[#3C3C42] text-sm">{focusedReview.review}</p>
						</div>
					</>
				) : (
					<div className='h-full flex items-center justify-center'>
						<span className="text-[#6A7282] text-sm block text-center">{__( 'No reviews yet', 'easycommerce' )}</span>
					</div>
				)}
			</div>
		</div>
	);
};

export default Reviews;
