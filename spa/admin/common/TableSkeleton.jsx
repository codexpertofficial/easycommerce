import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const TableSkeleton = ({ numberOfRows = 5, SkeletonHeight = 20 }) => {
    return (
        <div className="w-full h-full">
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

export default TableSkeleton;
