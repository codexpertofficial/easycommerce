import React, { useEffect, useState } from "react";
import {__} from "@wordpress/i18n";
import parse from "html-react-parser";

// Components
import Socials from "./components/Socials";
import Accordion from "./components/Accordian";
import Feedback from "./components/Feedback";
import CommonHeader from "../../../common/components/CommonHeader";
import LoadingSkeleton from "./components/LoadingSkeleton";
import NotFound from "../../../common/NotFound";
import ContentSkeleton from "./components/ContentSkeleton";

//Images
const backgroundEffect = `${EASYCOMMERCE.assets}admin/img/icons/bg-effect.png`;
const downArrow = `${EASYCOMMERCE.assets}admin/img/icons/down-arrow.png`;
const noDataIcon = `${EASYCOMMERCE.assets}admin/img/nofound/no-data.png`;

const headerBG = `${EASYCOMMERCE.assets}admin/img/help/documentation-bg.png`;

const Help = () => {
    const [postContent, getPostContent] = useState("");
    const [postTitle, setPostTitle] = useState("");

    const [docs, setDocs] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isLoadingContent, setIsLoadingContent] = useState(true);

    useEffect(() => {
        fetch(`${EASYCOMMERCE.rest_base}/connectivity/docs`,{
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            }
        })
            .then((resp) => resp.json())
            .then((data) => {
                setIsLoading(false);
                if (data.success && data.data?.docs) setDocs(data.data.docs);
            });
    }, []);

    return (
        <>
            {/* <CommonHeader
                parentSlug="easycommerce"
                parentLavel="EasyCommerce"
                breadcumpSlug="Help & Support"
            /> */}

            <div className="mt-3 bg-white rounded-xl overflow-hidden">
                {!isLoading ? (
                    <>
                        {docs ? (
                            <>
                                <div 
                                    className="py-[68px] px-6 bg-ec-accent relative overflow-hidden"
                                    style={{ backgroundImage: `url(${headerBG})`, backgroundSize: 'cover', backgroundPosition: 'center' }}
                                >
                                    <h2 className="text-center text-white text-[38px] font-medium leading-[42px] mb-3 font-inter">
                                        {__("Documentation", "easycommerce")}
                                    </h2>
                                    <p className="w-[480px] mx-auto font-inter text-center text-white text-base leading-[26px] font-normal">
                                        {__("Detailed step-by-step guides on how to use and get the best out of EasyCommerce.", "easycommerce")}
                                    </p>

                                    <div className="absolute flex justify-end bottom-6 right-6">
                                        <Socials />
                                    </div>
                                </div>

                                <div className="flex bg-white">
                                    <div className="w-[354px] p-6 border-r border-ec-border">
                                        <Accordion
                                            items={docs}
                                            getPostContent={getPostContent}
                                            setIsLoadingContent={
                                                setIsLoadingContent
                                            }
                                            setPostTitle={setPostTitle}
                                        />
                                    </div>
                                    <div className="easycommerce-doc-content-wrap w-[calc(100%_-_354px)]">
                                        <div className="py-10 px-16">
                                            {!isLoadingContent && (
                                                <>
                                                    <h2 className="mb-6 text-ec-body font-inter leading-8 font-semibold text-2xl">
                                                        {postTitle}
                                                    </h2>
                                                    {parse(postContent)}
                                                </>
                                            )}

                                            {/* Content Area Skeleton Loader */}
                                            {isLoadingContent && (
                                                <ContentSkeleton />
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </>
                        ) : (
                            <NotFound
                                ImageUrl={noDataIcon}
                                title="Something went wrong."
                            />
                        )}
                    </>
                ) : (
                    <LoadingSkeleton />
                )}
            </div>
        </>
    );
};

export default Help;
