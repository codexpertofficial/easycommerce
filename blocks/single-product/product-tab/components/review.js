import { useState } from "react";
import { useEffect } from "react";
import StarRating from "../../rating/components/rating";

const Review = ({ postId }) => {
    const [isLoading, setIsLoading] = useState(true);
    const [data, setData] = useState(null);

    useEffect(() => {
        const url = `${EASYCOMMERCE.rest_base}/products/${postId}/reviews`;

        fetch(url)
            .then((res) => res.json())
            .then((data) => {
                setIsLoading(false);

                if (data.success) {
                    setData(data.data.reviews);
                }
            });
    }, []);

    return (
        <>
            <div className="easycommerce-reviews-warpper">
                <div className="easycommerce-reviews-header mb-12">
                    {data?.length > 0 && (
                        <>
                            <h2 className="font-inter text-xl font-semibold leading-8 text-ec-body !mb-1">
                                Reviews
                            </h2>
                            <span className="text-ec-placeholder font-inter text-[12px] font-medium leading-5">
                                Showing {data?.length} reviews
                            </span>
                        </>
                    )}
                </div>
                <div className="easycommerce-reviews">
                    {!isLoading &&
                        data?.map((item) => (
                            <>
                                <div className="easycommerce-single-reivew mb-4">
                                    <div className="flex items-center">
                                        <img
                                            className="w-[45px] h-[45px] rounded-full border border-[#DBDBDB] mr-3"
                                            src={item?.user.photo}
                                            alt={item?.user.name}
                                        />
                                        <div>
                                            <h3 className="text-ec-body font-inter font-semibold !text-base !leading-[26px] !mb-[6px]">
                                                {item?.user.name}
                                            </h3>
                                            <span>
                                                <StarRating
                                                    rating={item?.rating}
                                                />
                                            </span>
                                        </div>
                                    </div>
                                    <div className="ml-[70px] border-b-2 border-b-ec-border">
                                        <p className="font-inter text-base leading-[26px] font-normal text-ec-body mt-4 mb-6">
                                            {item?.text}
                                        </p>
                                    </div>
                                </div>
                            </>
                        ))}
                </div>
                <div className="easycommerce-reviews-form border border-ec-border mt-11 rounded-xl p-6">
                    <h2 className="font-inter text-xl font-semibold leading-8 text-ec-body !mb-1">
                        Write a review
                    </h2>
                    <p className="font-inter !text-[12px] font-normal leading-5 !text-ec-secondary ">
                        Your email address will not be published. required
                        fields are marked
                        <span className="text-[#FF3A52] font-inter text-[12px]">
                            *
                        </span>
                    </p>
                    <form action="" className="easycommerce-review-form">
                        <div>
                            <textarea
                                className="w-full p-[15px] border border-ec-border mb-5 rounded-md resize-none focus:outline-none"
                                name=""
                                id=""
                                placeholder="Write your review"
                            ></textarea>
                        </div>
                        <button
                            className="mt-6 py-[15px] px-[45px] bg-ec-primary rounded-md text-white font-medium font-inter leading-[26px] "
                            type="button"
                        >
                            Submit Now
                        </button>
                    </form>
                </div>
            </div>
        </>
    );
};

export default Review;
