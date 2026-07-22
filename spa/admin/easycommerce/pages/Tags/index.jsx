import React, { useEffect, useState } from "react";
import { toast } from "react-toastify";
import { __, sprintf } from '@wordpress/i18n';
// components;
import Button from "../../../common/components/inputs/Button";
import TextField from "../../../common/components/inputs/TextField";
import Title from "../../../common/components/Title";
import SubTitle from "../../../common/components/SubTitle";
import Table from "./components/Table";
import Pagination from "../../../common/components/Pagination";
import DeletePopup from "../../../common/components/DeletePopup";

const Tags = ({ page }) => {
    const [isLoading, setIsLoading] = useState(true);
    const [tags, setTags] = useState([]);
    const [flatTags, setFlatTags] = useState([]);
    const [tagsData, setTagsData] = useState({ tags: [{ tag_name: '', tag_slug: '' }] });
    const [editingTag, setEditingTag] = useState(null);
    const [totalPage, setTotalPage] = useState(1);
    const [postPerPage, setPostPerPage] = useState(10);
    const [bulkDeleteIds, setBulkDeleteIds] = useState([]);
    const [showModal, setShowModal] = useState(false);

    useEffect(() => {
        fetchData();
    }, []);

    const generateSlug = (text) => {
        return text
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9\-]/g, '');
    };

    const flattenTags = (tags) => {
        let flatList = [];

        tags.forEach((tag) => {
            flatList.push({
                id: tag.id,
                name: tag.name,
                slug: tag.slug,
            });

            if (tag.children && tag.children.length > 0) {
                flatList = flatList.concat(flattenTags(tag.children));
            }
        });

        return flatList;
    };

    const fetchData = async () => {
        setIsLoading(true);
        
        try {
            const response = await fetch(`${EASYCOMMERCE.rest_base}/products/tags?page=${page}&per_page=${postPerPage}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
            });
            const data = await response.json();
            setTags(data.data.tags);

            const flattened = flattenTags(data.data.tags);
            setFlatTags(flattened);
            setTotalPage(data.data.pagination.total_pages);

        } catch (error) {
            toast.error(__('Failed to fetch tags.', 'easycommerce'));
        }finally {
            setIsLoading(false); 
        } 
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        if (name === 'tag_name') {
            const newSlug = generateSlug(value);
            setTagsData((prev) => ({
                ...prev,
                tags: [{ ...prev.tags[0], tag_name: value, tag_slug: newSlug  }]
            }));
        }
    };

    const handleEditTag = (id) => {
        easycommerce_modal(true);
    
        const tag  = findTagById(tags, id);
        const flatEntry = flatTags.find(fc => fc.id === id);
    
        if (tag && flatEntry) {
            setTagsData({
                tags: [{
                    tag_name: tag.name,
                    tag_slug: tag.slug,
                }]
            });
    
            setEditingTag(id);
        }
        easycommerce_modal(false);
    };
    
    const findTagById = (tags, id) => {
        for (let tag of tags) {
            if (tag.id === id) return tag;
            if (tag.children && tag.children.length > 0) {
                const found = findTagById(tag.children, id);
                if (found) return found;
            }
        }
        return null;
    };

    const handleSubmit = async () => {
        const payload = {
            name: tagsData.tags[0].tag_name,
            slug: tagsData.tags[0].tag_slug,
        };

        if (!payload.name.trim() || !payload.slug.trim()) {
            toast.error(__('All fields are required!', 'easycommerce'));
            return;
        }
        const method = editingTag ? 'PUT' : 'POST';

        try {
            const url = editingTag
                ? `${EASYCOMMERCE.rest_base}/products/tags/${editingTag}`
                : `${EASYCOMMERCE.rest_base}/products/tags`;

            easycommerce_modal(true);
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                toast.success(
                    editingTag
                        ? __('Tag updated successfully', 'easycommerce')
                        : __('Tag created successfully', 'easycommerce')
                );
                fetchData();
                setTagsData({ tags: [{ tag_name: '', tag_slug: '' }] });
                setEditingTag(null);
                easycommerce_modal(false);
            } else {
                toast.error(data.data.message || __('Operation failed', 'easycommerce'));
                easycommerce_modal(false);
            }
        } catch (error) {
            toast.error(__('Operation failed', 'easycommerce'));
        }
    };

    const handleCancel = () => {
        easycommerce_modal(true);
 
        setTagsData({ tags: [{ tag_name: '', tag_slug: '' }] });
        setEditingTag(null);
     
        setTimeout(() => {
            easycommerce_modal(false);
        }, 100); 
    };

    const handleBulkDelete = async () => {
        try {
            easycommerce_modal(true);
            
            const response = await fetch(
                `${EASYCOMMERCE.rest_base}/products/tags/bulk-delete`,
                {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': EASYCOMMERCE.nonce,
                    },
                    body: JSON.stringify({ ids: bulkDeleteIds }),
                }
            );

            const data = await response.json();
            easycommerce_modal(false);

            if (data.success) {
                setFlatTags(
                    flatTags.filter((tag) => !bulkDeleteIds.includes(tag.id))
                );
                fetchData();
                setBulkDeleteIds([]);
                toast.success(__('Selected tags deleted successfully!', 'easycommerce'));
            } else {
                toast.error(__('Failed to delete selected tags.', 'easycommerce'));
            }
        } catch (error) {
            console.error('Bulk delete failed', error);
            toast.error(__('Bulk delete failed, please try again.', 'easycommerce'));
            easycommerce_modal(false);
        } finally {
            easycommerce_modal(false);
            setShowModal(false);
        }
    };
    
    return (
        <>
            <div className="product-panel-title mb-4">
                <h3>{__('Tags', 'easycommerce')}</h3>
            </div>
            <div className="grid grid-cols-12 gap-6 items-start">
                <div className="bg-white ec-db-lg:col-span-8 col-span-7 w-full self-start border border-solid border-ec-table-stock rounded-xl">
                    <div className="flex items-center border-b px-6 h-[60px]">
                        {bulkDeleteIds.length > 0 ? (
                            <button
                                onClick={(e) => {
                                    e.preventDefault();
                                    setShowModal(true);
                                }}
                                className="group flex px-3 py-2 gap-2 text-sm rounded-lg justify-center items-center text-ec-body border hover:border-ec-red hover:bg-ec-red hover:text-white transition-colors duration-300"
                            >
                                <svg
                                    width="17"
                                    height="20"
                                    viewBox="0 0 17 20"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                    className="size-4 fill-ec-body group-hover:fill-white duration-300"
                                >
                                    <path
                                        fillRule="evenodd"
                                        clipRule="evenodd"
                                        d="M15.1501 6.92017H1.85394L2.90454 17.3333C2.99002 18.184 3.68756 18.822 4.53044 18.822H12.4701C13.3147 18.822 14.0105 18.184 14.096 17.3333L15.1466 6.92017H15.1501ZM15.8442 5.1667C15.8442 4.37763 15.2074 3.73094 14.4303 3.73094H2.57632C1.79927 3.73094 1.16243 4.37765 1.16243 5.1667V5.74221H15.8443L15.8442 5.1667ZM5.04676 2.55558V2.49134C5.04676 1.1198 6.14951 0 7.50016 0H9.50217C10.8528 0 11.9556 1.1198 11.9556 2.49134V2.55558H14.4287C15.8443 2.55558 17 3.72918 17 5.1667V6.33077C17 6.65542 16.7393 6.91931 16.4204 6.91931H16.3084L15.245 17.4522C15.098 18.9036 13.9038 19.9991 12.4678 19.9991L4.53225 20C3.09698 20 1.90182 18.9045 1.75498 17.4531L0.691554 6.92017H0.579571C0.25987 6.92017 0 6.65542 0 6.33164V5.16757C0 3.73005 1.15573 2.55645 2.57135 2.55645H5.04444L5.04676 2.55558ZM10.7972 2.55558V2.49134C10.7972 1.76912 10.2125 1.17536 9.50123 1.17536H7.49921C6.78799 1.17536 6.20328 1.76912 6.20328 2.49134V2.55558H10.7972ZM5.75952 14.8916C5.75952 15.2163 5.49879 15.4802 5.17995 15.4802C4.86025 15.4802 4.60038 15.2154 4.60038 14.8916V10.8005C4.60038 10.4759 4.8611 10.212 5.17995 10.212C5.49965 10.212 5.75952 10.4767 5.75952 10.8005V14.8916ZM11.2261 10.8005C11.2261 10.4759 11.4868 10.212 11.8057 10.212C12.1254 10.212 12.3852 10.4767 12.3852 10.8005V14.8916C12.3852 15.2163 12.1245 15.4802 11.8057 15.4802C11.486 15.4802 11.2261 15.2154 11.2261 14.8916V10.8005ZM9.07277 15.9421C9.07277 16.2667 8.81205 16.5306 8.4932 16.5306C8.1735 16.5306 7.91363 16.2659 7.91363 15.9421V9.7485C7.91363 9.42385 8.17435 9.15996 8.4932 9.15996C8.8129 9.15996 9.07277 9.42472 9.07277 9.7485V15.9421Z"
                                    />
                                </svg>
                                {__('Delete Selected', 'easycommerce')}
                            </button>
                        ) : (
                            <Title title={__('Tags List', 'easycommerce')} />
                        )}
                    </div>

                    <div className="p-6">
                        <Table
                            tags={flatTags}
                            fetchData={fetchData}
                            onEdit={handleEditTag}
                            isLoading={isLoading}
                            bulkDeleteIds={bulkDeleteIds}
                            setBulkDeleteIds={setBulkDeleteIds}
                        />
                    </div>
                    {totalPage > 1 && (
                        <Pagination
                            baseSlug="tags"
                            current={page}
                            total={totalPage}
                        />
                    )}
                </div>

                {/* Form Section */}
                <div className="bg-white ec-db-lg:col-span-4 col-span-5 w-full self-start border border-solid border-ec-table-stock rounded-xl">
                    <div className="flex items-center border-b pl-6 py-3.5 rtl:pr-6">
                        <Title
                            title={
                                editingTag
                                    ? sprintf(
                                        // translators: %s: tag name.
                                        __('Edit Tag: %s', 'easycommerce'),
                                        tagsData.tags[0].tag_name
                                    )
                                    : __('Add New Tag', 'easycommerce')
                            }
                        />
                    </div>

                    <div className="p-6 flex flex-col gap-4">
                        <div className='flex flex-col gap-2'>
                            <SubTitle SubTitle={__('Name', 'easycommerce')} notice={__("It'll be used to filter products", 'easycommerce')} />
                            <TextField
                                name="tag_name"
                                value={tagsData.tags[0].tag_name}
                                onChange={handleChange}
                                placeholder={__('Enter Name', 'easycommerce')}
                                className="h-ec-input" 
                            />
                        </div>

                        <div className="flex flex-col gap-2">
                            <SubTitle SubTitle={__('Slug', 'easycommerce')} notice={__('URL-friendly slug of the tag', 'easycommerce')} />

                            <div className="h-ec-input rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out overflow-hidden flex ">
                                <div className="h-ec-input flex items-center justify-center text-ec-light-black pl-4">
                                    {EASYCOMMERCE.home_url}/shop/tags/
                                </div>
                                <input
                                    type="text"
                                    name="tag_slug"
                                    className="h-ec-input border-none outline-none shadow-none p-0 text-ec-body font-inter text-[14px] leading-[20px]"
                                    // placeholder={__('Enter Slug', 'easycommerce')}
                                    value={tagsData.tags[0].tag_slug}
                                    onChange={(e) => {
                                        const val = e.target.value;
                                        setTagsData(prev => ({
                                        ...prev,
                                        tags: [{ ...prev.tags[0], tag_slug: val }]
                                        }));
                                    }}
                                />
                            </div>
                        </div>

                        <div className="flex items-center justify-between mt-6">
                            {editingTag ? (
                                <Button
                                    className="h-[37px] mt-[15px] easycommerce-underline-button"
                                    value={__('Cancel', 'easycommerce')}
                                    onClick={handleCancel}
                                />
                            ) : (
                                <div />
                            )}
                            <Button
                                className="easycommerce-outline-button"
                                value={editingTag ? __('Update Tag', 'easycommerce') : __('Create Tag', 'easycommerce')}
                                onClick={handleSubmit}
                            />
                        </div>
                    </div>
                </div>
            </div>

            {showModal && (
                <DeletePopup
                    onClose={() => {
                        setShowModal(false);
                        setBulkDeleteIds([]);
                    }}
                    onConfirm={handleBulkDelete}
                />
            )}
        </>
    );
};

export default Tags;