import React, { useState, useEffect, useCallback, useRef } from 'react';
import { motion } from 'framer-motion';
import { Tooltip } from 'react-tooltip';
import { toast } from 'react-toastify';
import { __ } from '@wordpress/i18n';
import PanelTitle from '../common/PanelTitle';
import AttrItem from './components/AttrItem';
import AttributeModal from './components/AttributeModal';
import AiGenerateAttributes from '../common/AiGenerate/AiGenerateAttributes';

/**
 * ProductAttr Component
 *
 *
 * State Management:
 * - productAttributes: Array of attributes currently assigned to the product (with values)
 * - selectedAttributes: Array of attribute IDs currently selected in the modal
 * - isOpen: Boolean for panel collapse/expand state
 * - attributeModal: Boolean for modal visibility
 * - isGenerating: Boolean for AI generation loading state
 * - isInitialized: Ref to prevent re-initialization of productAttributes
 *
 * Props:
 * @param {Function} setProductAttributes - Callback to send updated productAttributes to parent component
 * @param {Array} prevData - Initial attribute data for the product (array of {id, values})
 * @param {Function} fetchAttrs - Function to fetch updated attribute list from server
 * @param {Array} globalAttributes - Complete list of available attributes (array of {id, name, type, options})
 * @param {string} productTitle - Product title used for AI attribute generation
 *
 * Key Functions:
 * - handleAddAttrItem: Adds the next unused attribute to the product
 * - handleAttributeAdded: Updates productAttributes based on modal selections
 * - handleAIGenerate: Generates attributes using AI API
 * - updateAttrItem: Updates a specific attribute's values
 * - handleDeleteAttrItem: Removes an attribute from the product
 */
