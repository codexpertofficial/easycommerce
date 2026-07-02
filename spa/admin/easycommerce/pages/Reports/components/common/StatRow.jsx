import React from 'react';

const StatRow = ({ icon, title, value }) => {
	return (
		<div className="flex items-center justify-between py-2.5 px-4">
			<div className="flex items-center gap-4">
				<div className="rounded-[10px] bg-[#F3F3FF] flex items-center justify-center w-11 h-11">
					<img
						src={EASYCOMMERCE.assets + 'admin/img/reports/' + icon + '.svg'}
						alt=""
					/>
				</div>
				<h6 className="text-[#3C3C42] text-base font-medium">{title}</h6>
			</div>
			<span className="text-ec-title text-base">{value}</span>
		</div>
	);
};

export default StatRow;
