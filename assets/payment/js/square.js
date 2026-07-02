jQuery(function ($) {
    let squareCard;
    let isInitializing = false;

    if ( !document.getElementById("easycommerce-checkout") ){
        return;
    }

    const squareForm   = $("#easycommerce-checkout");
    const submitButton = squareForm.find('[type="submit"]');
    let isProcessing   = false;

    async function initializeSquare() {
        if (isInitializing) {
            return;
        }

        // Check if card already attached
        if (document.querySelector('#easycommerce_square_payment_form .sq-card-wrapper iframe.sq-card-component')) {
            return;
        }

        // prevent multiple inits
        isInitializing = true;

        if (!window.Square) {
            console.error("Square.js failed to load properly");
            isInitializing = false;
            return;
        }

        try {
            const { application_id: appId, location_id: locationId } = EASYCOMMERCE.square;

            const payments = window.Square.payments(appId, locationId);
            squareCard = await payments.card();

            await squareCard.attach("#easycommerce_square_payment_form");

        } catch (e) {
            console.error("Error initializing Square payments:", e);
            squareCard = null;
        } finally {
            isInitializing = false;
        }
    }

    function handleSquareTransaction(token) {
        return new Promise((resolve) => {
            const hiddenInput = $("<input>", {
                type: "hidden",
                name: "meta[squareToken]",
                value: token
            });
            squareForm.append(hiddenInput);
            resolve();
        });
    }

    void initializeSquare();

    // Reinit when checkout form is rendered
    $(document).on("easycommerceRenderPaymentForm", function () {
        setTimeout(initializeSquare, 100);
    });

    // Handle payment method selection changes
    $(document).on('change', 'input[name="easycommerce-payment_method"]', function () {
        if ($(this).val() === 'square') {
            isInitializing = false;
            void initializeSquare();
        }
    });

    squareForm.on("submit", async function (event) {
        event.preventDefault();

        if (isProcessing || !$("#easycommerce-payment_method-square").prop("checked") || !squareCard) {
            return;
        }

        isProcessing = true;
        submitButton.prop("disabled", true);

        try {
            const result = await squareCard.tokenize();

            if (result.error) {
                $("#easycommerce_square_payment_errors").text(result.error.message);
                isProcessing = false;
                submitButton.prop("disabled", false);
            } else {
                await handleSquareTransaction(result.token);
                squareForm.trigger("payment");
            }
        } catch (error) {
            console.error("Error generating Square token:", error);
            isProcessing = false;
            submitButton.prop("disabled", false);
        }
    });
});
