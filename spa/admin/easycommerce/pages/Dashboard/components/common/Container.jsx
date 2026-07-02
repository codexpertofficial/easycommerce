import React from 'react';
import DashboardButton from "../common/DashboardButton";

const Container = ({ title, button_url, children, fillHeight }) => {
	return (
		<div className={`bg-white rounded-lg border border-[#EEF0FF] ${fillHeight ? 'h-full' : 'h-fit'}`}>
			<div className="p-4 border-b border-b-[#EEF0FF] flex items-center justify-between">
				<div className="flex items-center gap-5">
					<h4 className="text-xl font-medium text-ec-title">
						{title}
					</h4>
				</div>
				{button_url && (
                    <DashboardButton url={button_url} />
                )}
			</div>
			<div className="p-4">
                {children}
            </div>
		</div>
	);
};
export default Container;
