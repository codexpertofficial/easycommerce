const StarRating = ({ rating }) => {
    const fullStar =
        EASYCOMMERCE.assets + "common/img/blocks/shop-page/full-star.png";

    const halfStar =
        EASYCOMMERCE.assets + "common/img/blocks/shop-page/half-star.png";

    const emptyStar =
        EASYCOMMERCE.assets + "common/img/blocks/shop-page/empty-star.png";

    const stars = Array.from({ length: 5 }, (_, index) => {
        if (rating >= index + 1) {
            return (
                <span key={index}>
                    <img src={fullStar} className="w-3 h-3" />
                </span>
            );
        } else if (rating > index && rating < index + 1) {
            return (
                <span key={index}>
                    <img src={halfStar} className="w-3 h-3" />
                </span>
            );
        } else {
            return (
                <span key={index}>
                    <img src={emptyStar} className="w-3 h-3" />
                </span>
            );
        }
    });

    return (
        <div className="flex flex-row items-center gap-1">
            {stars}
            <span className="ml-2 text-ec-secondary">({rating})</span>
        </div>
    );
};

export default StarRating;
