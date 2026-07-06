import React, { useState, useCallback, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { toast } from 'react-toastify';

// Components
import Button from '../../../../common/components/inputs/Button';
import TextField from '../../../../common/components/inputs/TextField';

// Panel Components
import ProductActions from './components/ProductActions';
import ProductSummary from '../components/ProductSummary';
import ProductAttr from '../components/ProductAttr';
import Prices from '../components/Prices';
import ProductDesc from '../components/ProductDesc';

import Gallery from '../components/Gallery';
import Organize from '../components/Organize';
import Template from '../components/ProductTemplate';
import ProductSettings from '../components/ProductSettings';
import ProductStatus from '../components/ProductStatus';

const EditProduct = ({ id, setBreadcrumbTitle }) => {
	const [productData, setProductData] = useState(null);
	const [globalAttributes, setGlobalAttributes] = useState([]);
	const [currentStatus, setCurrentStatus] = useState('publish');
	const [productTitle, setProductTitle] = useState('');
	const [productAttributes, setProductAttributes] = useState([]);

	const fetchProductData = useCallback(async () => {
		try {
			easycommerce_modal(true);
			const response = await fetch(`${EASYCOMMERCE.rest_base}/products/${id}`, {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': EASYCOMMERCE.nonce,
				},
			});

			const data = await response.json();
			easycommerce_modal(false);

			if (data.success) {
				setProductData(data.data);
				setCurrentStatus(data.data.status || 'publish');
				setProductAttributes(data.data.attributes || []);
				if (data.data?.title) {
					setBreadcrumbTitle(data.data.title);
				}
			} else {
				toast.error("Product doesn't exist");
				window.location.hash = `#/products`;
			}
		} catch (error) {
			easycommerce_modal(false);
			toast.error('Failed to fetch product data');
			console.error('Error fetching product data:', error);
		}
	}, [id]);

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

	useEffect(() => {
		easycommerce_modal(true);

		fetchProductData();
		fetchGlobalAttributes();
	}, [id]);

	useEffect(() => {
		if (productData && productData.title) {
			setProductTitle(productData.title);
		}
	}, [productData]);

	const saveProduct = useCallback(() => {
		easycommerce_modal(true);
		const formData = new FormData(
			document.getElementById('easycommerce-add-product'),
		);
		const rawData = Object.fromEntries(formData.entries());

		const updatedProduct = {
			...productData,
			meta: {
				gallery: productData.meta?.gallery || [],
				hide_from_shop: productData.meta?.hide_from_shop || false,
				noindex: productData.meta?.noindex || false,
				show_review: productData.meta?.show_review || false,
				review_text_mandatory: productData.meta?.review_text_mandatory || false,
				tax_class: productData.meta?.tax_class || '',
			},
		};

		updatedProduct.title = rawData.product_title || '';
		updatedProduct.status = rawData.product_status || 'published';
		updatedProduct.summary = rawData.product_summary || '';
		updatedProduct.attributes = JSON.parse(rawData.product_attributes || '[]');
		updatedProduct.description = rawData.product_description || '';
		updatedProduct.sku = rawData.product_sku || '';

		const priceData = document.querySelectorAll('.easycommerce-price-item');
		const variations = [];

		for (const item of priceData) {
			let variationData =
				item.querySelector('input[name=ec-variation-data]').value || '{}';

			try {
				variationData = JSON.parse(variationData);
			} catch (e) {
				toast.error('Invalid Pricing Data');
				easycommerce_modal(false);
				return;
			}

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

			if (isDuplicateName === true) {
				toast.error(`Duplicate pricing plan found: ${variationData.name}`);
				easycommerce_modal(false);
				return;
			}

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
						`Duplicate attributes found in ${variationData.name} and ${existing.name}`,
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

		const convertOrganizeField = (fieldValue) => {
			return fieldValue
				? fieldValue
						.split(',')
						.map((id) => parseInt(id.trim()) || id.trim())
						.filter((id) => id)
				: [];
		};

		updatedProduct.variations = variations;

		updatedProduct.thumbnail = rawData.product_thumbnail;
		updatedProduct.meta.gallery = JSON.parse(rawData.product_gallery || '[]');

		updatedProduct.categories = convertOrganizeField(
			rawData.product_categories,
		);
		updatedProduct.brands = convertOrganizeField(rawData.product_brands);
		updatedProduct.tags = convertOrganizeField(rawData.product_tags);
		updatedProduct.slug = rawData.product_slug || '';
		updatedProduct.meta.hide_from_shop = rawData.hide_from_shop === 'on';
		updatedProduct.meta.noindex = rawData.hide_from_search_engines === 'on';
		updatedProduct.meta.template = rawData.product_template || '';
		updatedProduct.meta.show_review = rawData.set_review === 'on';
		updatedProduct.meta.review_text_mandatory =
			rawData.set_review_mandatory === 'on';

		updatedProduct.meta.tax_class = rawData.product_tax_class || '';

		fetch(`${EASYCOMMERCE.rest_base}/products/${id}`, {
			method: 'PUT',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
			body: JSON.stringify(updatedProduct),
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success && data.data?.product?.id) {
					easycommerce_modal(false);
					toast.success(data.data.message || 'Product updated successfully');
					setProductData((prev) => ({
						...prev,
						status: updatedProduct.status,
						slug: data.data.product.slug || updatedProduct.slug,
					}));
				} else {
					easycommerce_modal(false);
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
				toast.error('Product update failed');
				console.error('Error updating product:', error);
			});
	}, [productData]);

	if (!productData || productData.length === 0) {
		return (
			<div className="flex items-center justify-center py-10">
				<span className="text-ec-body font-inter text-[16px] leading-[20px]">
					{__('Loading product data...', 'easycommerce')}
				</span>
			</div>
		);
	}

	return (
		<>
			<form id="easycommerce-add-product">
				<div className="product-panel-title flex font-inter items-center justify-between mb-4">
					<div className="flex items-center gap-3.5">
						<h3>
							{__('Edit: ', 'easycommerce')} {productData.title}
						</h3>

						{productData.status === 'publish' ? (
							<div className="flex items-center gap-1.5 px-2 py-1 rounded-full border border-[#00A900] bg-white">
								<div className="w-2 h-2 rounded-full bg-[#00A900]"></div>
								<span className="text-[#00A900] text-xs">
									{__('Live', 'easycommerce')}
								</span>
							</div>
						) : (
							<div className="flex items-center gap-1.5 px-2 py-1 rounded-full border border-[#7F7F98] bg-white">
								<div className="w-2 h-2 rounded-full bg-[#7F7F98]"></div>
								<span className="text-[#7F7F98] text-xs">
									{__('Draft', 'easycommerce')}
								</span>
							</div>
						)}
					</div>

					<div className="flex items-center gap-4 justify-between w-max">
						<button
							className="flex items-center"
							onClick={(e) => {
								e.preventDefault();

								try {
									saveProduct(productData.status);
									window.open(productData.url, '_blank');
								} catch (err) {
									console.error('Failed to save product:', err);
								}
							}}
						>
							<span className="text-ec-body text-base border-b border-ec-body hover:text-ec-primary hover:border-ec-primary duration-300">
								{__('Preview', 'easycommerce')}
							</span>
						</button>

						<ProductActions data={productData} />

						<ProductStatus
							prevStatus={productData.status}
							onStatusChange={setCurrentStatus}
						/>

						<button
							className="easycommerce-primary-button w-[124px] h-[41px] flex items-center justify-center gap-2.5"
							onClick={(e) => {
								e.preventDefault();
								saveProduct();
							}}
						>
							{currentStatus === 'draft'
								? __('Save as Draft', 'easycommerce')
								: __('Save as Live', 'easycommerce')}
						</button>
					</div>
				</div>
				<div className="grow-[1] border border-solid border-ec-table-stock rounded-xl min-h-screen">
					<div className="w-full">
						<div className="p-6 bg-white rounded-xl mb-6">
							<TextField
								name={`product_title`}
								placeholder={`Add your product title here`}
								value={productTitle}
								onChange={(e) => setProductTitle(e.target.value)}
								className="h-ec-input"
							/>
						</div>
						<div className="flex gap-4">
							<div className="flex-grow max-w-[calc(100%-426px)] ec-db-lg:max-w-[calc(100%-510px)] flex flex-col gap-4">
								<ProductSummary
									prevData={productData.summary}
									productTitle={productTitle}
								/>

								<ProductAttr
									productAttributes={productAttributes}
									setProductAttributes={setProductAttributes}
									globalAttributes={globalAttributes}
									fetchGlobalAttributes={fetchGlobalAttributes}
									productTitle={productTitle}
								/>

								<Prices
									productTitle={productTitle}
									globalAttributes={globalAttributes}
									productAttributes={productAttributes}
									prevData={productData.variations}
								/>

								<ProductDesc
									productTitle={productTitle}
									prevData={productData.description}
								/>
							</div>
							<div className="w-[410px] ec-db-lg:w-[510px] flex flex-col gap-4">
								<Gallery
									productTitle={productTitle}
									prevFiles={productData.meta.gallery}
									prevThumbnail={productData.thumbnail}
								/>
								<Organize
									prevCats={productData.categories}
									prevTags={productData.tags}
									prevBrands={productData.brands}
								/>
								<Template prevData={productData.meta.template} />
								<ProductSettings
									productSlug={productData.slug}
									prevData={productData.meta}
								/>
							</div>
						</div>
					</div>
				</div>
			</form>
		</>
	);
};

export default EditProduct;
