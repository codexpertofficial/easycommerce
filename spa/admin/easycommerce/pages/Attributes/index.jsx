import React, { useEffect, useState } from "react";
import { toast } from 'react-toastify';

//components
import Title from "../../../common/components/Title";
import Table from "./components/Table";
import Pagination from "../../../common/components/Pagination";
import AttributeForm from "./components/AttributeForm";
import DeletePopup from "../../../common/components/DeletePopup";

const Attributes = ({page}) => {
    const [bulkDeleteIds, setBulkDeleteIds] = useState([]);
    const [showModal, setShowModal] = useState(false);
    const [attributes, setAttributes] = useState([]);
    const [attributesData, setAttributesData] = useState({
        attributes: [
            {
                attribute_name: '',
                attribute_slug: '',
                attribute_type: '',
                options: [],
            },
        ],
    });
    const [field, setField] = useState([{ label: '', color: '', image: null, uploader: false }]);
    const [editingAttribute, setEditingAttribute] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [totalPage, setTotalPage] = useState(1);
    const [postPerPage, setPostPerPage] = useState(10);

    const fetchData = async () => {
        setIsLoading(true);

        try {
            const response = await fetch( `${EASYCOMMERCE.rest_base}/attributes?page=${page}&per_page=${postPerPage}` , {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            }
        });

        const jsonData = await response.json();
        setAttributes(jsonData.data.attributes);
        setTotalPage(jsonData.data.pagination.total_pages);

        } catch (error) {
            console.error('Error fetching data:', error);
        }finally {
            setIsLoading(false);
            easycommerce_modal(false);
        }
    }

    const handleEditAttribute = (attributeData) => {
        setEditingAttribute(attributeData);
    };

    const handleAttributeCreated = () => {
        fetchData();
        setEditingAttribute(null);
    };

    const handleAttributeUpdated = () => {
        fetchData();
        setEditingAttribute(null);
    };

    const handleCancel = () => {
        setEditingAttribute(null);
    };

    const handleBulkDelete = async () => {
		try {
			easycommerce_modal(true);
			
			const response = await fetch(
				`${EASYCOMMERCE.rest_base}/attributes/bulk-delete`,
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
				setAttributes(
					attributes.filter((attribute) => !bulkDeleteIds.includes(attribute.id))
				);
				fetchData();
				setBulkDeleteIds([]);
				toast.success('Selected attributes deleted successfully!');
			} else {
				toast.error('Failed to delete selected attributes.');
			}
		} catch (error) {
			console.error('Bulk delete failed', error);
			toast.error('Bulk delete failed, please try again.');
			easycommerce_modal(false);
		} finally {
			easycommerce_modal(false);
            setShowModal(false);
		}
	};

    useEffect(() => {
        fetchData();
    }, []);

    return (
        <>
            <div className="product-panel-title mb-4">
                <h3>Attributes</h3>
            </div>
            <div className="grid min-[1400px]:grid-cols-10 min-[1280px]:grid-cols-12 gap-6 items-start">
                <div className="bg-white min-[1400px]:col-span-6 min-[1280px]:col-span-12 w-full self-start border border-solid border-ec-table-stock rounded-xl">
                    <div className="flex items-center border-b px-6 h-[60px]">
                        {bulkDeleteIds.length > 0 ? (
                            <button
                                onClick={(e) => {
                                    e.preventDefault();
                                    setShowModal(true);
                                    setAttributeIdToDelete(value.id);
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
                                Delete Selected
                            </button>
                        ) : (
                            <Title title="Attributes List" />
                        )}
                    </div>

                    <div className="p-6">
                        <Table
                            attributes={attributes}
                            setAttributes={setAttributes}
                            fetchData={fetchData}
                            onEdit={handleEditAttribute}
                            setAttributesData={setAttributesData}
                            setField={setField}
                            setEditingAttribute={setEditingAttribute}
                            isLoading={isLoading}
                            bulkDeleteIds={bulkDeleteIds}
                            setBulkDeleteIds={setBulkDeleteIds}
                        />
                    </div>
                    {totalPage > 1 && (
                        <Pagination
                            baseSlug="attributes"
                            current={page}
                            total={totalPage}
                        />
                    )}
                </div>

                <div className="bg-white min-[1400px]:col-span-4 min-[1280px]:col-span-12 w-full self-start border border-solid border-ec-table-stock rounded-xl">
                    <div className="flex items-center border-b pl-6 py-3.5 rtl:pr-6">
                        <Title title={editingAttribute ? 'Edit Attribute: ' + attributesData.attributes[0].attribute_name : "Add New Attribute"} />
                    </div>
                    <AttributeForm
                        editingAttribute={editingAttribute}
                        attributes={attributes}
                        attributesData={attributesData}
                        setAttributesData={setAttributesData}
                        field={field}
                        setField={setField}
                        onAttributeCreated={handleAttributeCreated}
                        onAttributeUpdated={handleAttributeUpdated}
                        onCancel={handleCancel}
                    />
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

export default Attributes;