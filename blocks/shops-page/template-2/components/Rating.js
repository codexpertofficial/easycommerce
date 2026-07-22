import React from "react";
import { __ } from "@wordpress/i18n";

const StarRating = ({ rating, attributes }) => {
    const fullStar =
        EASYCOMMERCE.assets + "/common/img/blocks/shop-page/full-star.png";

    const halfStar =
        EASYCOMMERCE.assets + "/common/img/blocks/shop-page/half-star.png";

    const emptyStar =
        EASYCOMMERCE.assets + "/common/img/blocks/shop-page/empty-star.png";

    
     // Ensure starSize has a default value and proper unit
    const starSizeValue = attributes.starSize || 13;
    const starSizeStyle = `${starSizeValue}px`;

    const stars = Array.from({ length: 5 }, (_, index) => {
        if (rating >= index + 1) {
            return (
                <span key={index}>
                    <img
                        src={fullStar}
                        alt={__("full star", "easycommerce")}
                        style={{
                            height: starSizeStyle,
                            width: starSizeStyle,
                            display: 'block'
                        }}
                    />
                </span>
            );
        } else if (rating > index && rating < index + 1) {
            return (
                <span key={index}>
                    <img
                        src={halfStar}
                        alt={__("half star", "easycommerce")}
                        style={{
                            height: starSizeStyle,
                            width: starSizeStyle,
                            display: 'block'
                        }}
                    />
                </span>
            );
        } else {
            return (
                <span key={index}>
                    <img
                        src={emptyStar}
                        alt={__("empty star", "easycommerce")}
                        style={{
                            height: starSizeStyle,
                            width: starSizeStyle,
                            display: 'block'
                        }}
                    />
                </span>
            );
        }
    });

    return (
        <div className="flex flex-row items-center gap-1 mt-2">
            {stars}
            <span
                className="ml-2"
                style={{
                    color:
                        attributes.ratingColor || "var(--color-ec-secondary)",
                    fontSize: attributes.ratingFontSize
                        ? `${attributes.ratingFontSize}px`
                        : "16px",
                    fontWeight: attributes.ratingFontWeight || "400",
                    textTransform: attributes.ratingTextTransform || "none",
                    fontStyle: attributes.ratingStyle || "normal",
                    textDecoration: attributes.ratingDecoration || "none",
                    lineHeight: attributes.ratingLineHeight
                        ? `${attributes.ratingLineHeight}px`
                        : "26px",
                    letterSpacing: attributes.ratingSpacing
                        ? `${attributes.ratingSpacing}px`
                        : "0px",
                }}
            >
                ({rating})
            </span>
        </div>
    );
};

export default StarRating;
