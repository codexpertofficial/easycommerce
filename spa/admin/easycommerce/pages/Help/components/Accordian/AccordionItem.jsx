import { useEffect, useState } from "react";
import TabItem from "./TabItem";

const AccordionItem = ({
    title,
    content,
    isActive,
    onClick,
    getPostContent,
    setIsLoadingContent,
    setPostTitle,
}) => {
    const postsIds = Object.keys(content);
    const itemsLabel = Object.values(content);

    const getPost = async (id) => {
        try {
            const response = await fetch(
                `${EASYCOMMERCE.rest_base}/connectivity/docs/${id}`,
                {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                        "X-WP-Nonce": EASYCOMMERCE.nonce,
                    },
                }
            );
            const data = await response.json();
        
            setPostTitle(data.data.doc.title);
            getPostContent(data.data.doc.description);
        } catch (err) {
            console.error("Error fetching document:", err);
        } finally {
            setIsLoadingContent(false);
        }
    };

    const [activeTab, setActiveTab] = useState(0);

    const handleTab = (index, id) => {
        setIsLoadingContent(true);
        getPost(id);
        setActiveTab(index === activeTab ? null : index);
    };

    const arrowIcon = (
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="6" viewBox="0 0 11 6" fill="none" className={`duration-300 ${isActive ? "rotate-180" : "rotate-0"}`}>
            <path d="M9.87109 1.71094L5.71484 5.62109C5.56901 5.7487 5.41406 5.8125 5.25 5.8125C5.08594 5.8125 4.9401 5.7487 4.8125 5.62109L0.65625 1.71094C0.382812 1.40104 0.373698 1.09115 0.628906 0.78125C0.920573 0.507812 1.23047 0.498698 1.55859 0.753906L5.25 4.25391L8.96875 0.753906C9.27865 0.498698 9.57943 0.498698 9.87109 0.753906C10.1263 1.08203 10.1263 1.40104 9.87109 1.71094Z" className={isActive ? "fill-white" : "fill-[#3C3C42]"} />
        </svg>
    )

    return (
        <div className="border border-ec-table-stock rounded-lg">
            <div
                onClick={onClick}
                className={`flex items-center justify-between cursor-pointer py-4 px-5 hover:bg-ec-table-stock duration-300 ` + (isActive ? 'bg-ec-table-stock border-b border-ec-primary' : '')}
            >
                <p className="text-base font-inter text-ec-body">{title}</p>
                <span className={`w-6 h-6 flex items-center justify-center rounded-full border ${isActive ? 'bg-ec-primary border-ec-primary' : 'border-ec-table-stock bg-white'}`}>
                    {arrowIcon}
                </span>
            </div>

            <div className={isActive ? 'p-4 block' : 'hidden'}>
                {itemsLabel.map((item, index) => (
                    <>
                        <TabItem
                            key={index}
                            item={item}
                            isActive={index === activeTab}
                            onClick={() =>
                                handleTab(index, postsIds[index])
                            }
                        />
                    </>
                ))}
            </div>
        </div>
    );
};

export default AccordionItem;
