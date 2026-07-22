import { useEffect, useState } from 'react';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

//Icons
const deleteIcon = `${EASYCOMMERCE.assets}public/icons/delete-14-16.png`;
const deleteIconRed = `${EASYCOMMERCE.assets}public/icons/delete-red-14-16.png`;

// //Demo Images
const productImg = `${EASYCOMMERCE.assets}public/img/checkout/headphone.png`;
const yourOrderImage = `${EASYCOMMERCE.assets}public/img/checkout/your-order.png`;

const Cart = () => {
    const [quantity, setQuantity] = useState(1);
    // const [carts, getCarts] = useState([]);

    /**
     * Filters the cart items displayed in the checkout.
     *
     * @since 1.0.0
     * @param {Array} cartItems Array of cart item objects.
     */
    const defaultCartItems = [
        {
            id: 1,
            name: 'Music headphone',
            variant: 'Black',
            price: 152,
            image: productImg,
        },
        {
            id: 2,
            name: 'Music headphone',
            variant: 'Black',
            price: 152,
            image: productImg,
        },
    ];
    const cartItems = applyFilters('easycommerce.checkout.cart.items', defaultCartItems);

    //get Cart Data
    useEffect(() => {
        const fetchProducts = async () => {
            try {
                const response = await fetch(EASYCOMMERCE.rest_base + '/cart',{
                    headers: {
                        "Content-Type": "application/json",
                        "X-WP-Nonce": EASYCOMMERCE.nonce,
                    }
                });
                const data = await response.json();
            } catch (error) {
            }
        };

        fetchProducts();
    }, []);

    return (
        <>
            <div className="easycommerce-cart-wrapper border border-ec-border bg-white rounded-lg p-[30px] mb-5">
                <div className="flex items-center mb-[30px]">
                    <img
                        src={yourOrderImage}
                        className="w-[53px] h-[53px] mr-4"
                    />
                    <h3 className="font-inter leading-8 font-semibold text-xl mb-0">
                        {__( 'Your Orders', 'easycommerce' )}
                    </h3>
                </div>
                 <div>
                     {cartItems.map((item, index) => (
                         <div key={index} className="easycommerce-cart-product flex items-center justify-between pt-5 pb-5">
                             <div className="flex items-center w-[calc(100%_-_60px)]">
                                 <div className="w-[80px] h-[90px] rounded-lg bg-[#F0F2F4] flex items-center justify-center mr-5">
                                     <img src={item.image} />
                                 </div>
                                 <div className="w-[calc(100%_-_100px)]">
                                     <h5 className="font-inter text-ec-body font-medium leading-[26px] text-base mb-1">
                                         {item.name}
                                     </h5>
                                     <span className="block mb-[13px] text-ec-placeholder font-inter font-normal leading-5 text-[12px]">
                                         {item.variant}
                                     </span>
                                     <div className="flex items-center gap-[10px]">
                                         <button
                                             type="button"
                                             className="easycommerce-qunatity-button border border-ec-border w-[23px] h-[21px] flex
                             items-center justify-center rounded-[3px] text-ec-secondary hover:bg-white"
                                             onClick={() =>
                                                 setQuantity(quantity - 1)
                                             }
                                         >
                                             -
                                         </button>
                                         <input
                                             className="easycommerce-qunatity-input font-inter leading-[12px] font-normal"
                                             type="text"
                                             value={quantity}
                                             onChange={(e) =>
                                                 setQuantity(e.target.value)
                                             }
                                         />
                                         <button
                                             type="button"
                                             className="easycommerce-qunatity-button border border-ec-border w-[23px] h-[21px] flex
                             items-center justify-center rounded-[3px] text-ec-secondary hover:bg-white"
                                             onClick={() =>
                                                 setQuantity(quantity + 1)
                                             }
                                         >
                                             +
                                         </button>
                                     </div>
                                 </div>
                             </div>
                             <div className="flex flex-col items-end gap-5 justify-center">
                                 <span className="text-ec-body text-base font-inter font-normal leading-[26px] p-[5px] border border-ec-border rounded-[4px] bg-[#F8F8F8]">
                                     ${item.price}
                                 </span>
                                 <button className="p-[10px] w-[35px] h-[35px] group rounded-[5px] bg-[#F8F8F8] hover:bg-[#FF3A520D] flex items-center justify-center">
                                     <img
                                         className="block group-hover:hidden"
                                         src={deleteIcon}
                                     />
                                     <img
                                         src={deleteIconRed}
                                         className="hidden group-hover:block"
                                     />
                                 </button>
                             </div>
                         </div>
                     ))}
                 </div>
            </div>
        </>
    );
};

export default Cart;
