import React from 'react';
import Tooltip from '../../../../../common/components/Tooltip';

const PanelTitle = ({ title, notice = ''}) => {
    return (
        <>
            <div class="flex gap-3 items-center">
                <h3 className="font-inter text-lg font-medium text-[#121216]">
                    {title}
                </h3>
                
                {notice && (
                    <Tooltip text={notice} />
                )}
            </div>
        </>
    )
}

export default PanelTitle