const ProductAttr = ({globalAttributes, fetchGlobalAttributes, productAttributes, setProductAttributes, productTitle }) => {
    const [aiOpen, setAiOpen] = useState(false);
    const [isOpen, setIsOpen] = useState(true);
    const [attributeModal, setAttributeModal] = useState(false);
    const [isGenerating, setIsGenerating] = useState(false);
    const [selectedAttributes, setSelectedAttributes] = useState([]);

    useEffect(() => {
        if (productAttributes && productAttributes.length > 0) {
            setProductAttributes(productAttributes);
        }
    }, [productAttributes]);

    /**
     * Add a new attribute item (e.g. user clicks "Add Attribute" button)
     * 
     * This function is not in use currently since we are using a modal to select attributes.
     */
    const handleAddAttrItem = (e) => {
        e.preventDefault();
        const selected = globalAttributes.filter((attr) => {
            return !productAttributes.map((item) => item.id).includes(attr.id);
        })[0]

        setProductAttributes((prev) => [
            ...prev,
            {
                id: selected.id,
                values: []
            }
        ]);
    };

    /**
     * Update an existing attribute item
     * @param {Object} item - The item to update
     * @param {Object} newValue - New values to merge into the item
     */
    const updateAttrItem = useCallback((item, newValue) => {
        setProductAttributes((prev) =>
            prev.map((attr) => (attr.id === item.id ? { ...attr, ...newValue } : attr))
        );
    }, []);

    /**
     * Remove an attribute item by its ID
     * @param {number} idToRemove - ID of the attribute item to delete
     */
    const handleDeleteAttrItem = useCallback((idToRemove) => {
        setProductAttributes((prev) => prev.filter((item) => item.id !== idToRemove));
    }, []);

    /**
     * Send updated attributes list to parent or API whenever it changes
     */
    useEffect(() => {
        setProductAttributes(productAttributes);
    }, [productAttributes, setProductAttributes]);

    const handleAttributeAdded = async (selectedAttributes) => {
        if (selectedAttributes) {
            const newAttrItems = selectedAttributes.map(id => {
                const existing = productAttributes.find(item => item.id === id);
                return existing || { id, values: [] };
            });
            
            await fetchGlobalAttributes().then(() => {
                setProductAttributes(newAttrItems);
            });

            setSelectedAttributes([]);
            setAttributeModal(false);
        }
    };

    /**
     * Generate attributes using AI
     */
    const handleAIGenerate = async (data) => {
        if (data) {
            setIsGenerating(true);

            const generatedAttrs = data;

            const newAttrItems = generatedAttrs.map(attr => ({
                id: attr.attribute_id,
                values: attr.value_ids.map(valueId => ({
                    id: valueId,
                    attribute_id: attr.attribute_id
                }))
            }));

            await fetchGlobalAttributes().then(() => {
                setProductAttributes(newAttrItems);
            });

            toast.success(__('Attributes generated successfully!', 'easycommerce'));
            setIsGenerating(false);
        } else {
            toast.error(__('Failed to generate attributes', 'easycommerce'));
        }
    }

    return (
        <>
            {aiOpen && (
                <AiGenerateAttributes
                    productTitle={productTitle}
                    setAiOpen={setAiOpen}
                    setAiContent={handleAIGenerate}
                />
            )}
            <div>
                <div class="bg-white rounded-xl border-ec-table-stock border border-solid">
                    <div class="py-[14px] px-6 flex items-center justify-between border-b border-ec-table-stock border-solid rounded-t-xl">
                        <PanelTitle
                            title="Attributes"
                            notice={__('Add attributes to your product to help customers filter and search for products more easily.', 'easycommerce')}
                        />
                        <div className="panel-actions">
                            <div>
                                <button
                                    data-tooltip-id={!productTitle ? 'ai-summary' : ''}
                                    data-tooltip-content="Add product title first!"
                                    className={`ai-generate ${productTitle ? '' : 'grayscale opacity-50 cursor-not-allowed'}`}
                                    type="button"
                                    onClick={() => productTitle && setAiOpen(true)}
                                    disabled={!productTitle || isGenerating}
                                >
                                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M13.4235 9.7689C9.65184 11.015 8.39589 12.2712 7.14976 16.0427C7.04724 16.3527 6.6088 16.3527 6.5063 16.0427C5.26021 12.271 4.00401 11.015 0.232545 9.7689C-0.0775151 9.66638 -0.0775151 9.22794 0.232545 9.12544C4.00422 7.87935 5.26017 6.62315 6.5063 2.85169C6.60882 2.54163 7.04725 2.54163 7.14976 2.85169C8.39585 6.62336 9.65205 7.87931 13.4235 9.12544C13.7336 9.22796 13.7336 9.6664 13.4235 9.7689Z" fill="url(#paint0_linear_5708_10443)"/>
                                        <path d="M16.1579 3.57531C14.2725 4.19795 13.6441 4.82641 13.0206 6.71261C12.9698 6.86764 12.7506 6.86764 12.6989 6.71261C12.0763 4.82722 11.4478 4.1988 9.56159 3.57531C9.40655 3.52447 9.40655 3.30526 9.56159 3.25358C11.447 2.63094 12.0754 2.00248 12.6989 0.116274C12.7497 -0.0387581 12.9689 -0.0387581 13.0206 0.116274C13.6433 2.00166 14.2717 2.63009 16.1579 3.25358C16.313 3.30442 16.313 3.52363 16.1579 3.57531Z" fill="url(#paint1_linear_5708_10443)"/>
                                        <defs>
                                        <linearGradient id="paint0_linear_5708_10443" x1="6.82803" y1="2.61914" x2="6.82803" y2="16.2752" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#3200FF"/>
                                        <stop offset="1" stop-color="#FF48E7"/>
                                        </linearGradient>
                                        <linearGradient id="paint1_linear_5708_10443" x1="12.8598" y1="0" x2="12.8598" y2="6.82889" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#3200FF"/>
                                        <stop offset="1" stop-color="#FF48E7"/>
                                        </linearGradient>
                                        </defs>
                                    </svg>
                                    <span className="easycommerce-ai-generate-text">
                                        {isGenerating ? __('Generating...', 'easycommerce') : __('Generate with AI', 'easycommerce')}
                                    </span>
                                </button>

                                {!productTitle && <Tooltip
                                    id="ai-summary"
                                    place="top"
                                    style={{ backgroundColor: '#7351fd', color: 'white' }}
                                />}
                            </div>

                            <button
                                className="panel-collapse"
                                type="button"
                                onClick={() => setIsOpen(!isOpen)}
                            >
                                <svg
                                    className={`transition-transform duration-300 ${
                                        isOpen ? '' : 'rotate-180'
                                    }`}
                                    width="11"
                                    height="6"
                                    viewBox="0 0 11 6"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
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
                            transition: { duration: 0.3, ease: 'easeInOut' }
                        }}
                        style={{
                            visibility: isOpen ? 'visible' : 'hidden'
                        }}
                    >
                        <div class="p-6 duration-300">
                            <div class="flex flex-col gap-3 mb-8">
                                {!isGenerating && productAttributes.length > 0 ? productAttributes.map((item, index) => (
                                    <AttrItem globalAttributes={globalAttributes} selectedAttrs={productAttributes} key={index} item={item} updateAttr={updateAttrItem} handleDelete={handleDeleteAttrItem} />
                                )) : isGenerating ? (
                                    <span className='text-ec-body font-inter text-sm leading-[20px]'>
                                        {__('Fetching generated attributes...', 'easycommerce')}
                                    </span>
                                ) : (
                                    <span className='text-ec-body font-inter text-sm leading-[20px]'>
                                        {globalAttributes.length > 0 ?
                                        __('No attributes are added yet. Click the "Add New" button below to add attributes to this product.', 'easycommerce')
                                        : __('Add options like color or size. Click the "Manage Attributes" button below or "Generate with AI" above.', 'easycommerce')}
                                    </span>
                                )}
                            </div>
                            <div class="flex items-center justify-end">
                                <button
                                    className="easycommerce-outline-button group flex gap-[6px] items-center"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        setSelectedAttributes(productAttributes.map(item => item.id));
                                        setAttributeModal(true);
                                    }}
                                >
                                    <svg class="fill-ec-primary group-hover:fill-white duration-300" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <path d="M6.5 0C6.1119 0 5.7973 0.314618 5.7973 0.702703V5.79732H0.702703C0.314618 5.79732 0 6.11192 0 6.50002C0 6.88812 0.314618 7.20272 0.702703 7.20272H5.7973V12.2973C5.7973 12.6854 6.1119 13 6.5 13C6.8881 13 7.2027 12.6854 7.2027 12.2973V7.20272H12.2973C12.6854 7.20272 13 6.88812 13 6.50002C13 6.11192 12.6854 5.79732 12.2973 5.79732H7.2027V0.702703C7.2027 0.314618 6.8881 0 6.5 0Z"/>
                                    </svg>
                                    {__('Add Attribute', 'easycommerce')}
                                </button>
                            </div>
                            <input type="hidden" name="product_attributes" value={JSON.stringify(productAttributes)} />
                        </div>
                    </motion.div>
                </div>
                {attributeModal && (
                    <AttributeModal
                        globalAttributes={globalAttributes}
                        isOpen={attributeModal}
                        onClose={() => setAttributeModal(false)}
                        onAttributeAdded={handleAttributeAdded}
                        selectedAttributes={selectedAttributes}
                        setSelectedAttributes={setSelectedAttributes}
                    />
                )}
            </div>
        </>
    );
};

export default ProductAttr;