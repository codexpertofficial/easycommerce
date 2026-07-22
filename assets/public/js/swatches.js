jQuery(function ($) {
    const { __ } = wp.i18n;

    $(document).ready(function () {
        // Loop through all attribute wrappers
        $('.easycommerce-attributes-wrapper').each(function () {
            var variationsData = $(this).data('variations');
            if (!variationsData) return;

            // Check if any variation is digital
            var hasDigital = variationsData.some(function (variation) {
                return variation.type === 'digital';
            });

            if (hasDigital) {
                $('.easycommerce-add-to-cart').hide();
            } else {
                $('.easycommerce-add-to-cart').show();
            }
        });

        if ($("#easycommerce_variation_id").val() === "1") {
            $(".easycommerce-add-to-cart-button").prop("disabled", true);
        }
        
        $(document).on("change", '.easycommerce-attributes-wrapper input[type="checkbox"]', function () {
            var form            = $(this).closest(".easycommerce-attributes-wrapper");
            var variationObj    = form.data("variations");
            var variations      = variationObj;
            var attributeKeys   = [];

            if (variations.length > 0 && variations[0].attributes && Array.isArray(variations[0].attributes)) {
                var uniqueAttributes = {};
                variations[0].attributes.forEach(function(attr) {
                    if (attr.attribute_slug && !uniqueAttributes[attr.attribute_slug]) {
                        uniqueAttributes[attr.attribute_slug] = true;
                        attributeKeys.push(attr.attribute_slug);
                    }
                });
            }
            
            var matchValArray       = [];
            var matchAttrArray      = [];
            var finalAllMatch       = [];
            var alreadyMatchedAttr  = [];
            let sliceIndex          = 0;
            
            $('.easycommerce-qunatity-input').val(1);
            $(this).closest(".easycommerce-vs-wrapper").find('input[type="checkbox"]').not(this).prop("checked", false);

            attributeKeys.forEach((attribute) => {
                var checkedBoxes = $(`input[name="attribute_${attribute}"]:checked`);
                checkedBoxes.each(function () {
                    var attrValueId = parseInt($(this).val());
                    var attrName = attribute;
                    if (attrValueId) {
                        matchValArray.push(attrValueId);
                    }
                    if (attrName) {
                        matchAttrArray.push(attrName);
                    }
                });
            });

            function attributesToObject(attributesArray) {
                var obj = {};
                if (Array.isArray(attributesArray)) {
                    attributesArray.forEach(function(attr) {
                        if (attr.attribute_slug && attr.value_id) {
                            obj[attr.attribute_slug] = parseInt(attr.value_id);
                        }
                    });
                }
                return obj;
            }

            Object.entries(attributeKeys).forEach(() => {
                const compAttr = matchAttrArray[sliceIndex];
                const compVal = matchValArray.slice(0, sliceIndex).concat(matchValArray.slice(sliceIndex + 1));

                if (compAttr != undefined) {
                    variations.forEach((variation) => {
                        var attributes = attributesToObject(variation.attributes);
                        if (compVal.every((val) => Object.values(attributes).includes(val))) {
                            if (!finalAllMatch[compAttr]) {
                                finalAllMatch[compAttr] = [];
                                alreadyMatchedAttr.push(compAttr);
                            }
                            if (!finalAllMatch[compAttr].includes(attributes[compAttr])) {
                                finalAllMatch[compAttr].push(attributes[compAttr]);
                            }
                        }
                    });
                }
                sliceIndex++;
            });

            var missingAttr = attributeKeys.filter(
                (key) => !alreadyMatchedAttr.includes(key)
            );
            if (missingAttr.length) {
                missingAttr.forEach((missingKey) => {
                    variations.forEach((variation) => {
                        var attributes = attributesToObject(variation.attributes);
                        if (matchValArray.every((val) => Object.values(attributes).includes(val))) {
                            if (!finalAllMatch[missingKey]) {
                                finalAllMatch[missingKey] = [];
                            }
                            if (!finalAllMatch[missingKey].includes(attributes[missingKey])) {
                                finalAllMatch[missingKey].push(attributes[missingKey]);
                            }
                        }
                    });
                });
            }

            // Disable options based on matching attributes
            attributeKeys.forEach((attrName) => {
                var options = form.find(`input[name="attribute_${attrName}"]`);
                options.each(function () {
                    var termValueId = parseInt($(this).val());
                    var shouldDisable = finalAllMatch[attrName] && !finalAllMatch[attrName].includes(termValueId);

                    $(this).prop("disabled", shouldDisable);

                    if (matchValArray.includes(termValueId)) {
                        $(this).prop("checked", true);
                    }
                });
            });

            // Check if all attributes are selected
            if (matchValArray.length === attributeKeys.length) {
                variations.forEach((variation) => {
                    var attributes = attributesToObject(variation.attributes);
                    if (matchValArray.every((val) => Object.values(attributes).includes(val))) {
                        var variationId = variation.id;
                        var productId = variation.price_id;
                        var price = variation.price;
                        var sale_price = variation.sale_price;
                        var stock_count = variation.stock_count;
                        var type = variation.type;
                        
                        var matchingSlide = $(".swiper-slide").filter(function () {
                            var dataId = $(this).attr("data-id");
                            try {
                                var idsArray = JSON.parse(dataId.replace(/&quot;/g, '"'));
                                return idsArray.includes(String(variationId));
                            } catch (e) {
                                return false;
                            }
                        });

                        $("#easycommerce_variation_id").val(variationId);
                        $("#easycommerce_variation_price_id").val(productId);
                        $(".easycommerce-add-to-cart-button").prop("disabled", false);
                        $(".easycommerce-product-price").text(price);
                        $(".easycommerce-product-sale-price").text(sale_price);
                        $(".easycommerce-stock-count").text(stock_count);
                        $(".easycommerce-qunatity-input").attr("data-stock-count", stock_count);
                        $(".easycommerce-qunatity-input").attr("data-type", type);
                        
                        if (matchingSlide.length) {
                            $(".swiper-slide").removeClass("swiper-slide-active");
                            matchingSlide.first().addClass("swiper-slide-active");

                            const swiperInstance = $(".swiper").get(0)?.swiper;
                            if (swiperInstance) {
                                swiperInstance.slideTo(matchingSlide.first().index());
                            }
                        }
                        
                        if (stock_count == null) {
                            $(".easycommerce-stock-count-wrapper").hide();
                        } else {
                            $(".easycommerce-stock-count-wrapper").show();
                        }

                        if (stock_count == 0) {
                            $(".easycommerce-add-to-cart-button").prop("disabled", true);
                            $(".easycommerce-stock-count").text(__("Out of stock", 'easycommerce')).removeClass("text-emerald-500").addClass("text-red-500");
                            $(".easycommerce-stock-dot").removeClass("bg-emerald-500").addClass("bg-red-500");
                            $(".easycommerce-stock-label").hide();
                        } else {
                            $(".easycommerce-add-to-cart-button").prop("disabled", false);
                            $(".easycommerce-stock-count").removeClass("text-red-500").addClass("text-emerald-500");
                            $(".easycommerce-stock-dot").removeClass("bg-red-500").addClass("bg-emerald-500");
                            $(".easycommerce-stock-label").show();
                        }
                    }
                });
            } else {
                $('.easycommerce-qunatity-input').val(1);
                $("#easycommerce_variation_id").val(1);
                $("#easycommerce_variation_price_id").val(1);
                $(".easycommerce-add-to-cart-button").prop("disabled", true);

                var partialStock = 0;
                variations.forEach(function(variation) {
                    var attributes = attributesToObject(variation.attributes);
                    var isMatch = matchValArray.every(function(val) {
                        return Object.values(attributes).includes(val);
                    });
                    if (isMatch) {
                        partialStock += parseInt(variation.stock_count) || 0;
                    }
                });

                $(".easycommerce-stock-count-wrapper").show();
                $(".easycommerce-qunatity-input").attr("data-stock-count", partialStock);

                if (partialStock == 0) {
                    $(".easycommerce-stock-count").text(__("Out of stock", 'easycommerce')).removeClass("text-emerald-500").addClass("text-red-500");
                    $(".easycommerce-stock-dot").removeClass("bg-emerald-500").addClass("bg-red-500");
                    $(".easycommerce-stock-label").hide();
                } else {
                    $(".easycommerce-stock-count").text(partialStock).removeClass("text-red-500").addClass("text-emerald-500");
                    $(".easycommerce-stock-dot").removeClass("bg-red-500").addClass("bg-emerald-500");
                    $(".easycommerce-stock-label").show();
                }
            }
        });
    });
});