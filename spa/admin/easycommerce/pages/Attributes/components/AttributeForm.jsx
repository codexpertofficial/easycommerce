import React, { useState, useEffect } from "react";
import { toast } from "react-toastify";
import Button from "../../../../common/components/inputs/Button";
import Dropdown from "../../../../common/components/inputs/Dropdown";
import TextField from "../../../../common/components/inputs/TextField";
import SubTitle from "../../../../common/components/SubTitle";

const AttributeForm = ({
    editingAttribute,
    attributes = [],
    attributesData,
    setAttributesData,
    field,
    setField,
    onAttributeCreated,
    onAttributeUpdated,
    onCancel,
    noPadding = false,
}) => {
    const [selectType, setSelectType] = useState('Text');

    const selectedTypeOptions = [
        { value: "Text", label: "Text" },
        { value: "Image", label: "Image" },
        { value: "Color", label: "Color" },
    ];

    const addNewField = () => {
        setField([...field, { label: '', color: '#000000', image: null, uploader: false }]);
    };

    const handleFieldChange = (index, value) => {
        const updatedField = [...field];
        updatedField[index].label = value;
        setField(updatedField);
    };

    const handleColorChange = (index, color) => {
        const updatedField = [...field];
        updatedField[index].color = color;
        setField(updatedField);
    };

    const openMediaUploader = (index) => {
        const mediaUploader = wp.media({
            title: 'Select an Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        mediaUploader.on('select', function () {
            const attachment = mediaUploader.state().get('selection').first().toJSON();

            const updatedField = [...field];
            updatedField[index].image = {
                id: attachment.id,
                url: attachment.url,
                alt: attachment.alt || ''
            };

            setField(updatedField);
        });

        mediaUploader.open();
    };

    const handleRemoveField = (indexToRemove) => {
        setField((prevFields) =>
            prevFields.length > 1
                ? prevFields.filter((_, index) => index !== indexToRemove)
                : prevFields
        );
    };

    const handleTypeChange = (value) => {
        setSelectType(value);
        setAttributesData((prev) => ({
            ...prev,
            attributes: [
                {
                    ...prev.attributes[0],
                    attribute_type: value
                }
            ]
        }));

        if (!editingAttribute) {
            setField([{ label: '', color: '', image: null, uploader: false }]);
        }
    };

    const slugify = (text) => {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-');
    };

    const handleChange = (e) => {
        const { name, value } = e.target;

        setAttributesData((prev) => {
            const updatedAttribute = {
                ...prev.attributes[0],
                [name]: value,
            };

            if (name === 'attribute_name') {
                updatedAttribute.attribute_slug = slugify(value);
            }

            return {
                ...prev,
                attributes: [updatedAttribute],
            };
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        const attr = attributesData.attributes[0];

        if (!attr.attribute_name.trim()) {
            toast.error('Attribute name are required!');
            return;
        }

        if (!attr.attribute_slug.trim()) {
            toast.error('Attribute slug are required!');
            return;
        }

        const nameExists = attributes.some(
            (a) =>
                a.name.trim().toLowerCase() === attr.attribute_name.trim().toLowerCase() &&
                (!editingAttribute || a.id !== editingAttribute.id)
        );

        const slugExists = attributes.some(
            (a) =>
                a.slug.trim().toLowerCase() === attr.attribute_slug.trim().toLowerCase() &&
                (!editingAttribute || a.id !== editingAttribute.id)
        );

        if (nameExists) {
            toast.error('Attribute name already exists!');
            return;
        }
        if (slugExists) {
            toast.error('Attribute slug already exists!');
            return;
        }

        for (let i = 0; i < field.length; i++) {
            const f = field[i];
            if (selectType === 'Text' && !f.label.trim()) {
                toast.error(`Option ${i + 1} must have a label!`);
                return;
            }

            if (selectType === 'Image' && (!f.label.trim() || !f.image)) {
                toast.error(`Option ${i + 1} must have a label and image`);
                return;
            }

            if (selectType === 'Color' && (!f.label.trim() || !f.color)) {
                toast.error(`Option ${i + 1} must have a label and color`);
                return;
            }
        }

        let options = [];

        if (selectType === 'Text') {
            options = field.map(f => {
                const slug = f.label.toLowerCase().replace(/\s+/g, '-');
                return {
                    ...(f.id && { id: f.id }),
                    name: f.label,
                    slug: slug,
                    value: f.label
                };
            });
        } else if (selectType === 'Image') {
            options = field.map(f => {
                const slug = f.label.toLowerCase().replace(/\s+/g, '-');
                return {
                    ...(f.id && { id: f.id }),
                    name: f.label,
                    slug: slug,
                    value: f.image?.id?.toString() || f.label
                };
            });
        } else if (selectType === 'Color') {
            const isValidHex = (color) => /^#([0-9A-F]{3}){1,2}$/i.test(color);
            options = field.map(f => {
                const slug = f.label.toLowerCase().replace(/\s+/g, '-');
                const value = isValidHex(f.color) ? f.color : f.label;
                return {
                    ...(f.id && { id: f.id }),
                    name: f.label,
                    slug: slug,
                    value: value
                };
            });
        }

        const hasDuplicates = options.some((option, index) =>
            index !== options.findIndex((o) => o.name.toLowerCase() === option.name.toLowerCase())
        );

        if (hasDuplicates) {
            toast.error('Duplicate option labels are not allowed!');
            return;
        }

        easycommerce_modal(true);

        const finalData = {
            name: attr.attribute_name,
            slug: attr.attribute_slug,
            type: selectType,
            options: options
        };

        const isEditing = !!editingAttribute;

        try {
            const response = await fetch(
                isEditing
                    ? `${EASYCOMMERCE.rest_base}/attributes/${editingAttribute.id}`
                    : `${EASYCOMMERCE.rest_base}/attributes`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': EASYCOMMERCE.nonce,
                    },
                    body: JSON.stringify(finalData),
                }
            );

            const result = await response.json();

            if (result.success) {
                if (isEditing) {
                    toast.success('Attribute updated successfully!');
                    onAttributeUpdated && onAttributeUpdated();
                } else {
                    toast.success('Attribute created successfully!');
                    onAttributeCreated && onAttributeCreated();
                }
                // Reset form
                setField([{ label: '', color: '', image: null, uploader: false }]);
                setSelectType('Text');
                setAttributesData({
                    attributes: [
                        { attribute_name: '', attribute_slug: '', attribute_type: '', options: [] }
                    ]
                });
            } else {
                console.error('API Error:', result);
            }
        } catch (error) {
            console.error('Error saving data:', error);
        } finally {
            easycommerce_modal(false);
        }
    };

    const handleCancel = () => {
        easycommerce_modal(true);

        setField([{ label: '', color: '', image: null, uploader: false }]);
        setSelectType('Text');
        setAttributesData({
            attributes: [
                {
                    attribute_name: '',
                    attribute_slug: '',
                    attribute_type: '',
                    options: []
                }
            ]
        });

        setTimeout(() => {
            easycommerce_modal(false);
        }, 100);

        onCancel && onCancel();
    };

    useEffect(() => {
        if (!editingAttribute) {
            setField([{ label: '', color: '#000000', image: null, uploader: false }]);
        }
    }, [selectType]);

    useEffect(() => {
        if (!editingAttribute) return;

        const attrType = editingAttribute.type;
        setSelectType(attrType);

        setAttributesData({
            attributes: [{
                attribute_name: editingAttribute.name,
                attribute_slug: editingAttribute.slug,
                attribute_type: attrType
            }]
        });

        const options = editingAttribute.options || [];

        const parsedOptions = options.map(opt => ({
            id: opt.id,
            label: opt.name || opt.label || opt.value || '',
            color: attrType === 'Color' ? opt.value : '',
            image: attrType === 'Image' ? { id: opt.value, url: opt.url, alt: '' } : null,
            uploader: false,
        }));

        setField(parsedOptions.length > 0
            ? parsedOptions
            : [{ label: '', color: '', image: null, uploader: false }]
        );
    }, [editingAttribute]);
    return (
        <>
            <div className={`flex flex-col gap-4 ${noPadding ? '' : 'p-6'}`}>
                <div className='flex flex-col gap-2'>
                    <SubTitle SubTitle="Name" notice="It'll show on the single product screen"/>
                    <TextField 
                        name={`attribute_name`}
                        value={attributesData.attributes[0].attribute_name}
                        onChange={handleChange} 
                        placeholder="Enter Name" 
                        className="h-ec-input"
                    />
                </div>
                <div className='flex flex-col gap-2'>
                    <SubTitle SubTitle="Slug" notice="URL-friendly slug of the attribute"/>
                    <div className="rounded-lg h-ec-input font-inter text-[14px] leading-[20px] border border-ec-table-stock
                      placeholder-ec-placeholder hover:border-ec-primary focus-within:border-ec-primary 
                        focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors
                        duration-300 ease-in-out overflow-hidden flex items-center">
                        <div className="h-ec-input flex items-center justify-center text-ec-light-black pl-4">
                            {EASYCOMMERCE.home_url}/shop/attributes/
                        </div>
                        <input
                            type="text"
                            name="attribute_slug"
                            className="h-ec-input border-none outline-none shadow-none p-0 text-ec-body font-inter text-[14px] leading-[20px]"
                            value={attributesData.attributes[0].attribute_slug} 
                            onChange={handleChange}
                        />
                    </div>
                </div>
                <div className='flex flex-col gap-2'>
                    <SubTitle SubTitle="Type" notice="How customers will see this on the single product screen"/>
                    <div className="w-full h-ec-input">
                        <Dropdown
                            key={selectType}
                            options={selectedTypeOptions}
                            placeholder={selectType}
                            onChange={(option) => handleTypeChange(option.value)}
                            value={selectType}
                        /> 
                    </div>
                </div>
                <div className='flex flex-col gap-2 mt-2'>
                    <SubTitle SubTitle="Options" notice="Options of the attribute"/>
                    <div className="flex flex-col gap-3">
                        {
                        selectType === 'Text' &&  (
                            <>
                                {field.map((option, index)=>(
                                    <div key={index} className="flex gap-4 items-center">    
                                        <div className="flex-1">
                                            <TextField
                                                value={option.label}
                                                onChange={(e) => handleFieldChange(index, e.target.value)}
                                                name="option"
                                                placeholder="Write here"
                                                className="h-ec-input"
                                            />
                                        </div>
                                        <div className="flex gap-2">
                                            <button onClick={(e) => { e.preventDefault(); addNewField(); }}  className="group w-10 h-10 flex justify-center items-center border border-solid border-ec-table-stock rounded-lg bg-[#ffffff] hover:bg-[#7351FD08] hover:border-none duration-300">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" className="size-5 fill-ec-body group-hover:fill-ec-primary duration-300">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10 0C9.40292 0 8.91892 0.484027 8.91892 1.08108V8.91895H1.08108C0.484027 8.91895 0 9.40295 0 10C0 10.5971 0.484027 11.0811 1.08108 11.0811H8.91892V18.9189C8.91892 19.516 9.40292 20 10 20C10.5971 20 11.0811 19.516 11.0811 18.9189V11.0811H18.9189C19.516 11.0811 20 10.5971 20 10C20 9.40295 19.516 8.91895 18.9189 8.91895H11.0811V1.08108C11.0811 0.484027 10.5971 0 10 0Z" />
                                                </svg>
                                            </button>
                                            <button onClick={(e) => { e.preventDefault(); handleRemoveField(index); }} className="group w-10 h-10 flex justify-center items-center border border-solid border-ec-table-stock rounded-lg bg-[#ffffff] hover:bg-[#FF3A521A] hover:border-none duration-300">
                                                <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg" className="size-5 fill-ec-body group-hover:fill-ec-red duration-300">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.1501 6.92017H1.85394L2.90454 17.3333C2.99002 18.184 3.68756 18.822 4.53044 18.822H12.4701C13.3147 18.822 14.0105 18.184 14.096 17.3333L15.1466 6.92017H15.1501ZM15.8442 5.1667C15.8442 4.37763 15.2074 3.73094 14.4303 3.73094H2.57632C1.79927 3.73094 1.16243 4.37765 1.16243 5.1667V5.74221H15.8443L15.8442 5.1667ZM5.04676 2.55558V2.49134C5.04676 1.1198 6.14951 0 7.50016 0H9.50217C10.8528 0 11.9556 1.1198 11.9556 2.49134V2.55558H14.4287C15.8443 2.55558 17 3.72918 17 5.1667V6.33077C17 6.65542 16.7393 6.91931 16.4204 6.91931H16.3084L15.245 17.4522C15.098 18.9036 13.9038 19.9991 12.4678 19.9991L4.53225 20C3.09698 20 1.90182 18.9045 1.75498 17.4531L0.691554 6.92017H0.579571C0.25987 6.92017 0 6.65542 0 6.33164V5.16757C0 3.73005 1.15573 2.55645 2.57135 2.55645H5.04444L5.04676 2.55558ZM10.7972 2.55558V2.49134C10.7972 1.76912 10.2125 1.17536 9.50123 1.17536H7.49921C6.78799 1.17536 6.20328 1.76912 6.20328 2.49134V2.55558H10.7972ZM5.75952 14.8916C5.75952 15.2163 5.49879 15.4802 5.17995 15.4802C4.86025 15.4802 4.60038 15.2154 4.60038 14.8916V10.8005C4.60038 10.4759 4.8611 10.212 5.17995 10.212C5.49965 10.212 5.75952 10.4767 5.75952 10.8005V14.8916ZM11.2261 10.8005C11.2261 10.4759 11.4868 10.212 11.8057 10.212C12.1254 10.212 12.3852 10.4767 12.3852 10.8005V14.8916C12.3852 15.2163 12.1245 15.4802 11.8057 15.4802C11.486 15.4802 11.2261 15.2154 11.2261 14.8916V10.8005ZM9.07277 15.9421C9.07277 16.2667 8.81205 16.5306 8.4932 16.5306C8.1735 16.5306 7.91363 16.2659 7.91363 15.9421V9.7485C7.91363 9.42385 8.17435 9.15996 8.4932 9.15996C8.8129 9.15996 9.07277 9.42472 9.07277 9.7485V15.9421Z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                ))}   
                            </>
                        ) }
                    
                        {
                        selectType === 'Image' &&  (
                            <>
                                {field.map((option, index)=>(
                                    <div key={index} className="flex gap-3">
                                        <div className="flex grow gap-2">
                                            <div className="grow">
                                                <TextField
                                                    value={option.label}
                                                    onChange={(e) => handleFieldChange(index, e.target.value)}
                                                    name="option"
                                                    placeholder="Write here"
                                                    className="h-ec-input"
                                                />
                                            </div>
                                            <button className="w-10 h-10 border border-solid border-ec-table-stock flex justify-center items-center rounded-lg"  onClick={(e) => { e.preventDefault(); openMediaUploader(index); }}  type="button" >
                                                {option.image ? (
                                                    <img
                                                        src={option.image.url}
                                                        alt={option.image.alt || 'Selected'}
                                                        className="w-[80%] h-[45px] rounded-[4px] object-cover"
                                                    />
                                                    ) : (
                                                    <svg
                                                        width="28"
                                                        height="28"
                                                        viewBox="0 0 28 28"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <path d="M8.32735 6.9375C7.81627 6.9375 7.40234 7.35143 7.40234 7.8625C7.40234 8.37358 7.81627 8.7875 8.32735 8.7875C8.83842 8.7875 9.25235 8.37358 9.25235 7.8625C9.25235 7.35143 8.83842 6.9375 8.32735 6.9375Z" fill="#9B9BB5"/>
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M19.4251 8.28516e-05C21.4682 8.28516e-05 23.1251 1.65701 23.1251 3.70009V12.01C22.2533 11.7198 21.3202 11.5626 20.3501 11.5626C19.2216 11.5626 18.1428 11.7753 17.1518 12.1626L16.9887 11.8562C16.0325 10.0582 13.5303 9.86863 12.314 11.5013L10.1171 14.4497L9.94831 14.3052C8.62439 13.1743 6.59758 13.527 5.73385 15.0394L4.28392 17.5785C4.12089 17.8653 4.12204 18.2167 4.28739 18.5024C4.45273 18.7868 4.75797 18.9625 5.08751 18.9625H11.6712C11.5995 19.4146 11.5625 19.8783 11.5625 20.3501C11.5625 21.3201 11.7197 22.2532 12.0099 23.1251H3.70001C1.65692 23.1251 0 21.4681 0 19.425V3.70001C0 1.65692 1.65692 0 3.70001 0L19.4251 8.28516e-05ZM5.55003 7.86245C5.55003 6.33041 6.793 5.08745 8.32504 5.08745C9.85708 5.08745 11.1 6.33041 11.1 7.86245C11.1 9.3945 9.85708 10.6375 8.32504 10.6375C6.793 10.6375 5.55003 9.3945 5.55003 7.86245Z" fill="#9B9BB5"/>
                                                        <path d="M15.5089 13.0146L15.3539 12.7244C15.036 12.1255 14.2012 12.063 13.7965 12.6065L11.0087 16.3482C10.8549 16.554 10.6237 16.687 10.3693 16.7159C10.1149 16.7436 9.85939 16.665 9.66515 16.4997L8.74477 15.7111C8.30307 15.3342 7.62784 15.4521 7.33992 15.9562L6.67969 17.1125H12.1764C12.8447 15.429 14.0173 14.0009 15.5089 13.0146Z" fill="#9B9BB5"/>
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M20.3453 12.9502C24.4328 12.9502 27.7453 16.2627 27.7453 20.3502C27.7453 24.4377 24.4328 27.7502 20.3453 27.7502C16.2579 27.7502 12.9453 24.4377 12.9453 20.3502C12.9453 16.2627 16.2579 12.9502 20.3453 12.9502ZM16.183 20.3502C16.183 19.8391 16.5969 19.4252 17.108 19.4252H19.4205V17.1127C19.4205 16.6016 19.8344 16.1877 20.3455 16.1877C20.8566 16.1877 21.2705 16.6016 21.2705 17.1127V19.4205V21.2752H23.583C24.0941 19.4205 24.508 19.8391 24.508 20.3502C24.508 20.8613 24.0941 21.2752 23.583 21.2752H21.2705V23.5877C21.2705 24.0988 20.8566 24.5127 20.3455 24.5127C19.8344 24.5127 19.4205 24.0988 19.4205 23.5877V21.2752H17.108C16.5969 21.2752 16.183 20.8613 16.183 20.3502Z" fill="#9B9BB5"/>
                                                    </svg>
                                                )}
                                            </button>
                                        </div>
                                        <div className="flex gap-2">
                                            <button onClick={(e) => { e.preventDefault(); addNewField(); }}  className="group w-10 h-10 flex justify-center items-center border border-solid border-ec-table-stock rounded-lg bg-[#ffffff] hover:bg-[#7351FD08] hover:border-none duration-300">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" className="size-5 fill-ec-body group-hover:fill-ec-primary duration-300">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10 0C9.40292 0 8.91892 0.484027 8.91892 1.08108V8.91895H1.08108C0.484027 8.91895 0 9.40295 0 10C0 10.5971 0.484027 11.0811 1.08108 11.0811H8.91892V18.9189C8.91892 19.516 9.40292 20 10 20C10.5971 20 11.0811 19.516 11.0811 18.9189V11.0811H18.9189C19.516 11.0811 20 10.5971 20 10C20 9.40295 19.516 8.91895 18.9189 8.91895H11.0811V1.08108C11.0811 0.484027 10.5971 0 10 0Z" />
                                                </svg>
                                            </button>
                                            <button onClick={(e) => { e.preventDefault(); handleRemoveField(index); }} className="group w-10 h-10 flex justify-center items-center border border-solid border-ec-table-stock rounded-lg bg-[#ffffff] hover:bg-[#FF3A521A] hover:border-none duration-300">
                                                <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg" className="size-5 fill-ec-body group-hover:fill-ec-red duration-300">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.1501 6.92017H1.85394L2.90454 17.3333C2.99002 18.184 3.68756 18.822 4.53044 18.822H12.4701C13.3147 18.822 14.0105 18.184 14.096 17.3333L15.1466 6.92017H15.1501ZM15.8442 5.1667C15.8442 4.37763 15.2074 3.73094 14.4303 3.73094H2.57632C1.79927 3.73094 1.16243 4.37765 1.16243 5.1667V5.74221H15.8443L15.8442 5.1667ZM5.04676 2.55558V2.49134C5.04676 1.1198 6.14951 0 7.50016 0H9.50217C10.8528 0 11.9556 1.1198 11.9556 2.49134V2.55558H14.4287C15.8443 2.55558 17 3.72918 17 5.1667V6.33077C17 6.65542 16.7393 6.91931 16.4204 6.91931H16.3084L15.245 17.4522C15.098 18.9036 13.9038 19.9991 12.4678 19.9991L4.53225 20C3.09698 20 1.90182 18.9045 1.75498 17.4531L0.691554 6.92017H0.579571C0.25987 6.92017 0 6.65542 0 6.33164V5.16757C0 3.73005 1.15573 2.55645 2.57135 2.55645H5.04444L5.04676 2.55558ZM10.7972 2.55558V2.49134C10.7972 1.76912 10.2125 1.17536 9.50123 1.17536H7.49921C6.78799 1.17536 6.20328 1.76912 6.20328 2.49134V2.55558H10.7972ZM5.75952 14.8916C5.75952 15.2163 5.49879 15.4802 5.17995 15.4802C4.86025 15.4802 4.60038 15.2154 4.60038 14.8916V10.8005C4.60038 10.4759 4.8611 10.212 5.17995 10.212C5.49965 10.212 5.75952 10.4767 5.75952 10.8005V14.8916ZM11.2261 10.8005C11.2261 10.4759 11.4868 10.212 11.8057 10.212C12.1254 10.212 12.3852 10.4767 12.3852 10.8005V14.8916C12.3852 15.2163 12.1245 15.4802 11.8057 15.4802C11.486 15.4802 11.2261 15.2154 11.2261 14.8916V10.8005ZM9.07277 15.9421C9.07277 16.2667 8.81205 16.5306 8.4932 16.5306C8.1735 16.5306 7.91363 16.2659 7.91363 15.9421V9.7485C7.91363 9.42385 8.17435 9.15996 8.4932 9.15996C8.8129 9.15996 9.07277 9.42472 9.07277 9.7485V15.9421Z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                ))} 
                            </>
                        )}

                        {
                        selectType === 'Color' &&  (
                            <>
                                {field.map((option, index) => (
                                    <div key={index} className="flex gap-3">    
                                       <div className="flex grow gap-2">
                                            <div className="grow">
                                                <TextField
                                                    value={option.label}
                                                    onChange={(e) => handleFieldChange(index, e.target.value)}
                                                    name="option"
                                                    placeholder="Write here"
                                                    className="h-ec-input"
                                                />
                                            </div>
                                            <input
                                                type="color"
                                                value={field[index].color || '#000000'} 
                                                className="w-10 h-10 cursor-pointer border-ec-border 
                                                    [&::-webkit-color-swatch-wrapper]:p-[px]
                                                    [&::-webkit-color-swatch]:rounded-[4px]
                                                    [&::-webkit-color-swatch]:border-0"
                                                onChange={(e) => handleColorChange(index, e.target.value)}
                                            />
                                        </div>
                                        <div className="flex gap-2">
                                            <button onClick={(e) => { e.preventDefault(); addNewField(); }}  className="group w-10 h-10 flex justify-center items-center border border-solid border-ec-table-stock rounded-lg bg-[#ffffff] hover:bg-[#7351FD08] hover:border-none duration-300">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" className="size-5 fill-ec-body group-hover:fill-ec-primary duration-300">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10 0C9.40292 0 8.91892 0.484027 8.91892 1.08108V8.91895H1.08108C0.484027 8.91895 0 9.40295 0 10C0 10.5971 0.484027 11.0811 1.08108 11.0811H8.91892V18.9189C8.91892 19.516 9.40292 20 10 20C10.5971 20 11.0811 19.516 11.0811 18.9189V11.0811H18.9189C19.516 11.0811 20 10.5971 20 10C20 9.40295 19.516 8.91895 18.9189 8.91895H11.0811V1.08108C11.0811 0.484027 10.5971 0 10 0Z" />
                                                </svg>
                                            </button>

                                            <button onClick={(e) => { e.preventDefault(); handleRemoveField(index); }} className="group w-10 h-[42px] flex justify-center items-center border border-solid border-ec-table-stock rounded-lg bg-[#ffffff] hover:bg-[#FF3A521A] hover:border-none duration-300">
                                                <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg" className="size-5 fill-ec-body group-hover:fill-ec-red duration-300">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.1501 6.92017H1.85394L2.90454 17.3333C2.99002 18.184 3.68756 18.822 4.53044 18.822H12.4701C13.3147 18.822 14.0105 18.184 14.096 17.3333L15.1466 6.92017H15.1501ZM15.8442 5.1667C15.8442 4.37763 15.2074 3.73094 14.4303 3.73094H2.57632C1.79927 3.73094 1.16243 4.37765 1.16243 5.1667V5.74221H15.8443L15.8442 5.1667ZM5.04676 2.55558V2.49134C5.04676 1.1198 6.14951 0 7.50016 0H9.50217C10.8528 0 11.9556 1.1198 11.9556 2.49134V2.55558H14.4287C15.8443 2.55558 17 3.72918 17 5.1667V6.33077C17 6.65542 16.7393 6.91931 16.4204 6.91931H16.3084L15.245 17.4522C15.098 18.9036 13.9038 19.9991 12.4678 19.9991L4.53225 20C3.09698 20 1.90182 18.9045 1.75498 17.4531L0.691554 6.92017H0.579571C0.25987 6.92017 0 6.65542 0 6.33164V5.16757C0 3.73005 1.15573 2.55645 2.57135 2.55645H5.04444L5.04676 2.55558ZM10.7972 2.55558V2.49134C10.7972 1.76912 10.2125 1.17536 9.50123 1.17536H7.49921C6.78799 1.17536 6.20328 1.76912 6.20328 2.49134V2.55558H10.7972ZM5.75952 14.8916C5.75952 15.2163 5.49879 15.4802 5.17995 15.4802C4.86025 15.4802 4.60038 15.2154 4.60038 14.8916V10.8005C4.60038 10.4759 4.8611 10.212 5.17995 10.212C5.49965 10.212 5.75952 10.4767 5.75952 10.8005V14.8916ZM11.2261 10.8005C11.2261 10.4759 11.4868 10.212 11.8057 10.212C12.1254 10.212 12.3852 10.4767 12.3852 10.8005V14.8916C12.3852 15.2163 12.1245 15.4802 11.8057 15.4802C11.486 15.4802 11.2261 15.2154 11.2261 14.8916V10.8005ZM9.07277 15.9421C9.07277 16.2667 8.81205 16.5306 8.4932 16.5306C8.1735 16.5306 7.91363 16.2659 7.91363 15.9421V9.7485C7.91363 9.42385 8.17435 9.15996 8.4932 9.15996C8.8129 9.15996 9.07277 9.42472 9.07277 9.7485V15.9421Z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </>
                        )}
                    </div>
                    <div className="flex justify-between mt-6">
                        {editingAttribute ? (
                            <Button
                                className="h-[37px] mt-[15px] easycommerce-underline-button"
                                value="Cancel"
                                onClick={handleCancel} 
                            />
                        ) : (
                            <div /> 
                        )}
                        <Button
                            className="easycommerce-outline-button"
                            value={editingAttribute ? "Update Attribute" : "Create Attribute"}
                            onClick={handleSubmit}
                        />
                    </div>
                </div>
            </div>
        </>
    );
};

export default AttributeForm;