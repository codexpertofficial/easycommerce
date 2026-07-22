let braintreeInstance;
let braintreeInitializing = false;

document.addEventListener("DOMContentLoaded", async function () {
    if ( !document.getElementById("easycommerce-checkout") ){
        return;
    }

    // initializeBraintreePayment();
    // Add event listener for re-rendering the payment form
    document.addEventListener("easycommerceRenderPaymentForm", initializeBraintreePayment);
});

async function initializeBraintreePayment() {
    const { __ } = wp.i18n;

    if (braintreeInitializing) return;

    const authorizationToken = EASYCOMMERCE.braintree.tokenization_key;
    const errorContainer = document.getElementById("easycommerce_braintree_payment_errors");
    const form = document.getElementById("easycommerce-checkout");

    if (!window.braintree || !authorizationToken) {
        errorContainer.textContent = __("Braintree.js failed to load or missing authorization token.", 'easycommerce');
        return;
    }

    braintreeInitializing = true;

    if (braintreeInstance) {
        try {
            await new Promise((resolve) => braintreeInstance.teardown(resolve));
        } catch (e) {}
        braintreeInstance = null;
    }

    try {
        braintreeInstance = await initializeBraintree(authorizationToken);
        errorContainer.textContent = "";
    } catch (error) {
        braintreeInitializing = false;
        errorContainer.textContent = __("Error initializing Braintree.", 'easycommerce');
        return;
    }

    braintreeInitializing = false;

    [].forEach.call(
        document.querySelectorAll('#easycommerce_braintree_payment_form .braintree-heading'),
        ( element ) => {
            element.style.display = 'none';
        }
    );
    document.querySelector("#easycommerce_braintree_payment_form .braintree-placeholder").style.display = "none";

	form.removeEventListener("submit", handleBraintreeSubmit);
    form.addEventListener("submit", handleBraintreeSubmit);
}

async function handleBraintreeSubmit(event) {
    const { __ } = wp.i18n;

    event.preventDefault();
    const form = document.getElementById("easycommerce-checkout");
    const errorContainer = document.getElementById("easycommerce_braintree_payment_errors");

    if (!document.querySelector("#easycommerce-payment_method-braintree")?.checked) return;

    document.querySelector(".easycommerce-checkout-main-btn").style.display = "none";
    document.querySelector(".easycommerce-css-loader-wrapper").style.display = "flex";

    try {
        const payload = await requestBraintreePaymentMethod(braintreeInstance);
        if (!payload.nonce) {
            errorContainer.textContent = __("Failed to get payment nonce.", 'easycommerce');
            document.querySelector(".easycommerce-css-loader-wrapper").style.display = "none";
            document.querySelector(".easycommerce-checkout-main-btn").style.display = "flex";
            return;
        }
        await handleBraintreeTransaction(payload.nonce);
        form.dispatchEvent(new Event("payment"));
    } catch (error) {
        document.querySelector(".easycommerce-css-loader-wrapper").style.display = "none";
        document.querySelector(".easycommerce-checkout-main-btn").style.display = "flex";
        console.error("Error processing Braintree payment:", error);
        errorContainer.textContent = __("Error processing payment.", 'easycommerce');
    }
};


// Initialize Braintree Drop-in UI
async function initializeBraintree(authorizationToken) {
	return new Promise((resolve, reject) => {
		braintree.dropin.create(
			{
				authorization: authorizationToken,
				selector: "#easycommerce_braintree_payment_form",
			},
			function (err, instance) {
				if (err) {
					reject(err);
				} else {
					resolve(instance);
				}
			}
		);
	});
}
function requestBraintreePaymentMethod(instance) {
	return new Promise((resolve, reject) => {
		instance.requestPaymentMethod(function (err, payload) {
			if (err) {
				reject(err);
			} else {
				resolve(payload);
			}
		});
	});
}
function handleBraintreeTransaction(nonce) {
	return new Promise((resolve) => {
		const hiddenInput = document.createElement("input");
		hiddenInput.setAttribute("type", "hidden");
		hiddenInput.setAttribute("name", "meta[braintreeNonce]");
		hiddenInput.setAttribute("value", nonce);
		const form = document.getElementById("easycommerce-checkout");
		form.appendChild(hiddenInput);
		resolve();
	});
}
