const DashboardButton = ( { url } ) => {
    return (
        <>
            <a href={url} className="text-sm text-[#7A7A99] font-inter font-normal flex items-center cursor-pointer">
                View all
            </a>
        </>
    );
};
export default DashboardButton;