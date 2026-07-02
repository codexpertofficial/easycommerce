import React, { useEffect, useState } from "react";
import AccordianItem from "./AccordionItem";

const Accordion = ({
    items,
    getPostContent,
    setIsLoadingContent,
    setPostTitle,
}) => {
    if (!items || typeof items !== 'object') {
        return null;
    }

    const itemsValues = Object.values(items);

    if (!itemsValues.length || !itemsValues[0]?.docs) {
        return null;
    }

    const [activeIndex, setActiveIndex] = useState(0);
    const [intialPostId, setIntialPostId] = useState(
        Object.keys(itemsValues[0].docs)[0]
    );

    const handleClick = (index) => {
        setIsLoadingContent(true);
        setActiveIndex(index === activeIndex ? null : index);
        setIntialPostId(Object.keys(itemsValues[index].docs)[0]);
    };

    intialPostId &&
        useEffect(() => {
            fetch(`${EASYCOMMERCE.rest_base}/connectivity/docs/${intialPostId}`,{
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": EASYCOMMERCE.nonce,
                }
            })
                .then((resp) => resp.json())
                .then((data) => {
                    setIsLoadingContent(false);
                    setPostTitle(data.data.doc.title);
                    getPostContent(data.data.doc.description);
                });
        }, [intialPostId]);

    return (
        <div className="flex flex-col gap-3">
            {itemsValues.map((item, index) => (
                <AccordianItem
                    key={index}
                    title={item.label}
                    content={item.docs}
                    isActive={index === activeIndex}
                    onClick={() => handleClick(index)}
                    getPostContent={getPostContent}
                    setIsLoadingContent={setIsLoadingContent}
                    setPostTitle={setPostTitle}
                />
            ))}
        </div>
    );
};

export default Accordion;
