import React from "react";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const AddressSkeleton = ({ title }) => {
	return (
		<div>
			<div className="w-full h-[57px] flex items-center px-5 bg-[#F8F8F8] border border-ec-border rounded-tl-lg rounded-tr-lg">
				<h3 className="easycommerce-dashboard-address-title">
					{title} Address
				</h3>
			</div>
			<div className="easycommerce-dashboard-address-wrapper grid grid-cols-1 sm:grid-cols-2 gap-4 px-5 pt-6 pb-[30px] border border-ec-border border-t-0 rounded-bl-lg rounded-br-lg">
				{[...Array(11)].map((_, index) => (
					<div
						className="col-span-1 flex flex-col gap-2 items-start"
						key={index}>
						<div className="w-full">
							<Skeleton height={20} width={"100%"} />
						</div>
						<div className="w-full">
							<Skeleton height={40} width={"100%"} />
						</div>
					</div>
				))}
			</div>
			<div className="w-full mt-4 flex justify-end items-center">
				<Skeleton height={48} width={180} />
			</div>
		</div>
	);
};

export default AddressSkeleton;
