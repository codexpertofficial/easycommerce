jQuery(document).ready(function ($) {
	// Add-to-cart is delegated on `document` so it works wherever shop product
	// cards render — including the product-collection block on a homepage — not
	// only on the Shop page. Registered BEFORE the shop-page guard below, which
	// early-returns when #shop-settings is absent.
	$(document).on("click", ".easycommerce-add-to-cart-shop", function () {
		var $button = $(this);
		var productId = $button.data("id");
		var $loader = $button.siblings(".loader");
		var originalText = $button.text();
		var $productContainer = $button.closest('.easycommerce-single-product').find(".easycommerce-single-product-checkout-btn-single");
	
		$button.text(__("Adding...", "easycommerce"));
		$loader.show();
	
		$.ajax({
			url: EASYCOMMERCE.rest_base + "/cart",
			method: "POST",
			contentType: "application/json",
			headers: {
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
			data: JSON.stringify({ products: [{ id: productId }] }),
			success: function (data) {
				if (data.success) {
					$button.text(__("Added", "easycommerce"));
                    // Redirect to the checkout page if Direct checkout is enabled.
                    if ( Boolean( EASYCOMMERCE.direct_checkout ) ) {
                        window.location.href = data.data.redirect;

                        return;
                    }
					$productContainer.show();
				} else {
					$button.text(originalText);
				}
			},
			error: function (xhr, status, error) {
				$button.text(originalText);
				var res = xhr.responseJSON;
				var message =
					(res && res.data && (res.data.message || (typeof res.data === "string" ? res.data : null))) ||
					(res && typeof res.data === "string" ? res.data : null) ||
					__("Failed to add to cart. Please try again.", "easycommerce");
				if (typeof easycommerce_error_toast === "function") {
					easycommerce_error_toast(message);
				}
			},
			complete: function () {
				$loader.hide();
			},
		});
	});

	const { __, _n, sprintf } = wp.i18n;

	var shopSettings = $("#shop-settings").val();
	if (!shopSettings) return;
	var settings = JSON.parse(shopSettings);
	var productPerPage = settings.ProductPerPage;
	function gatherInputValues() {
		const filters = {
			s: $(".easycommerce-product-search").val() || "",
			brands: $('input[name^="brand_"]:checked')
				.map(function () {
					return $(this).val();
				})
				.get(),
			categories: $('input[name^="category_"]:checked')
				.map(function () {
					return $(this).attr("name").replace("category_", "");
				})
				.get(),
			attributes: {},
			sort_by:
				$(".easycommerce-shop-page-input-type-radio:checked").val() ||
				"",
			min_price: $("#easycommerce-min-price").val() || 0,
			max_price: $("#easycommerce-max-price").val() || 0,
			return_html: 1,
			shop_name : $("#shop-name").val(),
			page: 1,
			per_page: productPerPage,
			settings: {},
		};

		$(".easycommerce-input-attribute:checked").each(function () {
			const attributeName = $(this).attr("name");
			const attributeValue = $(this).val();
			const attribute = $(this).data("attribute");

			if (attributeValue) {
				if (!filters.attributes[attribute]) {
					filters.attributes[attribute] = [];
				}
				if (Array.isArray(filters.attributes[attribute])) {
					filters.attributes[attribute].push(attributeName);
				} else {
					filters.attributes[attribute] = [filters.attributes[attribute], attributeName];
				}
			}
		});

		if (filters.brands.length === 0) delete filters.brands;
		if (filters.categories.length === 0) delete filters.categories;
		if (Object.keys(filters.attributes).length === 0)
			delete filters.attributes;

		const settingsValue = $("#shop-settings").val() || "{}";
		try {
			filters.settings = JSON.parse(settingsValue);
		} catch (error) {
		}

		return filters;
	}

	let currentView = "easycommerce-st-grid";
	$(document).ready(function() {
		$(".easycommerce-shop-st-grid-btn").addClass("active");
		$(".easycommerce-shop-st-list-btn").removeClass("active");
	});

	// Grid button
	$(".easycommerce-shop-st-grid-btn").click(function () {
		currentView = "easycommerce-st-grid";
		$(".easycommerce-st-list").addClass("hidden");
		$(".easycommerce-st-grid").removeClass("hidden");
		$(".easycommerce-shop-st-grid-btn").addClass("active");
		$(".easycommerce-shop-st-list-btn").removeClass("active");
		const filters = gatherInputValues();
		filters.view = currentView;
		callApiWithFilters(filters);
	});

	// List button
	$(".easycommerce-shop-st-list-btn").click(function () {
		currentView = "easycommerce-st-list";
		$(".easycommerce-st-grid").addClass("hidden");
		$(".easycommerce-st-list").removeClass("hidden");

		$(".easycommerce-shop-st-list-btn").addClass("active");
		$(".easycommerce-shop-st-grid-btn").removeClass("active");

		const filters = gatherInputValues();
		filters.view = currentView;
		callApiWithFilters(filters);
	});

	function callApiWithFilters(filters, page = 1) {
		const apiUrl = EASYCOMMERCE.rest_base + "/products";
		easycommerce_modal(true);

		const attributesParams = Object.keys(filters.attributes || {})
			.map(
				(key) => {
					const values = Array.isArray(filters.attributes[key]) 
						? filters.attributes[key] 
						: [filters.attributes[key]];
					return values.map(value => 
						`attributes[${encodeURIComponent(key)}][]=${encodeURIComponent(value)}`
					).join("&");
				}
			)
			.join("&");

		const settingsParams = {
			settings: filters.settings,
		};
		const settingsParamsString = new URLSearchParams(
			settingsParams.settings
		).toString();

		const params = new URLSearchParams({
			s: filters.s,
			brands: filters.brands ? filters.brands.join(",") : "",
			categories: filters.categories ? filters.categories.join(",") : "",
			sort_by: filters.sort_by,
			return_html: filters.return_html,
			shop_name: filters.shop_name,
			page: page,
			per_page: filters.per_page,
			min_price: filters.min_price,
			max_price: filters.max_price,
			is_shop: 1,
			view: filters.view || "easycommerce-st-grid",
		}).toString();

		const queryString = attributesParams
			? `${params}&${attributesParams}`
			: params;

		fetch(`${apiUrl}?${queryString}&${settingsParamsString}`, {
			method: "GET",
			headers: {
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
		})
			.then((response) => response.json())
			.then((data) => {
				// for template-3
				if (filters.shop_name === "template-3") {
					$(".easycommerce-st-grid").html("");
					$(".easycommerce-st-list").html("");

					if (filters.view === "easycommerce-st-list") {
						$(".easycommerce-st-list").html(
							data.data.products || data.data.message
						);
					} else {
						$(".easycommerce-st-grid").html(
							data.data.products || data.data.message
						);
					}
				} else {
					$(".easycommerce-shop-container").html(
						data.data.products || data.data.message
					);
				}

				$(".pagination-container").html(data.data.pagination || "");
				updateActivePaginationLink(page);

				const total = data.data.total || 0;
				const perPage = filters.per_page || 0;
				const start = total > 0 ? (page - 1) * perPage + 1 : 0;
				const end = Math.min(page * perPage, total);
				let countText = "";

				if (total > perPage) {
					countText = sprintf(
						// translators: 1: first result index, 2: last result index, 3: total results.
						_n(
							"Showing %1$s-%2$s of %3$s result",
							"Showing %1$s-%2$s of %3$s results",
							total,
							"easycommerce"
						),
						start,
						end,
						total
					);
				} else if (total > 0) {
					// translators: %s: total number of results.
					countText = sprintf(
						_n(
							"Showing %s result",
							"Showing %s results",
							total,
							"easycommerce"
						),
						total
					);
				} else {
					countText = __("No results found", "easycommerce");
				}

				$(".easycommerce-shop-count").text(countText);
			})
			.catch((error) => {
				console.error(error);
			})
			.finally(() => {
				easycommerce_modal(false);
			});
	}


	function updateActivePaginationLink(currentPage) {
		$(".easycommerce-pagination-link").removeClass(
			"easycommerce-pagination-active"
		);
		$(`.easycommerce-pagination-link[data-page="${currentPage}"]`).addClass(
			"easycommerce-pagination-active"
		);
	}

	updateActivePaginationLink(1);

	$(document).on(
		"change",
		".easycommerce-input-checkoutbox, #color-input, #search-input, .easycommerce-shop-page-input-type-radio, #easycommerce-min-price, #easycommerce-max-price",
		function () {
			const filters = gatherInputValues();
			filters.view = currentView;
			callApiWithFilters(filters);
		}
	);
	$(document).on("keydown", ".easycommerce-product-search", function (e) {
		if (e.key === "Enter") {
			e.preventDefault();
			const filters = gatherInputValues();
			callApiWithFilters(filters);
		}
	});

	function handleMouseUpAction() {
		const filters = gatherInputValues();
		filters.view = currentView;
		callApiWithFilters(filters);
	}

	$(document).on("click", ".easycommerce-pagination-link", function (e) {
		e.preventDefault();
		const page = $(this).data("page");
		const filters = gatherInputValues();
		filters.view = currentView; // Ensure current view is maintained during pagination
		callApiWithFilters(filters, page);
	});

	$(".easycommerce-filter").on("click", function () {
		$(".easycommerce-drawer-container").css({ transform: "translateX(0)" });
		$(".easycommerce-drawer-wrap").css("transform", "translateX(0)");
	});
	$(".easycommerce-drawer-close").on("click", function () {
		$(".easycommerce-drawer-wrap").css("transform", "translateX(-100%)");

		setTimeout(() => {
			$(".easycommerce-drawer-container").css(
				"transform",
				"translateX(-100%)"
			);
		}, 305);
	});

	//price range drag slider
	function startDrag($handle, isMin) {
		const $slider = $handle.parent();
		const $track = $slider.find(".bg-gray-200");
		const $minPriceInput = $("#easycommerce-min-price");
		const $maxPriceInput = $("#easycommerce-max-price");

		const onMouseMove = (e) => {
			const rect = $slider[0].getBoundingClientRect();
			const sliderWidth = rect.width;
			const offsetX = e.clientX - rect.left;
			const percent = Math.max(
				0,
				Math.min(100, (offsetX / sliderWidth) * 100)
			);
			const maxPrice = parseInt($maxPriceInput.attr("max"));
			const minPrice = parseInt($minPriceInput.attr("min"));
			let minValue = parseInt($minPriceInput.val());
			let maxValue = parseInt($maxPriceInput.val());

			if (isMin) {
				let newMin = Math.round((percent / 100) * maxPrice);
				newMin = Math.max(minPrice, Math.min(newMin, maxValue - 1));
				$minPriceInput.val(newMin);
				$handle.css("left", `${(newMin / maxPrice) * 95}%`);
			} else {
				let newMax = Math.round((percent / 100) * maxPrice);
				newMax = Math.min(maxPrice, Math.max(newMax, minValue + 1));
				$maxPriceInput.val(newMax);
				$handle.css("left", `${(newMax / maxPrice) * 95}%`);
			}

			minValue = parseInt($minPriceInput.val());
			maxValue = parseInt($maxPriceInput.val());
			const minPercent = (minValue / maxPrice) * 100;
			const maxPercent = (maxValue / maxPrice) * 100;

			$track.css({
				left: `${minPercent}%`,
				right: `${100 - maxPercent}%`,
			});
		};

		const onMouseUp = () => {
			$(window).off("mousemove", onMouseMove);
			$(window).off("mouseup", onMouseUp);
			handleMouseUpAction();
		};

		$(window).on("mousemove", onMouseMove);
		$(window).on("mouseup", onMouseUp);
	}

	$(".easycommerce-handle-min").on("mousedown", function (e) {
		e.preventDefault();
		startDrag($(this), true);
	});

	$(".easycommerce-handle-max").on("mousedown", function (e) {
		e.preventDefault();
		startDrag($(this), false);
	});
});
