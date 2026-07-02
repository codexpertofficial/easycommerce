import React from 'react';

const Item = ({image, title, desc, comingSoon}) => {
	return (
		<div className="border border-ec-table-stock rounded-xl p-5 flex flex-col gap-5">
			{image}

			<div>
				<h2 className="text-xl text-ec-title font-medium mb-2">
					{title}
				</h2>
				<p className="text-ec-body text-sm">
					{desc}
				</p>
			</div>

            {comingSoon ? (
                <span className="group flex items-center font-medium text-sm gap-2.5 px-4 py-1.5 rounded-full duration-300 text-[#F99D1D] bg-[#F99D1D1A] w-max">
                    Coming Soon
                </span>
            ) : (
                <a href='https://easycommerce.dev/pricing?utm_source=inplugin&utm_medium=button&utm_campaign=BFCM' target='_blank' className="!flex gap-[10px] items-center font-medium text-sm px-4 py-1.5 rounded-full duration-300 text-ec-primary bg-ec-primary/10 w-max hover:bg-ec-primary hover:text-white">
                    Upgrade to PRO
                    <svg
                        width="14"
                        height="12"
                        viewBox="0 0 14 12"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M7.75 10.75L12.75 5.75M12.75 5.75L7.75 0.75M12.75 5.75H0.75"
                            className="stroke-ec-primary group-hover:stroke-white duration-300"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </a>
            )}
		</div>
	);
};

export default Item;
