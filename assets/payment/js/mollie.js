jQuery(function ($) {

    let mollie, cardHolder, cardNumber, expiryDate, verificationCode;

    const $existingForm = $('#easycommerce-payment_method-mollie-form');
    if (! $existingForm.length) {
        return;
    }
	function setupMollie() {
		var profileId = EASYCOMMERCE.mollie.profile_id;
		var testMode  = EASYCOMMERCE.mollie.test_mode === '1';

		mollie = Mollie( profileId, { testmode: testMode } );

		cardHolder       = mollie.createComponent( 'cardHolder' );
		cardNumber       = mollie.createComponent( 'cardNumber' );
		expiryDate       = mollie.createComponent( 'expiryDate' );
		verificationCode = mollie.createComponent( 'verificationCode' );

		mountComponents();
	}

	function mountComponents() {
		cardHolder.mount( '#easycommerce_cardHolder' );
		cardNumber.mount( '#easycommerce_cardNumber' );
		expiryDate.mount( '#easycommerce_expiryDate' );
		verificationCode.mount( '#easycommerce_verificationCode' );
	}

	setupMollie();

	$( document ).on( 'easycommerceRenderPaymentForm', function() {
		cardHolder.unmount();
		cardNumber.unmount();
		expiryDate.unmount();
		verificationCode.unmount();

		mountComponents();
	} );

    function handleMollieToken(token) {
        return new Promise((resolve) => {
            let $hiddenInput = $("<input>", {
                type: "hidden",
                name: "meta[mollieToken]",
                value: token
            });
            $("#easycommerce-checkout").append($hiddenInput);
            resolve();
        });
    }

    const $form = $("#easycommerce-checkout");

    $form.on("submit", async function (event) {
        event.preventDefault();
        if (!$("#easycommerce-payment_method-mollie").prop("checked")) return;
        $(".easycommerce-checkout-main-btn").hide();
        $(".easycommerce-css-loader-wrapper").css("display", "flex");
        try {
            const { token, error } = await mollie.createToken();
            if (error) {
                console.error("Error creating Mollie token:", error.message);
                $("#easycommerce_mollie_payment_errors").text(error.message);
                $(".easycommerce-css-loader-wrapper").hide();
                $(".easycommerce-checkout-main-btn").show();
                return;
            }

            await handleMollieToken(token);
            $form.trigger("payment");
        } catch (err) {
            $(".easycommerce-css-loader-wrapper").hide();
            $(".easycommerce-checkout-main-btn").show();
            console.error("Unexpected error during form submission:", err);
        }
    });

	$("#easycommerce_cardHolder").on("click", function () {
		$(".mollie-component.mollie-component--cardHolder").addClass(
			"opacity-zero"
		);
	});
	$("#easycommerce_cardHolder").on("blur", function () {
		$(".mollie-component.mollie-component--cardHolder").removeClass(
			"opacity-zero"
		);
		$(".mollie-component.mollie-component--cardHolder").addClass(
			"opacity-normal"
		);
	});
	$("#easycommerce_cardNumber").on("click", function () {
		$(".mollie-component.mollie-component--cardNumber").addClass(
			"opacity-zero"
		);
	});
	$("#easycommerce_verificationCode").on("click", function () {
		$(".mollie-component.mollie-component--verificationCode").addClass(
			"opacity-zero"
		);
	});
});