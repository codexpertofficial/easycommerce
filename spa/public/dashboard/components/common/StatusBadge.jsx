import React from "react";

/**
 * Order-status pill. Colours come from the shared EasyCommerce status tokens
 * (see tailwind.config.js) so the badge always matches the rest of the admin.
 *
 * Class strings are written out in full (not interpolated) so Tailwind's JIT
 * scanner keeps them in the build.
 */
const STATUS_CLASSES = {
    completed:          "text-ec-completedText bg-ec-completedBg",
    processing:         "text-ec-processingText bg-ec-processingBg",
    pending:            "text-ec-pendingText bg-ec-pendingBg",
    on_hold:            "text-ec-onHoldText bg-ec-onHoldBg",
    refunded:           "text-ec-refundedText bg-ec-refundedBg",
    partially_refunded: "text-ec-partiallyRefundedText bg-ec-partiallyRefundedBg",
    cancelled:          "text-ec-cancelledText bg-ec-cancelledBg",
    failed:             "text-ec-failedText bg-ec-failedBg",
};

const StatusBadge = ({ status, label }) => {
    const classes = STATUS_CLASSES[status] || "text-ec-body bg-ec-table-bg";
    const text =
        label ??
        (EASYCOMMERCE.order_statuses?.[status] ?? (status || "").replace(/_/g, " "));

    return (
        <span
            className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold capitalize whitespace-nowrap ${classes}`}
        >
            <span className="w-1.5 h-1.5 rounded-full bg-current opacity-70" />
            {text}
        </span>
    );
};

export default StatusBadge;
