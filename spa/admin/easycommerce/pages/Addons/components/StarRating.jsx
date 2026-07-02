import React from 'react'

const starImageURL = EASYCOMMERCE.assets + '/admin/img/star.svg';

const StarRating = ({ rating }) => {
	return (
		<div className="flex items-center flex-row gap-1">
			<div className="w-[69px] h-[14px]">
				<div style={{ maskImage: `url(${starImageURL})`, width: '100%', height: '100%', backgroundSize: 'cover', display: 'flex' }}>
					<div style={{ width: `${(rating / 5) * 100}%`, height: '100%', backgroundColor: '#F99D1D' }}></div>
					<div style={{ width: `${((5 - rating) / 5) * 100}%`, height: '100%', backgroundColor: '#D1D5DB' }}></div>
				</div>
			</div>
			<span className="text-[#1A0180] text-[14px]">{rating}</span>
		</div>
	)
}

export default StarRating