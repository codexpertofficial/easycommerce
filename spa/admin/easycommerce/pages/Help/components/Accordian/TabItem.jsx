//icon
const icon = `${EASYCOMMERCE.assets}admin/img/icons/question-mark.png`;

const TabItem = ({ item, isActive, onClick }) => {
    return (
        <>
            {!isActive ? (
                <div
                    onClick={onClick}
                    className="p-4 flex items-center cursor-pointer w-full rounded-lg hover:bg-ec-table-stock duration-300"
                >
                    <p className="font-inter text-sm font-normal text-ec-body">
                        {item.title}
                    </p>
                </div>
            ) : (
                <div className="p-4 flex items-center cursor-pointer w-full bg-ec-table-stock rounded-lg">
                    <p className="text-sm font-inter font-normal text-ec-body">
                        {item.title}
                    </p>
                </div>
            )}
        </>
    );
};

export default TabItem;
