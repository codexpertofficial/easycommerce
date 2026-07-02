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
                        cartContent.innerHTML = `<p>${EASYCOMMERCE.i18n.cart.empty}</p>`;
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
                        <th class="py-2 px-4 border-b font-semibold text-left">${EASYCOMMERCE.i18n.title}</th>
                        <th class="py-2 px-4 border-b font-semibold text-left">${EASYCOMMERCE.i18n.quantity}</th>
                        <th class="py-2 px-4 border-b font-semibold text-left">${EASYCOMMERCE.i18n.price}</th>
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
                    cartContent.innerHTML = `<p>${EASYCOMMERCE.i18n.cart.failed_to_load}</p>`;
                }
            })
            .catch((error) => {
                cartContent.innerHTML = `<p>${EASYCOMMERCE.i18n.cart.error_loading}</p>`;
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
                            'Failed to add to cart. Please try again.';
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
                        easycommerce_error_toast('Failed to add to cart. Please try again.');
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

//User Registration
jQuery(function ($) {
    $('#easycommerce-registration-form').submit(function (e) {
        e.preventDefault();

        // Collect form data
        var formData = {
            username: $('#easycommerce-register-username').val(),
            email: $('#easycommerce-register-email').val(),
            password: $('#easycommerce-register-new-password').val(),
            confirmPassword: $('#easycommerce-register-confirm-password').val(),
        };

        // Send AJAX request
        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/connectivity/registration`,
            type: 'POST',
            data: formData,
            headers: {
                'X-WP-Nonce': EASYCOMMERCE.nonce,
            },
            success: function (res) {
                if (res.success === true) {
                    window.location.href = res.data.redirect_url;
                } else {
                    $('#easycommerce-registration-error-message')
                        .show()
                        .text(res.data.message);
                }
            },
            error: function (xhr, status, error) {
                $('#easycommerce-registration-error-message')
                    .show()
                    .text(error);
            },
        });
    });

    // Reset Password Form
    $('#easycommerce-reset-form').on('submit', function (e) {
        e.preventDefault();

        $('#easycommerce-reset-error-message').hide();
        
        const submitButton = $(this).find('button[type="submit"]');
        const originalText = submitButton.text();
        submitButton.text('Sending...').prop('disabled', true);
        const userLogin = $('#easycommerce-reset-email-username').val();

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/connectivity/reset-password`,
            type: 'POST',
            data: {
                user_login: userLogin
            },
            headers: {
                'X-WP-Nonce': EASYCOMMERCE.nonce,
            },
            success: function (response) {
                if (response.success === true) {
                    $('#easycommerce-reset-error-message').removeClass('text-red-600').addClass('text-green-600').show().text(response.data.message);
                    $('#easycommerce-reset-email-username').val('');
                } else {
                    $('#easycommerce-reset-error-message')
                        .removeClass('text-green-600')
                        .addClass('text-red-600')
                        .show()
                        .text(response.data.message);
                }
            },
            error: function (xhr, status, error) {
                let errorMessage = 'An error occurred. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMessage = xhr.responseJSON.data.message;
                }
                
                $('#easycommerce-reset-error-message')
                    .removeClass('text-green-600')
                    .addClass('text-red-600')
                    .show()
                    .text(errorMessage);
            },
            complete: function() {
                submitButton.text(originalText).prop('disabled', false);
            }
        });
    });

    // Reset Password Confirmation
    const resetPasswordForm = $("#easycommerce-reset-password-form");
    if (resetPasswordForm.length === 0) return;

    resetPasswordForm.on("submit", function (e) {
        e.preventDefault();

        const newPassword = $("#new-password").val();
        const confirmPassword = $("#confirm-password").val();
        const resetKey = $("#reset-key").val();
        const resetLogin = $("#reset-login").val();
        const errorMessage = $("#easycommerce-reset-password-error-message");
        errorMessage.hide();
        const submitButton = resetPasswordForm.find("button[type='submit']");
        const originalText = submitButton.text();
        submitButton.text("Resetting...").prop("disabled", true);

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/connectivity/reset-password-confirm`,
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                key: resetKey,
                login: resetLogin,
                password: newPassword,
                confirm_password: confirmPassword,
            }),
            headers: {
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            success: function (response) {
                if (response.success === true) {
                window.location.href = response.data.redirect_url;
                } else {
                errorMessage.text(response.data.message).show();
                }
            },
            error: function () {
                errorMessage.text("An error occurred. Please try again.").show();
            },
            complete: function () {
                submitButton.text(originalText).prop("disabled", false);
            },
        });
    });

});