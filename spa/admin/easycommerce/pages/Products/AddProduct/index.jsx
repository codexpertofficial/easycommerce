import React, { useState, useCallback, useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { toast } from 'react-toastify';

// Components
import Button from '../../../../common/components/inputs/Button';
import TextField from '../../../../common/components/inputs/TextField';

// Panel Components
import ProductSummary from '../components/ProductSummary';
import ProductAttr from '../components/ProductAttr';
import Prices from '../components/Prices';
import ProductDesc from '../components/ProductDesc';

import Gallery from '../components/Gallery';
import Organize from '../components/Organize';
import Template from '../components/ProductTemplate';
import ProductSettings from '../components/ProductSettings';
import ProductStatus from '../components/ProductStatus';

const AddProduct = () => {
	const [productTitle, setProductTitle] = useState('');
	const [productAttributes, setProductAttributes] = useState([]);
	const [productSlug, setProductSlug] = useState('');
	const [currentStatus, setCurrentStatus] = useState('draft');
	// Stores the fetched attribute list from server
	const [globalAttributes, setGlobalAttributes] = useState([]);

	/**
	 * Fetch attributes from the server on component mount
	 */
	const fetchGlobalAttributes = useCallback(async () => {
		try {
			// Fetch all attributes by setting per_page to a high number
			const response = await fetch(
				`${EASYCOMMERCE.rest_base}/attributes?per_page=-1`,
				{
					method: 'GET',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': EASYCOMMERCE.nonce,
					},
				},
			);

			const jsonData = await response.json();
			setGlobalAttributes(jsonData.data.attributes || []);
		} catch (error) {
			console.error('Error fetching attributes:', error);
		} finally {
			// Close spinner after fetch finishes
			easycommerce_modal(false);
		}
	}, []);

	// Run fetchAttrs only once on component mount
	useEffect(() => {
		fetchGlobalAttributes();
	}, []);

	const saveProduct = useCallback((status) => {
		// easycommerce_modal(true);
		const formData = new FormData(
			document.getElementById('easycommerce-add-product')
		);
		const rawData = Object.fromEntries(formData.entries());

		if (rawData.product_title === '') {
			toast.error(__('Product title is required', 'easycommerce'));
			easycommerce_modal(false);
			return;
		}

		const productData = {
			title: rawData.product_title,
			status: status,
			summary: rawData.product_summary || '',
			attributes: JSON.parse(rawData.product_attributes || '[]'),
			description: rawData.product_description || '',
			sku: rawData.product_sku || '',
			thumbnail: rawData.product_thumbnail || null,
			slug: rawData.product_slug || '',
			meta: {
				gallery: JSON.parse(rawData.product_gallery || '[]'),
				hide_from_shop: rawData.hide_from_shop === 'on',
				noindex: rawData.hide_from_search_engines === 'on',
				show_review: rawData.set_review === 'on',
				review_text_mandatory: rawData.set_review_mandatory === 'on',
				tax_class: rawData.product_tax_class || '',
				template: rawData.product_template || '',
				_badge_new: rawData._badge_new === '1',
				_badge_best_seller: rawData._badge_best_seller === '1',
				_badge_featured: rawData._badge_featured === '1',
			},
			categories: rawData.product_categories ? rawData.product_categories.split(',').map(id => parseInt(id.trim()) || id.trim()).filter(id => id) : [],
			brands: rawData.product_brands ? rawData.product_brands.split(',').map(id => parseInt(id.trim()) || id.trim()).filter(id => id) : [],
			tags: rawData.product_tags ? rawData.product_tags.split(',').map(id => parseInt(id.trim()) || id.trim()).filter(id => id) : [],
			variations: [],
		};

		// Validate and collect variation data
		const priceData = document.querySelectorAll('.easycommerce-price-item');
		const variations = [];

		for (const item of priceData) {
			let variationData =
				item.querySelector('input[name=ec-variation-data]').value || '{}';

			try {
				variationData = JSON.parse(variationData);
			} catch (e) {
				toast.error(__('Invalid Pricing Data', 'easycommerce'));
				easycommerce_modal(false);
				return;
			}

			// if (!variationData.name?.trim()) {
			//     toast.error('Variation title is required');
			//     easycommerce_modal(false);
			//     return;
			// }

			// if (variationData.regular_price == null || variationData.regular_price === '') {
			//     toast.error('Regular price is required for each variation');
			//     easycommerce_modal(false);
			//     return;
			// }

			const slotFields = item.querySelectorAll('.ec-slot-field input');
			slotFields.forEach((field) => {
				const fieldName = field.getAttribute('name');
				const fieldValue = field.value;
				variationData.meta = {
					...(variationData.meta || {}),
					[fieldName]: fieldValue,
				};
			});

			variations.push(variationData);

			const isDuplicateName = variations.some((existing) => {
				return (
					existing.id !== variationData.id &&
					existing.name === variationData.name
				);
			});

			if (isDuplicateName) {
				toast.error(
					// translators: %s: pricing plan name.
					sprintf(__('Duplicate pricing plan found: %s', 'easycommerce'), variationData.name)
				);
				easycommerce_modal(false);
				return;
			}

			// variationData.attributes.length > 0 && variations.some((existing) => {
			//     if (JSON.stringify(existing.attributes || {}) === JSON.stringify(variationData.attributes || {})) {
			//         toast.error(`Duplicate attributes found in ${variationData.name} and ${existing.name}`);
			//         easycommerce_modal(false);
			//         return;
			//     }
			// });
			const attributesEqual = (a = [], b = []) => {
				if (a.length !== b.length) return false;

				const mapA = new Map();
				const mapB = new Map();

				for (const attr of a) {
					const key = String(attr.attribute_id ?? attr.attribute_slug ?? '');
					if (!key) continue;
					mapA.set(key, String(attr.value_id ?? attr.value_slug ?? ''));
				}

				for (const attr of b) {
					const key = String(attr.attribute_id ?? attr.attribute_slug ?? '');
					if (!key) continue;
					mapB.set(key, String(attr.value_id ?? attr.value_slug ?? ''));
				}

				if (mapA.size !== mapB.size) return false;

				for (const [key, valA] of mapA) {
					const valB = mapB.get(key);
					if (valB !== valA) return false;
				}

				return true;
			};

			let hasMatching = variations.some((existing) => {
				if (existing.id === variationData.id) return false;

				if (attributesEqual(existing.attributes, variationData.attributes)) {
					toast.error(
						// translators: 1: new variation name, 2: existing variation name.
						sprintf(__('Duplicate attributes found in %1$s and %2$s', 'easycommerce'), variationData.name, existing.name)
					);
					easycommerce_modal(false);
					return true; // stops .some and sets hasMatching to true
				}

				return false;
			});

			if (hasMatching) {
				return;
			}
		}

		productData.variations = variations;

		fetch(`${EASYCOMMERCE.rest_base}/products`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
			body: JSON.stringify(productData),
		})
			.then((res) => res.json())
			.then((data) => {
				easycommerce_modal(false);
				if (data.success && data.data?.product?.id) {
					status == 'publish' && toast.success(__('Product is live now!', 'easycommerce'));
					status == 'draft' && toast.success(__('Product saved as draft!', 'easycommerce'));
					if (data.data.product.slug) {
						setProductSlug(data.data.product.slug);
						setTimeout(() => {
							window.location.hash = `#/products/edit/${data.data.product.id}`;
						}, 100);
					} else {
						window.location.hash = `#/products/edit/${data.data.product.id}`;
					}
				} else {
					if (data.data && data.data.message) {
						toast.error(data.data.message);
					} else if (data.message) {
						toast.error(data.message);
					} else {
						toast.error(data.message);
					}
				}
			})
			.catch((error) => {
				easycommerce_modal(false);
				toast.error(__('Product Creation Failed', 'easycommerce'));
				console.error('Error creating product:', error);
			});
	}, []);

	return (
		<>
			<form id="easycommerce-add-product">
				<div className="product-panel-title flex items-center justify-between mb-4">
					<h3>{__('Add New Product', 'easycommerce')}</h3>

					<div class="flex items-center justify-between gap-4 w-max">
						<ProductStatus onStatusChange={setCurrentStatus} />

						<button
							className="easycommerce-primary-button w-[124px] h-[41px] flex items-center justify-center gap-2.5"
							onClick={(e) => {
								e.preventDefault();
								saveProduct(currentStatus);
							}}
						>
							{currentStatus === 'draft' ? __('Save as Draft', 'easycommerce') : __('Save as Live', 'easycommerce')}
						</button>
					</div>
				</div>
				<div className="grow-[1] border border-solid border-ec-table-stock rounded-xl min-h-screen">
					<div className="w-full">
						<div class="p-6 bg-white rounded-xl mb-6">
							<TextField
								name={`product_title`}
								placeholder={__('Add your product title here', 'easycommerce')}
								value={productTitle}
								onChange={(e) => setProductTitle(e.target.value)}
								className="h-ec-input"
							/>
						</div>
						<div className="flex gap-4">
							<div className="flex-grow max-w-[calc(100%-426px)] ec-db-lg:max-w-[calc(100%-510px)] flex flex-col gap-4">
								<ProductSummary productTitle={productTitle} />
								<ProductAttr
									globalAttributes={globalAttributes}
									fetchGlobalAttributes={fetchGlobalAttributes}
									productAttributes={productAttributes}
									setProductAttributes={setProductAttributes}
									productTitle={productTitle}
								/>
								<Prices
									productTitle={productTitle}
									globalAttributes={globalAttributes}
									productAttributes={productAttributes}
								/>
								<ProductDesc productTitle={productTitle} />
							</div>
							<div className="w-[410px] ec-db-lg:w-[510px] flex flex-col gap-4">
								<Gallery productTitle={productTitle} />
								<Organize />
								<Template />
								<ProductSettings productTitle={productTitle} productSlug={productSlug} />		
							</div>
						</div>
					</div>
				</div>
			</form>
		</>
	);
};

export default AddProduct;
