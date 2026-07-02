jQuery(function ($) {
	$("#easycommerce-reset-settings").on("click", function (e) {
		e.preventDefault();
		easycommerce_modal();

		$.ajax({
			url: `${EASYCOMMERCE.rest_base}/option`,
			type: "DELETE",
			dataType: "JSON",
			data: {
				key: $(this).data("option_key"),
			},
			headers: {
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
			success: (resp) => {
				easycommerce_toast(resp.data);
				setTimeout(function(){
					location.reload();
				}, 1000)
			},
			error: (err) => {
				easycommerce_modal(false);
			},
		});
	});

	$(".easycommerce-settings-form").submit(function (e) {
		e.preventDefault();
		easycommerce_modal();

		$(this).find('input[type="checkbox"]').each(function () {
			if ( ! $(this).is(':checked') ) {
				$(this).after(
					$('<input>').attr({
						type: 'hidden',
						name: $(this).attr('name'),
						value: '0',
						class: 'ec-switch-hidden'
					})
				);
			}
		});

		let formData = $(this).serializeArray();
		let data = {};

		// Convert serialized data array into an object
		$.each(formData, function () {
			if (data[this.name]) {
				if (!data[this.name].push) {
					data[this.name] = [data[this.name]];
				}
				data[this.name].push(this.value || "");
			} else {
				data[this.name] = this.value || "";
			}
		});
		$(this).find('input.ec-switch-hidden').remove();

		$.ajax({
			url: `${EASYCOMMERCE.rest_base}/option`,
			type: "POST",
			dataType: "JSON",
			data: {
				key: $(this).data("option_key"),
				value: data,
			},
			headers: {
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
			success: (resp) => {
				easycommerce_modal(false);
				easycommerce_toast(resp.data);

                if ( resp.success && $(this).data('reload') ) {
                    setTimeout(function(){
                        location.reload();
                    }, 1000)
                }
			},
			error: (err) => {
				easycommerce_modal(false);
			},
		});
	});

	$(document).on('click', '#easycommerce-save-settings', function (e) {
		e.preventDefault();
		$('.easycommerce-settings-form').trigger('submit');
	});

	$(".easycommerce-field-image").on("click", function (event) {
			event.preventDefault();
			var self = $(this);
			var file_frame = (wp.media.frames.file_frame = wp.media({
				title: self.data("title"),
				button: { text: self.data("select-text") },
				multiple: !1,
			}));
			file_frame.on("select", function () {
				attachment = file_frame
					.state()
					.get("selection")
					.first()
					.toJSON();
				self.attr("src", attachment.url);
				self.siblings(".easycommerce-field-image-value").val(attachment.id);
			});
			file_frame.open();
		}
	);

	$(".easycommerce-field-media").on("click", function (event) {
			event.preventDefault();
			var self = $(this);
			var file_frame = (wp.media.frames.file_frame = wp.media({
				title: self.data("title"),
				button: { text: self.data("select-text") },
				multiple: !1,
			}));
			file_frame.on("select", function () {
				attachment = file_frame
					.state()
					.get("selection")
					.first()
					.toJSON();
				self.siblings(".easycommerce-field-media-url").val(attachment.url);
				self.siblings(".easycommerce-field-media-value").val(attachment.id);
			});
			file_frame.open();
		}
	);


	$('.submenu-toggle').on('click', function(e) {
		e.preventDefault();

		var $parentLi 	= $(this).closest('li');
		var $submenu 	= $parentLi.children('.submenu-items');
		var $arrow 		= $(this).find('.arrow-icon');

		$submenu.slideToggle(200);
		$arrow.toggleClass('rotate-180');
	});

	$("#easycommerce-field-countries").select2();
	$("#easycommerce-field-event_types").select2();

	// Geo cascade: Country → State → City (General > Business > Address)
	var $geoCountry = $('#easycommerce-field-country');
	var $geoState   = $('#easycommerce-field-state[data-geo]');
	var $geoCity    = $('#easycommerce-field-city[data-geo]');

	if ($geoCountry.length && $geoState.length) {
		function ecPopulateGeoOptions($select, items, savedValue) {
			var placeholder = $select.data('placeholder') || 'Select ' + $select.data('geo');
			$select.empty().append('<option value="">' + placeholder + '</option>');
			$.each(items, function (i, val) {
				$select.append('<option value="' + val + '">' + val + '</option>');
			});
			if (savedValue) $select.val(savedValue);
		}

		function ecFetchCities(country, state, savedCity) {
			if (!country || !state) {
				$geoCity.empty().append('<option value="">Select City</option>');
				return;
			}
			$.ajax({
				url: EASYCOMMERCE.rest_base + '/geo/cities/' + country + (EASYCOMMERCE.rest_base.includes('?') ? '&' : '?') + 'state=' + encodeURIComponent(state),
				headers: { 'X-WP-Nonce': EASYCOMMERCE.nonce },
				success: function (resp) {
					if (resp.success && resp.data.cities) {
						ecPopulateGeoOptions($geoCity, resp.data.cities, savedCity);
					}
				},
			});
		}

		function ecFetchStates(country, savedState, savedCity) {
			if (!country) {
				$geoState.empty().append('<option value="">Select State</option>');
				$geoCity.empty().append('<option value="">Select City</option>');
				return;
			}
			$.ajax({
				url: EASYCOMMERCE.rest_base + '/geo/states/' + country,
				headers: { 'X-WP-Nonce': EASYCOMMERCE.nonce },
				success: function (resp) {
					if (resp.success && resp.data.states) {
						ecPopulateGeoOptions($geoState, resp.data.states, savedState);
						if (savedState) ecFetchCities(country, savedState, savedCity);
					}
				},
			});
		}

		$geoCountry.on('change', function () {
			ecFetchStates($(this).val(), '', '');
		});

		$geoState.on('change', function () {
			ecFetchCities($geoCountry.val(), $(this).val(), '');
		});

		// Restore saved state/city on page load
		var initCountry = $geoCountry.val();
		var initState   = $geoState.data('saved-value') || '';
		var initCity    = $geoCity.data('saved-value') || '';
		if (initCountry) {
			ecFetchStates(initCountry, initState, initCity);
		}
	}


    // Show/hide stripe payment gateway fields by change the "enable sandbox"
    const changePaymentGatewayFieldsVisibility = ( isEnabled = false ) =>{
        if( isEnabled ) {
            $("#easycommerce-field-wrapper-publishable_key").hide();
            $("#easycommerce-field-wrapper-secret_key").hide();
            $("#easycommerce-field-wrapper-webhook_secret").hide();
            $("#easycommerce-field-wrapper-sandbox_publishable_key").show();
            $("#easycommerce-field-wrapper-sandbox_secret_key").show();
            $("#easycommerce-field-wrapper-sandbox_webhook_secret").show();
        } else {
            $("#easycommerce-field-wrapper-publishable_key").show();
            $("#easycommerce-field-wrapper-secret_key").show();
            $("#easycommerce-field-wrapper-webhook_secret").show();
            $("#easycommerce-field-wrapper-sandbox_publishable_key").hide();
            $("#easycommerce-field-wrapper-sandbox_secret_key").hide();
            $("#easycommerce-field-wrapper-sandbox_webhook_secret").hide();
        }
    }

    var $enableSandboxMode = $("#easycommerce-field-is_sandbox_mode");
    changePaymentGatewayFieldsVisibility($enableSandboxMode.is(":checked"));
    $enableSandboxMode.on("change", function () {
        changePaymentGatewayFieldsVisibility($(this).is(":checked"));
    })

});
