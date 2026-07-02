import React from 'react';

// components
import CommonHeader from '../../../common/components/CommonHeader';
import Feedback from '../Help/components/Feedback';
import FreeComponent from './components/FreeComponent';
import Activated from './components/Activated';

const Pro = () => {
	return (
		<>
			{/* <CommonHeader
				parentSlug="easycommerce"
				parentLavel="EasyCommerce"
				breadcumpSlug="Pro"
			/> */}

            <div className="mt-3 bg-white max-w-full px-[48px] rounded-xl font-inter">
                {EASYCOMMERCE.pro.activated ? (
                    <Activated />
                ) : (
                    <FreeComponent />
                )}
            </div>
		</>
	);
};

export default Pro;
