import React from 'react';

const yesIcon = (
    <svg className='mx-auto' width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="24" height="24" rx="12" fill="#00BC7A"/>
        <path d="M17.3202 7.20074C17.0062 7.2112 16.7087 7.34374 16.4909 7.57019C14.18 9.88605 12.4152 11.8203 10.2705 14.0092L7.98589 12.079C7.86467 11.9765 7.72441 11.899 7.57316 11.8508C7.4219 11.8025 7.26262 11.7847 7.10444 11.7981C6.94626 11.8116 6.79229 11.8561 6.65135 11.9292C6.51041 12.0022 6.38527 12.1024 6.28311 12.2239C6.18094 12.3454 6.10375 12.4859 6.05597 12.6373C6.00818 12.7887 5.99074 12.948 6.00463 13.1061C6.01853 13.2643 6.06349 13.4181 6.13694 13.5589C6.21039 13.6996 6.31089 13.8245 6.43268 13.9263L9.56926 16.5803C9.80004 16.7747 10.0953 16.8756 10.3968 16.863C10.6982 16.8504 10.9841 16.7254 11.1979 16.5125C13.7964 13.9084 15.6462 11.8284 18.1949 9.2742C18.3699 9.10501 18.4897 8.88682 18.5384 8.6483C18.5872 8.40978 18.5626 8.16209 18.468 7.93777C18.3734 7.71346 18.2131 7.52302 18.0083 7.39147C17.8034 7.25993 17.5636 7.19344 17.3202 7.20074Z" fill="white"/>
    </svg>
)

const noIcon = (
    <svg className='mx-auto' width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="24" height="24" rx="12" fill="#FF4564"/>
        <path d="M16.8731 17.2212C16.6643 17.4168 16.3896 17.5267 16.1035 17.5291C15.9533 17.53 15.8044 17.5001 15.6662 17.4413C15.5279 17.3824 15.4032 17.2959 15.2997 17.187L12.0843 13.8349L8.93165 17.2555C8.82369 17.3687 8.69358 17.4586 8.5494 17.5194C8.40522 17.5802 8.25007 17.6107 8.09361 17.6089C7.81299 17.6068 7.54303 17.5012 7.33537 17.3125C7.12777 17.1056 7.0078 16.8267 7.00037 16.5337C6.99294 16.2407 7.09861 15.9561 7.29547 15.739L10.5393 12.2215L7.26126 8.80087C7.12699 8.57905 7.07234 8.31809 7.10632 8.06104C7.14029 7.80398 7.26087 7.56619 7.44816 7.38687C7.63544 7.20755 7.87826 7.09742 8.13654 7.07465C8.39483 7.05188 8.65317 7.11782 8.86894 7.2616L12.0501 10.5853L15.1685 7.20459C15.3817 7.05611 15.6392 6.98488 15.8984 7.00268C16.1575 7.02049 16.4029 7.12626 16.5937 7.30249C16.7846 7.47872 16.9095 7.71485 16.9479 7.97178C16.9863 8.22871 16.9358 8.49105 16.8047 8.71535L13.5951 12.193L16.9074 15.6478C17.105 15.8642 17.2116 16.1484 17.2053 16.4414C17.1989 16.7343 17.08 17.0136 16.8731 17.2212Z" fill="white"/>
    </svg>
)

const CompareTable = () => {
	const features = [
		{ title: 'AI-Powered Smart Features' },
		{ title: 'Free AI Credits', pro: true },
		{ title: 'Drag & Drop Builder Integrations (Gutenberg, WP Bakery)' },
		{ title: 'Product, Order & Customer Management' },
		{ title: 'Bring Your Own Key (OpenAI/DeepSeek/Claude)', pro: true },
		{ title: 'Shipping & Advanced Taxation' },
		{ title: 'Coupon Management' },
		{ title: 'Personalized Emails' },
		{ title: 'Abandoned Cart Recovery' },
		{ title: 'Automated Abandoned Cart Emails', pro: true },
		{ title: 'Single Product Templates' },
		{ title: 'Checkout Templates' },
		{ title: 'Popular Payment Gateways (Stripe, PayPal, Mollie, etc)' },
		{ title: 'Subscriptions Management', pro: true },
		{ title: 'License Management', pro: true },
		{ title: 'Marketing & Automation Integrations' },
		{ title: 'Membership Management', pro: true, upcoming: true },
		{ title: 'Multivendor Marketplace', pro: true, upcoming: true },
		{ title: 'Learning Management System', pro: true, upcoming: true },
		{ title: 'Migration Tool' },
        { title: 'Bookings', pro: true, upcoming: true },
	];

	return (
        <div className="mt-24 py-[100px] px-[120px] rounded-xl bg-ec-table-stock">
            <h1 className='text-center text-4xl leading-[44px] font-medium text-ec-title'>
                The Best of Both Worlds
                <span className='block text-ec-primary'>EasyCommerce Free + Pro</span>
            </h1>

            <div className="w-full mt-[60px]">
                <div className="grid grid-cols-5 bg-ec-primary/10 rounded-lg py-5 px-8 text-xl font-medium text-ec-title">
                    <span className='col-span-3'>Features</span>
                    <span className='col-span-1 text-center'>Free</span>
                    <span className='col-span-1 text-center'>Pro</span>
                </div>

				{features.map((feature, index) => (
					<div key={index} className="grid grid-cols-5 bg-transparent hover:bg-white duration-300 border-b border-ec-primary/10 py-5 px-8 text-base font-medium text-ec-body">
						<span className='col-span-3'>
							{feature.title}
							{feature.upcoming && <span className='text-ec-primary'> (upcoming)</span>}
						</span>
						<span className='col-span-1'>{feature.pro ? noIcon : yesIcon}</span>
						<span className='col-span-1'>{yesIcon}</span>
					</div>
				))}
                
                <div className="grid grid-cols-5 items-center bg-transparent py-5 px-8 text-base font-medium text-ec-title">
                    <span className='col-span-3'></span>
                    <span className='col-span-1 text-center block'>Your Current Plan</span>
                    <span className='col-span-1'>
                        <a href='https://easycommerce.dev/pricing?utm_source=inplugin&utm_medium=propage&utm_campaign=BFCM' target='_blank' className="group flex items-center w-max gap-2.5 h-[48px] px-4 text-ec-title text-base font-medium rounded-lg border border-[#F99D1D] bg-[#F99D1D] mx-auto">
                            Upgrade to PRO
                        </a>
                    </span>
                </div>
            </div>
        </div>
    );
};

export default CompareTable;
