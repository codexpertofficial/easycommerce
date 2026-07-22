import React, { useState, useEffect } from "react";
import apiFetch from '@wordpress/api-fetch';
import TableSkeleton from "../../../../common/TableSkeleton";
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
const tabs = ['All', 'Orders', 'Refunds', 'Reviews', 'Others'];

const RecentActivities = ({ range = 'last-30' }) => {
    const [activeTab, setActiveTab] = useState('All');
    const [activities, setActivities] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    const getDateRange = (rangeValue) => {
        const today = new Date();
        let fromDate, toDate;

        switch (rangeValue) {
            case 'last-7':
                fromDate = new Date(today);
                fromDate.setDate(fromDate.getDate() - 7);
                toDate = new Date(today);
                break;
            case 'last-30':
                fromDate = new Date(today);
                fromDate.setDate(fromDate.getDate() - 30);
                toDate = new Date(today);
                break;
            case 'this-week':
                fromDate = new Date(today);
                fromDate.setDate(fromDate.getDate() - fromDate.getDay());
                toDate = new Date(fromDate);
                toDate.setDate(toDate.getDate() + 6);
                break;
            case 'this-month':
                fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            case 'this-year':
                fromDate = new Date(today.getFullYear(), 0, 1);
                toDate = new Date(today.getFullYear(), 11, 31);
                break;
            default:
                fromDate = new Date(today);
                fromDate.setDate(fromDate.getDate() - 30);
                toDate = new Date(today);
        }

        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        return {
            from: formatDate(fromDate),
            to: formatDate(toDate),
        };
    };

   useEffect(() => {
        setIsLoading(true);
        const dateRange = getDateRange(range);

        apiFetch({
            path: addQueryArgs('/easycommerce/v1/dashboard/activities', {
                type: activeTab,
                from: dateRange.from,
                to: dateRange.to,
            }),
        }).then((data) => {
            setIsLoading(false);
            if (data.success) {
                setActivities(data.data.activities);
            }
        }).catch(() => {
            setIsLoading(false);
        });
    }, [range, activeTab]);

    const filtered = activeTab === 'All' ? activities : activities.filter(a => a.type === activeTab);

    return (
        <>  
            <div className="flex items-center gap-2 mb-4">
                {tabs.map((tab) => (
                    <button
                        key={tab}
                        onClick={() => setActiveTab(tab)}
                        className={`px-2 py-1 rounded-lg text-sm transition-all duration-200 ${
                            activeTab === tab
                                ? 'bg-[#E9E9E9] text-[#101828]'
                                : 'text-[#4A5565] hover:bg-[#F8F8FB]'
                        }`}
                    >
                        {tab}
                    </button>
                ))}
            </div>

            <div className="group overflow-hidden">
                <div className="flex flex-col overflow-y-auto max-h-[480px] pr-4 [&::-webkit-scrollbar]:w-1 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-transparent group-hover:[&::-webkit-scrollbar-thumb]:bg-[#EEF0FF]">
                    {isLoading ? (
                        <TableSkeleton numberOfRows={5} SkeletonHeight={35} />
                    ) : filtered.length === 0 ? (
                        <p className="text-sm text-center text-[#7A7A99] py-4">{__( 'No activities found.', 'easycommerce' )}</p>
                    ) : (
                        filtered.map((item, index) => (
                            <div key={index} className="flex gap-[10px] py-4 border-b border-[#F8F8F8] last:border-0">
                                <div className="mt-1.5 w-2 h-2 rounded-full bg-[#A3A3A3] shrink-0" />
                                <div className="flex-1">
                                    <div className="flex items-start justify-between gap-2">
                                        <p className="text-sm font-semibold text-[#272435]">{item.title}</p>
                                        <p className="text-xs text-[#6A7282] shrink-0">{item.date}</p>
                                    </div>
                                    <p className="text-sm text-[#364153] mt-1.5">{item.description}</p>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </>
    );
};

export default RecentActivities;