import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import Variations from './Variations';

const ProductInfo = ({ product }) => {
	const isVariableProduct = product?.variations && product.variations.length > 1;

	return (
		<div>
			<div className={`flex items-center gap-4 ${!isVariableProduct && 'flex-col justify-center'}`}>
				<div className="w-[100px] h-[100px] overflow-hidden">
					{product?.thumbnail ? (
						<div className='rounded-lg border border-[#E9E4FF] flex items-center justify-center'>
							<img
								src={product?.thumbnail}
								alt={product?.name}
								className="w-[90px] h-[90px] object-contain"
							/>
						</div>
					) : (
						<svg
							className="mx-auto"
							width="100"
							height="100"
							viewBox="0 0 172 172"
							fill="none"
							xmlns="http://www.w3.org/2000/svg"
						>
							<g opacity="0.58" clipPath="url(#clip0_9586_5626)">
								<mask
									id="mask0_9586_5626"
									style={{ maskType: 'luminance' }}
									maskUnits="userSpaceOnUse"
									x="0"
									y="0"
									width="172"
									height="172"
								>
									<path d="M0 0H172V172H0V0Z" fill="white" />
								</mask>

								<g mask="url(#mask0_9586_5626)">
									<path
										d="M157.664 105.367V115.282C157.664 116.928 157.592 118.588 157.449 120.233C155.438 142.873 139.712 157.667 117.02 157.667H54.9753C43.4859 157.667 33.7915 153.938 26.8258 147.177C24.0657 144.643 21.7586 141.656 20.0039 138.346C22.3736 135.465 25.0305 132.311 27.6156 129.149C31.9962 123.904 36.233 118.724 38.89 115.354C42.8396 110.468 53.252 97.6073 67.6859 103.643C70.63 104.864 73.2154 106.589 75.5851 108.097C81.4015 111.977 83.8431 113.127 87.9365 110.899C92.4602 108.456 95.4047 103.643 98.4923 98.6131C100.144 95.9474 101.796 93.3681 103.591 90.997C111.418 80.7943 123.482 78.0638 133.536 84.0992C138.563 87.117 142.871 90.9252 146.893 94.7978C147.754 95.6599 148.616 96.4577 149.406 97.2478C150.483 98.3255 154.074 101.918 157.664 105.367Z"
										fill="#A4A4A4"
									/>
									<path
										d="M117.093 14.334H54.9768C30.6331 14.334 14.332 31.3627 14.332 56.7185V115.284C14.332 124.114 16.3426 131.953 20.005 138.348C22.3747 135.466 25.0316 132.312 27.617 129.144C31.9976 123.906 36.2341 118.725 38.8911 115.355C42.8407 110.47 53.2531 97.6085 67.687 103.644C70.6315 104.865 73.2165 106.59 75.5862 108.099C81.403 111.979 83.8446 113.128 87.9376 110.894C92.4617 108.458 95.4058 103.644 98.4938 98.6073C100.145 95.9487 101.797 93.3693 103.592 90.9983C111.42 80.7955 123.484 78.065 133.537 84.1005C138.564 87.1182 142.872 90.9264 146.894 94.7991C147.756 95.6614 148.617 96.459 149.407 97.2491C150.484 98.3197 154.075 101.912 157.665 105.368V56.7185C157.665 31.3623 141.364 14.334 117.093 14.334Z"
										fill="#DBDBDB"
									/>
									<path
										d="M82.0871 63.0444C82.0871 73.1393 73.6782 81.5458 63.5887 81.5458C53.5065 81.5458 45.0977 73.1393 45.0977 63.0444C45.0977 52.9565 53.5065 44.543 63.5887 44.543C73.6782 44.543 82.0871 52.9565 82.0871 63.0444Z"
										fill="#A4A4A4"
									/>
								</g>
							</g>

							<defs>
								<clipPath id="clip0_9586_5626">
									<rect width="172" height="172" fill="white" />
								</clipPath>
							</defs>
						</svg>
					)}
				</div>

				<div className={!isVariableProduct && 'text-center'}>
					<h4 className="text-xl font-medium text-ec-title mb-1.5">
						{product?.name}
					</h4>
					<p className="text-sm text-[#6A7282]">SKU: {product?.sku}</p>
				</div>
			</div>

			{isVariableProduct ? (
				<Variations data={product?.variations} />
			) : (
				<div className="bg-[#F5F8FB] rounded-lg p-4 flex flex-col gap-4 mt-4">
					<div className="flex items-center justify-between text-[#3C3C42] text-lg">
						Stock
						<span className='font-medium'>{product?.variations[0].stock || 0}</span>
					</div>
					
					<div className="flex items-center justify-between text-[#3C3C42] text-lg">
						Amount Sold
						<span className='font-medium'>{product?.variations[0].total_sales || 0}</span>
					</div>
					
					<div className="flex items-center justify-between text-[#3C3C42] text-lg">
						Item Sold
						<span className='font-medium'>{product?.variations[0].items_sold || 0}</span>
					</div>
				</div>
			)}

			
		</div>
	);
};

export default ProductInfo;
