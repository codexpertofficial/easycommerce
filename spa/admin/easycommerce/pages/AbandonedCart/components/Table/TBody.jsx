import React from "react";

const TBody = ({
    abandonedCarts,
    columnList,
    deleteAbandonedCart,
    selectedCarts,
    toggleCart,
    onRemindClick,
}) => {

    const sortedCarts = [...abandonedCarts].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

    return (
        <tbody>
            {sortedCarts.map((cart, index) => (
                <>
                        <tr
                            key={index}
                            className="border-b border-ec-table-stock h-[70px] transition-shadow hover:shadow-[0px_4px_40px_0px_#00000014] group/carts"
                        >
                            {columnList.map((column) => {
                                if (column === "name") {
                                    return (
                                        <td key="name" className="p-5 w-[25%]">
                                            <div className="flex items-center">
                                            <input
                                                type="checkbox"
                                                checked={selectedCarts.includes(cart.hash)}
                                                onChange={() => toggleCart(cart.hash)}
                                                className="min-w-5 h-5 accent-ec-primary cursor-pointer mr-2 easycommerce-input-checkoutbox"
                                            />
                                            <div className="relative w-full min-w-0 h-10 ml-4 rtl:mr-4">
                                                <span className="text-sm text-ec-body font-inter font-normal absolute top-1/2 -translate-y-1/2 group-hover/carts:top-0 group-hover/carts:translate-y-0 duration-300">
                                                    {(cart.name || "(No Name)").length > 20 ? (cart.name || "(No Name)").slice(0, 20) + "…" : (cart.name || "(No Name)")}
                                                </span>
                                                <div className="invisible group-hover/carts:visible opacity-0 group-hover/carts:opacity-100 duration-300 absolute bottom-0">
                                                    <div className="flex items-center gap-1.5 font-inter font-normal text-xs text-ec-light-black">
                                                        <button
                                                            onClick={() => deleteAbandonedCart(cart.hash, cart.name)}
                                                            className="text-ec-red"
                                                        >
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                        </td>
                                    );
                                }

                                if (column === "email") {
                                    return (
                                        <td key="email" className="p-5 w-1/4">
                                            <span className="font-inter text-sm leading-[26px] text-ec-body">
                                                {cart.email}
                                            </span>
                                        </td>
                                    );
                                }

                                if (column === "items") {
                                    return (
                                        <td key="items" className="font-inter text-sm text-ec-body p-5 w-[20%]">
                                            {cart.product_names && cart.product_names.length > 0 ? (
                                                <ul className="space-y-0.5">
                                                    {cart.product_names.map((item, i) => (
                                                        <li key={i} className="flex items-center gap-1">
                                                            <span className="truncate max-w-[140px]">{item.name}</span>
                                                            <span className="text-ec-placeholder shrink-0">x{item.qty}</span>
                                                        </li>
                                                    ))}
                                                    {cart.distinct_products > 3 && (
                                                        <li className="text-ec-placeholder text-xs">+{cart.distinct_products - 3} more</li>
                                                    )}
                                                </ul>
                                            ) : (
                                                cart.items
                                            )}
                                        </td>
                                    );
                                }

                                if (column === "total") {
                                    return (
                                        <td key="total" className="font-inter text-sm text-ec-body p-5 w-[10%]">
                                            {cart.total}
                                        </td>
                                    );
                                }

                                if (column === "Last Activity") {
                                    return (
                                        <td key="last-activity" className="font-inter text-sm text-ec-body capitalize p-5 w-1/5">
                                            {cart.updated_at}
                                        </td>
                                    );
                                }

                                if (column === "reminders") {
                                    return (
                                        <td key="reminders" className="font-inter text-sm text-ec-body capitalize p-5 w-[10%]">
                                            {cart.reminders}
                                        </td>
                                    );
                                }

                                if (column === "actions") {
                                    return (
                                        <td key="actions" className="p-5 w-[10%]">
                                            <button
                                                onClick={() => onRemindClick(cart)}
                                                className="relative h-ec-input group text-sm font-inter text-ec-primary font-medium border border-ec-primary py-2 
                                                px-3 rounded-lg hover:bg-ec-primary hover:text-white transition duration-200 ease-in-out flex items-center gap-2"
                                            >
                                                Remind
                                                <svg width="13" height="12" viewBox="0 0 13 12" fill="none" className="fill-current" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11.6719 0.140625C11.9219 0.3125 12.0234 0.5625 11.9766 0.890625L10.4766 10.6172C10.4297 10.8516 10.3047 11.0312 10.1016 11.1562C10.0078 11.2031 9.88281 11.2344 9.72656 11.25C9.63281 11.25 9.53906 11.2344 9.44531 11.2031L6.60938 10.0078L4.61719 11.9531C4.57031 11.9844 4.52344 12 4.47656 12C4.42969 12 4.38281 11.9531 4.33594 11.8594L2.88281 8.4375L0.46875 7.42969C0.1875 7.28906 0.03125 7.07031 0 6.77344C0 6.49219 0.125 6.25781 0.375 6.07031L10.875 0.09375C11 0.03125 11.125 0 11.25 0C11.4062 0 11.5469 0.046875 11.6719 0.140625ZM0.75 6.75L2.90625 7.64062L9.65625 1.66406L0.75 6.75ZM4.71094 10.8281L5.92969 9.67969L5.10938 9.35156C5 9.30469 4.92969 9.21875 4.89844 9.09375C4.86719 8.98438 4.88281 8.88281 4.94531 8.78906L8.92969 3.32812L3.53906 8.08594L4.71094 10.8281ZM9.77344 10.3828L11.1328 1.54688L5.83594 8.83594L9.77344 10.3828Z"/>
                                                </svg>
                                            </button>
                                        </td>
                                    );
                                }

                                return null;
                            })}
                        </tr>
                </>
            ))}
        </tbody>
    );
};

export default TBody;
