import React, { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import { toast } from 'react-toastify';
import { __ } from '@wordpress/i18n';

import CategoryModal from './Modals/CategoryModal';
import TagModal from './Modals/TagModal';
import BrandModal from './Modals/BrandModal';
import PanelTitle from '../common/PanelTitle';
import Categories from './Categories';
import Tags from './Tags';
import Brands from './Brands';
import './style.css';

const Organize = ({ prevCats, prevTags, prevBrands }) => {
	const [activeTab, setActiveTab] = useState('categories');
	const [isOpen, setIsOpen] = useState(true);
	const [activeModal, setActiveModal] = useState('none');
	const [categories, setCategories] = useState([]);
	const [tags, setTags] = useState([]);
	const [brands, setBrands] = useState([]);
	const [selectedCategories, setSelectedCategories] = useState([]);
	const [selectedTags, setSelectedTags] = useState([]);
	const [selectedBrands, setSelectedBrands] = useState([]);

	const fetchCategories = async () => {
		try {
			const response = await fetch(
				`${EASYCOMMERCE.rest_base}/products/categories?per_page=99999999`,
				{
					method: 'GET',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': EASYCOMMERCE.nonce,
					},
				}
			);
			const data = await response.json();
			setCategories(data.data.categories);
		} catch (error) {
			toast.error('Failed to fetch categories.');
		}
	};

	const fetchTags = async () => {
		try {
			const response = await fetch(
				`${EASYCOMMERCE.rest_base}/products/tags?per_page=99999999`,
				{
					method: 'GET',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': EASYCOMMERCE.nonce,
					},
				}
			);
			const data = await response.json();
			setTags(data.data.tags);
		} catch (error) {
			toast.error('Failed to fetch tags.');
		}
	};

	const fetchBrands = async () => {
		try {
			const response = await fetch(
				`${EASYCOMMERCE.rest_base}/products/brands?per_page=99999999`,
				{
					method: 'GET',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': EASYCOMMERCE.nonce,
					},
				}
			);
			const data = await response.json();
			setBrands(data.data.brands);
		} catch (error) {
			toast.error('Failed to fetch brands.');
		}
	};

	useEffect(() => {
		void fetchCategories();
		void fetchTags();
		void fetchBrands();
	}, []);

	useEffect(() => {
		if (Array.isArray(prevCats)) {
			const ids = prevCats.filter((cat) => cat && cat.id).map((cat) => cat.id);
			setSelectedCategories(ids);
		}
	}, [prevCats]);

	useEffect(() => {
		if (Array.isArray(prevTags)) {
			const ids = prevTags.filter((tag) => tag && tag.id).map((tag) => tag.id);
			setSelectedTags(ids);
		}
	}, [prevTags]);

	useEffect(() => {
		if (Array.isArray(prevBrands)) {
			const ids = prevBrands
				.filter((brand) => brand && brand.id)
				.map((brand) => brand.id);
			setSelectedBrands(ids);
		}
	}, [prevBrands]);

	return (
		<div className="bg-white rounded-xl border-ec-table-stock border border-solid overflow-hidden">
			<div className="py-[14px] px-6 flex items-center justify-between border-b border-ec-table-stock border-solid">
				<PanelTitle
					title={__('Organize', 'easycommerce')}
					notice={__(
						'Organize your product with Categories, Tags, and Brands for better sorting.',
						'easycommerce'
					)}
				/>

				<div className="panel-actions">
					<button
						className="panel-collapse"
						type="button"
						onClick={() => setIsOpen(!isOpen)}
					>
						<svg
							className={`transition-transform duration-300 ${
								isOpen ? '' : 'rotate-180'
							}`}
							xmlns="http://www.w3.org/2000/svg"
							width="11"
							height="6"
							viewBox="0 0 11 6"
							fill="none"
						>
							<path
								d="M1.12891 4.28906L5.28516 0.378906C5.43099 0.251302 5.58594 0.1875 5.75 0.1875C5.91406 0.1875 6.0599 0.251302 6.1875 0.378906L10.3438 4.28906C10.6172 4.59896 10.6263 4.90885 10.3711 5.21875C10.0794 5.49219 9.76953 5.5013 9.44141 5.24609L5.75 1.74609L2.03125 5.24609C1.72135 5.5013 1.42057 5.5013 1.12891 5.24609C0.873698 4.91797 0.873698 4.59896 1.12891 4.28906Z"
								fill="#3C3C42"
							/>
						</svg>
					</button>
				</div>
			</div>

			<motion.div
				initial={false}
				animate={{
					height: isOpen ? 'auto' : 0,
					opacity: isOpen ? 1 : 0,
					overflow: 'hidden',
					transition: { duration: 0.3, ease: 'easeInOut' },
				}}
				style={{
					visibility: isOpen ? 'visible' : 'hidden',
				}}
			>
				<div className="p-6">
					<div className="terms-filters-container flex gap-4 lg:gap-2 border-b border-ec-primary">
						<button
							type="button"
							className={activeTab === 'categories' ? 'active' : ''}
							onClick={() => setActiveTab('categories')}
						>
							{__('Categories', 'easycommerce')}
						</button>
						<button
							type="button"
							className={activeTab === 'tags' ? 'active' : ''}
							onClick={() => setActiveTab('tags')}
						>
							{__('Tags', 'easycommerce')}
						</button>
						<button
							type="button"
							className={activeTab === 'brands' ? 'active' : ''}
							onClick={() => setActiveTab('brands')}
						>
							{__('Brands', 'easycommerce')}
						</button>
					</div>

					<Categories
						categories={categories}
						selected={selectedCategories}
						setSelected={setSelectedCategories}
						active={activeTab === 'categories'}
					/>
					<Tags
						tags={tags}
						selected={selectedTags}
						setSelected={setSelectedTags}
						active={activeTab === 'tags'}
					/>
					<Brands
						brands={brands}
						selected={selectedBrands}
						setSelected={setSelectedBrands}
						active={activeTab === 'brands'}
					/>

					{activeTab === 'categories' && (
						<>
							<a
								href="#"
								className="easycommerce-underline-button mt-6 block text-sm"
								onClick={(e) => {
									e.preventDefault();
									setActiveModal('categories');
								}}
							>
								{__('Manage Categories', 'easycommerce')}
							</a>
							<CategoryModal
								isOpen={activeTab === activeModal}
								onClose={() => setActiveModal('none')}
								onCategoryCreated={(newCategory) => {
									setCategories((prev) => [...prev, newCategory]);
									setSelectedCategories((prev) => [...prev, newCategory.id]);
								}}
							/>
						</>
					)}
					{activeTab === 'tags' && (
						<>
							<a
								href="#"
								className="easycommerce-underline-button mt-6 block"
								onClick={(e) => {
									e.preventDefault();
									setActiveModal('tags');
								}}
							>
								{__('Manage Tags', 'easycommerce')}
							</a>
							<TagModal
								isOpen={activeTab === activeModal}
								onClose={() => setActiveModal('none')}
								onTagCreated={(newTag) => {
									setTags((prev) => [...prev, newTag]);
									setSelectedTags((prev) => [...prev, newTag.id]);
								}}
							/>
						</>
					)}
					{activeTab === 'brands' && (
						<>
							<a
								href="#"
								className="easycommerce-underline-button mt-6 block"
								onClick={(e) => {
									e.preventDefault();
									setActiveModal('brands');
								}}
							>
								{__('Manage Brands', 'easycommerce')}
							</a>
							<BrandModal
								isOpen={activeTab === activeModal}
								onClose={() => setActiveModal('none')}
								onBrandCreated={(newBrand) => {
									setBrands((prev) => [...prev, newBrand]);
									setSelectedBrands((prev) => [...prev, newBrand.id]);
								}}
							/>
						</>
					)}
				</div>
			</motion.div>
		</div>
	);
};

export default Organize;
