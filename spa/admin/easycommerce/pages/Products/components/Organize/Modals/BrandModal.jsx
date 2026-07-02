import React, { useEffect, useState } from "react";
import { toast } from "react-toastify";
import Title from "../../../../../../common/components/Title";
import SubTitle from "../../../../../../common/components/SubTitle";
import TextField from "../../../../../../common/components/inputs/TextField";
import Dropdown from "../../../../../../common/components/inputs/Dropdown";
import Button from "../../../../../../common/components/inputs/Button";

const BrandModal = ({isOpen, onClose, onBrandCreated}) => {
    const [flatBrands, setFlatBrands] = useState([]);
    const [brandsData, setBrandsData] = useState({ brands: [{ brand_name: '', brand_slug: '', parent: 0 }] });
    const [selectParent, setSelectParent] = useState({ label: 'None', value: 0 });

    const generateSlug = (text) => {
        return text
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9\-]/g, '');
    };

    const flattenBrands = (brands, parentName = 'None', parentId = 0) => {
        let flatList = [];

        brands.forEach((brand) => {
            flatList.push({
                id: brand.id,
                name: brand.name,
                slug: brand.slug,
                parentName,
                parent: parentId,
            });

            if (brand.children && brand.children.length > 0) {
                flatList = flatList.concat(flattenBrands(brand.children, brand.name, brand.id));
            }
        });

        return flatList;
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        if (name === 'brand_name') {
            const newSlug = generateSlug(value);
            setBrandsData((prev) => ({
                ...prev,
                brands: [{ ...prev.brands[0], brand_name: value, brand_slug: newSlug, parent: prev.brands[0].parent }]
            }));
        }
    };

    const handleParentChange = (option) => {
        setBrandsData((prev) => ({
            ...prev,
            brands: [{ ...prev.brands[0], parent: option.value }]
        }));
        setSelectParent(option);
    };


    const handleSubmit = async (e) => {
        e.preventDefault();

        const payload = {
            name: brandsData.brands[0].brand_name,
            slug: brandsData.brands[0].brand_slug,
            parent: brandsData.brands[0].parent
        };

        if (!payload.name.trim() || !payload.slug.trim()) {
            toast.error('All fields are required!');
            return;
        }

        easycommerce_modal(true);

        try {
            const response = await fetch(`${EASYCOMMERCE.rest_base}/products/brands`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                toast.success('Brand created successfully');
                setBrandsData({ brands: [{ brand_name: '', brand_slug: '', parent: 0 }] });
                setSelectParent({ label: 'None', value: 0 });
                if (onBrandCreated) {
                    onBrandCreated(data.data.brand);
                }
                onClose();
                await fetchData();
            } else {
                toast.error(data.data.message || 'Operation failed');
            }
        } catch (error) {
            toast.error('Operation failed');
        } finally {
            easycommerce_modal(false);
        }
    };

    const fetchData = async () => {
        try {
            const response = await fetch(`${EASYCOMMERCE.rest_base}/products/brands`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
            });
            const data = await response.json();
            const flattened = flattenBrands(data.data.brands);
            setFlatBrands(flattened);
        } catch (error) {
            console.error('Error fetching data:', error);
        }
    };

    useEffect(() => {
        if (isOpen) {
            fetchData();
        }
    }, [isOpen]);

    if (!isOpen) return null;

    const selectedParentOptions = [
        { label: 'None', value: 0 },
        ...flatBrands.map(brand => ({ label: brand.name, value: brand.id }))
    ];

    return (
		<div className="w-screen h-screen inset-0 flex items-center justify-center font-inter backdrop-blur-sm fixed top-0 left-0 bg-[#00000082] z-[9999]">
			<div className="relative bg-white w-[800px] max-h-[90vh] rounded-[22px] shadow-lg z-10">
                <button
                    onClick={onClose}
                    className="group absolute w-[24px] h-[24px] top-[-15px] left-[795px] bg-white rounded-full hover:bg-[#FF3A52] flex items-center justify-center transition-colors duration-200"
                >
                    <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.260418 1.10798C0.607642 0.768042 1.17015 0.768042 1.51731 1.10798L5 4.5176L8.48269 1.10798C8.82991 0.768042 9.39241 0.768042 9.73958 1.10798C10.0868 1.44792 10.0868 1.99862 9.73958 2.33851L6.2569 5.74813L9.73958 9.15775C10.0868 9.49769 10.0868 10.0484 9.73958 10.3883C9.39236 10.7282 8.82985 10.7282 8.48269 10.3883L5 6.97866L1.51731 10.3883C1.17009 10.7282 0.607587 10.7282 0.260418 10.3883C-0.0867505 10.0483 -0.086806 9.49764 0.260418 9.15775L3.7431 5.74813L0.260418 2.33851C-0.086806 1.99857 -0.086806 1.44787 0.260418 1.10798Z" fill="#3C3C42" className="transition-colors duration-200 group-hover:fill-white"/>
                    </svg>
                </button>
				<div className="flex justify-between items-center border-b px-6 py-4">
					<Title title={"Add New Brand"} />
				</div>
				<div className="p-6 flex flex-col gap-4 max-h-[75vh] overflow-visible">
					<div className="flex flex-col gap-2">
						<SubTitle
							SubTitle="Name"
							notice="It'll be used to filter products"
						/>
						<TextField
							name="brand_name"
							value={brandsData.brands[0].brand_name}
							onChange={handleChange}
							placeholder="Enter Name"
							className="h-ec-input"
						/>
					</div>
					<div className="flex flex-col gap-2">
						<SubTitle
							SubTitle="Slug"
							notice="URL-friendly slug of the brand"
						/>
						<div className="h-ec-input rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out overflow-hidden flex">
							<div className="h-ec-input flex items-center justify-center text-ec-light-black pl-4">
								{EASYCOMMERCE.home_url}/shop/brands/
							</div>
							<input
								type="text"
								name="brand_slug"
								className="h-ec-input border-none outline-none shadow-none p-0 text-ec-body font-inter text-[14px] leading-[20px]"
								value={brandsData.brands[0].brand_slug}
								onChange={(e) => {
									const val = e.target.value;
									setBrandsData(prev => ({
										...prev,
										brands: [{ ...prev.brands[0], brand_slug: val }]
									}));
								}}
							/>
						</div>
					</div>
					<div className="flex flex-col gap-2">
						<SubTitle
							SubTitle="Parent"
							notice="Select a parent brand if any"
						/>
						<div className="w-full h-ec-input">
							<Dropdown
								options={selectedParentOptions}
								placeholder="Select Parent"
								onChange={handleParentChange}
								value={selectParent.value}
							/>
						</div>
					</div>
					<div className="flex justify-end mt-6">
						<Button
							className="easycommerce-outline-button"
							value="Create Brand"
							onClick={handleSubmit}
						/>
					</div>
				</div>
			</div>
		</div>
	);
}

export default BrandModal
