import React from 'react';

const Variations = ({ data }) => {
	return data && (
        <div className="text-base text-[#1B2538] mt-4">
            <div className="bg-[#F7F7F7] rounded-lg flex items-center gap-4 px-4 py-3 mb-[2px]">
                <div className="flex items-center justify-start w-[25%]">
                    <h6 className="font-medium">Variation</h6>
                </div>
                <div className="flex items-center justify-center w-[25%]">
                    <h6 className="font-medium">Stock</h6>
                </div>
                <div className="flex items-center justify-center w-[25%]">
                    <h6 className="font-medium">Amount Sold</h6>
                </div>
                <div className="flex items-center justify-center w-[25%]">
                    <h6 className="font-medium">Item Sold</h6>
                </div>
            </div>

            <div className="overflow-y-auto max-h-[184px]">
                {data?.map((variation, index) => (
                    <div className="w-full flex items-center gap-4 px-4 py-2.5 border-b border-[#EEF0FF]" key={index}>
                        <div className="flex items-center justify-start w-[25%]">
                            {variation.name}
                        </div>
                        <div className="flex items-center justify-center w-[25%]">
                            {variation.stock}
                        </div>
                        <div className="flex items-center justify-center w-[25%]">
                            {variation.total_sales}
                        </div>
                        <div className="flex items-center justify-center w-[25%]">
                            {variation.items_sold}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default Variations;
