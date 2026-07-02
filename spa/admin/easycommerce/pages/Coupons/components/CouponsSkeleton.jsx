import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const CouponsSkeleton = () => {
    const numberOfRows = 5;
    const SkeletonHeight = 30;
    return (
        <div className="w-full h-full">
            <Skeleton
                height={30}
                width={215}
                style={{ marginBottom: "26px", marginTop: "10px" }}
            />
            {Array(numberOfRows)
                .fill(0)
                .map((_, index) => (
                    <p key={index}>
                        <Skeleton
                            height={SkeletonHeight}
                            style={{ marginBottom: "20px" }}
                        />
                    </p>
                ))}
        </div>
    );
};

export default CouponsSkeleton;
