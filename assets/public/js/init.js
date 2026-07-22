const { __ } = wp.i18n;

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.custom-toast').forEach((button) => {
        button.addEventListener('click', function () {
            const type = this.dataset.type;
            const message = this.dataset.message;
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const cartContent = document.getElementById('easycommerce-cart-content');

    // Fetch and display cart data
    if (cartContent) {
        fetch(EASYCOMMERCE.rest_base + '/cart', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': EASYCOMMERCE.nonce,
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    if (data.data.length === 0) {
                        cartContent.innerHTML = `<p>${__('Your cart is empty.', 'easycommerce')}</p>`;
                        document.querySelector(
                            '.easycommerce-clear-cart'
                        ).style.display = 'none';
                        return;
                    }

                    const table = document.createElement('table');
                    table.className = 'min-w-full bg-white rounded-lg';
                    const thead = document.createElement('thead');
                    thead.className = 'bg-gray-200';
                    thead.innerHTML = `
                    <tr>
                        <th class="py-2 px-4 border-b font-semibold text-left">${__('Product', 'easycommerce')}</th>
                        <th class="py-2 px-4 border-b font-semibold text-left">${__('Quantity', 'easycommerce')}</th>
                        <th class="py-2 px-4 border-b font-semibold text-left">${__('Price', 'easycommerce')}</th>
                    </tr>
                `;
                    table.appendChild(thead);
                    const tbody = document.createElement('tbody');
                    data.data.forEach((item) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td class="py-2 px-4 border-b">
                            ${item.title} 
                            <button class="remove-item ml-2" data-id="${item.id}">&times;</button>
                        </td>
                        <td class="py-2 px-4 border-b">${item.quantity}</td>
                        <td class="py-2 px-4 border-b">${item.price}</td>
                    `;
                        tbody.appendChild(row);
                    });
                    table.appendChild(tbody);
                    cartContent.appendChild(table);

                    // Add event listener to remove buttons
                    document
                        .querySelectorAll('.remove-item')
                        .forEach((button) => {
                            button.addEventListener('click', function () {
                                const productId = this.getAttribute('data-id');

                                fetch(EASYCOMMERCE.rest_base + '/cart/remove', {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-WP-Nonce': EASYCOMMERCE.nonce,
                                    },
                                    body: JSON.stringify({ id: productId }),
                                })
                                    .then((response) => response.json())
                                    .then((resp) => {
                                        if (resp.success) {
                                            alert('Product removed from cart.');
                                            location.reload();
                                        } else {
                                            alert(
                                                'Failed to remove product from cart.'
                                            );
                                        }
                                    });
                            });
                        });

                    // Add event listener to clear button
                    const clearButton = document.querySelector(
                        '.easycommerce-clear-cart'
                    );
                    if (clearButton) {
                        clearButton.addEventListener('click', function () {
                            fetch(EASYCOMMERCE.rest_base + '/cart/clear', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                                },
                            })
                                .then((response) => response.json())
                                .then((resp) => {
                                    if (resp.success) {
                                        alert('Cart cleared.');
                                        location.reload();
                                    } else {
                                        alert('Failed to clear cart.');
                                    }
                                });
                        });
                    }
                } else {
                    cartContent.innerHTML = `<p>${__('Failed to load cart.', 'easycommerce')}</p>`;
                }
            })
            .catch((error) => {
                cartContent.innerHTML = `<p>${__('Error loading cart.', 'easycommerce')}</p>`;
            });
    }

    // Add product to cart
    const button = document.querySelector('.easycommerce-add-to-cart-button');
    const buttonText = document.getElementById('buttonText');
    const loader = document.getElementById('loader');

    if (button) {
        button.addEventListener('click', () => {
            const quantityInput = document.querySelector(
                '.easycommerce-qunatity-input'
            )?.value;
            if (quantityInput == 0 || quantityInput == null) return;

            const originalButtonHtml = button.innerHTML;
            buttonText.style.display = 'none';
            loader.style.display = 'inline-block';
            button.classList.add('loading');
            const products = [
                {
                    id: EASYCOMMERCE.product_id,
                    price_id: document.querySelector(
                        '#easycommerce_variation_price_id'
                    ).value,
                    quantity: parseInt(
                        document.querySelector('#quantity').value,
                        10
                    ),
                },
            ];

            fetch(EASYCOMMERCE.rest_base + '/cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
                body: JSON.stringify({ products: products }),
            })
                .then((response) => response.json())
                .then((data) => {
                    loader.style.display = 'none';
                    button.classList.remove('loading');
                    if (data.success) {
                        // Redirect to the checkout page if Direct checkout is enabled.
                        if ( Boolean( EASYCOMMERCE.direct_checkout ) ) {
                            window.location.href = data.data.redirect;

                            return;
                        }

                        buttonText.style.display = 'inline';
                        button.innerHTML = `<span class='material-symbols-outlined'> 
                                                <svg width="21" height="16" viewBox="0 0 21 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M20.5781 0.921875C20.8594 1.23438 21 1.59375 21 2C21 2.40625 20.8594 2.76562 20.5781 3.07812L8.57812 15.0781C8.26562 15.3594 7.90625 15.5 7.5 15.5C7.09375 15.5 6.73438 15.3594 6.42188 15.0781L0.421875 9.07812C0.140625 8.76562 0 8.40625 0 8C0 7.59375 0.140625 7.23438 0.421875 6.92188C0.734375 6.64062 1.09375 6.5 1.5 6.5C1.90625 6.5 2.26562 6.64062 2.57812 6.92188L7.45312 11.8906L18.4219 0.921875C18.7344 0.640625 19.0938 0.5 19.5 0.5C19.9062 0.5 20.2656 0.640625 20.5781 0.921875Z" fill="white"/>
                                                </svg>
                                            </span>`;
                        document.querySelector(
                            '.easycommerce-single-product-checkout-btn'
                        ).style.display = 'block';

                        setTimeout(function () {
                            button.innerHTML = originalButtonHtml;
                        }, 3000);
                    } else {
                        button.innerHTML = originalButtonHtml;
                        const message =
                            (data && data.data && (data.data.message || (typeof data.data === 'string' ? data.data : null))) ||
                            __('Failed to add to cart. Please try again.', 'easycommerce');
                        if (typeof easycommerce_error_toast === 'function') {
                            easycommerce_error_toast(message);
                        }
                    }
                })
                .catch((error) => {
                    buttonText.style.display = 'inline';
                    loader.style.display = 'none';
                    button.classList.remove('loading');
                    button.innerHTML = originalButtonHtml;
                    if (typeof easycommerce_error_toast === 'function') {
                        easycommerce_error_toast(__('Failed to add to cart. Please try again.', 'easycommerce'));
                    }
                });
        });
    }
});

// Add event listeners to quantity buttons
jQuery(function ($) {
    $(document).on('click', '.easycommerce-quantity-minus-btn', function () {
        let quantity = $('.easycommerce-qunatity-input').val();
        if (quantity > 1) {
            $('.easycommerce-qunatity-input').val(parseInt(quantity) - 1);
        }
    });

    $(document).on('click', '.easycommerce-quantity-plus-btn', function () {
        let type = $('.easycommerce-qunatity-input').attr('data-type');
        let quantity = $('.easycommerce-qunatity-input').val();
        let maxQuantity = parseInt(
            $('.easycommerce-qunatity-input').attr('data-stock-count')
        );
        if (type == 'digital' && quantity >= 1) return;
        if (quantity >= maxQuantity) return;
        $('.easycommerce-qunatity-input').val(parseInt(quantity) + 1);
    });
});

