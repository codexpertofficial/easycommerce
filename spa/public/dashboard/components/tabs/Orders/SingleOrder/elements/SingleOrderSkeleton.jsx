import React from "react";
import TableSkeleton from "../../../../../../../admin/common/TableSkeleton";
import Skeleton from "react-loading-skeleton";

const SingleOrderSkeleton = () => {
	return (
		<>
			<div className="pb-4">
				<Skeleton height={32} width={200} />
			</div>

			<div className="easycommerce-dashboard-section grid grid-cols-1 gap-6">
				<div className="easycommerce-dashboard-order-info-iteam col-span-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
					{[...Array(3)].map((_, index) => (
						<div className="flex flex-col" key={index}>
							<div className="w-full p-4 pb-3 border border-b-0 border-b-ec-border rounded-tl-lg rounded-tr-lg">
								<Skeleton height={40} width={200} />
							</div>

							<div className="w-full h-full flex flex-col gap-1 p-4 pt-10 border border-ec-border rounded-bl-lg rounded-br-lg">
								{[...Array(5)].map((_, index) => (
									<Skeleton
										key={index}
										height={20}
										width={"100%"}
									/>
								))}
							</div>
						</div>
					))}
				</div>

				<div className="easycommerce-dashboard-order-iteam p-1 md:p-7 col-span-1 border border-ec-border rounded-lg">
					<TableSkeleton numberOfRows={5} SkeletonHeight={30} />
				</div>
			</div>
		</>
	);
};

export default SingleOrderSkeleton;
