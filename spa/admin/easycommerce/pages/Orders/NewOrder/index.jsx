import React, { useState } from "react";
import { __ } from "@wordpress/i18n";

// components
import Header from "../../../../common/Header";
import Total from "./components/Total";
import Items from "./components/Items";
import Customer from "./components/Customer";

const NewOrder = () => {
    const [order, setOrder] = useState({});

    return (
        <>
            <Header breadcumpSlug={__("New Order", "easycommerce")} />

            <div className="mt-[30px] ml-[30px] mr-[30px] max-w-full flex gap-5 items-start justify-between mb-10">
                <Customer
                    setCustomerId={(id) => {
                        setOrder((prev) => ({ ...prev, customer: id }));
                    }}
                    setCustomerAddress={(billing, shipping) =>
                        setOrder((prev) => ({
                            ...prev,
                            billing_address: billing,
                            shipping_address: shipping,
                        }))
                    }
                />

                <div className="w-2/5 2xl:w-2/4 bg-white min-h-svh">
                    <Items
                        user_id={order?.customer}
                        setCartItemsHash={(hash) =>
                            setOrder((prev) => ({ ...prev, items: hash }))
                        }
                    />
                </div>
                <div className="w-[35%] 2xl:w-1/4 bg-white min-h-svh">
                    <Total order={order} />
                </div>
            </div>
        </>
    );
};

export default NewOrder;
