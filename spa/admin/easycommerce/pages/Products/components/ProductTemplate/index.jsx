import React, { useState, useMemo } from 'react';
import { motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';

import PanelTitle from '../common/PanelTitle';
import Dropdown from '../../../../../common/components/inputs/Dropdown';

const TEMPLATE_OPTIONS = [
    { value: 'template-1', label: __('Template 1', 'easycommerce') },
    { value: 'template-2', label: __('Template 2', 'easycommerce') },
];

// normalize prevData into "template-1" / "template-2"
function normalizeValue(prev) {
    if (!prev) return 'template-1';
    if (typeof prev === 'object' && prev.value) return prev.value;
    if (prev === 'Template 1') return 'template-1';
    if (prev === 'Template 2') return 'template-2';
    if (prev === 'template-1' || prev === 'template-2') return prev;
    return 'template-1';
}

const Template = ({ prevData }) => {
    const [isOpen, setIsOpen] = useState(true);
    const [template, setTemplate] = useState(() => normalizeValue(prevData));

    const selectedOption = useMemo(
        () => TEMPLATE_OPTIONS.find(o => o.value === template) || TEMPLATE_OPTIONS[0],
        [template]
    );

    return (
        <div className="bg-white rounded-xl border-ec-table-stock border border-solid overflow-hidden">
            <div className="py-[14px] px-6 flex items-center justify-between border-b border-ec-table-stock border-solid">
                <PanelTitle title={__('Product View', 'easycommerce')} notice={__('Choose a template to customize how your product page is displayed to customers.', 'easycommerce')} />
                <div className="panel-actions">
                    <button
                        className="panel-collapse"
                        type="button"
                        onClick={() => setIsOpen(!isOpen)}
                    >
                        <svg
                            className={`transition-transform duration-300 ${isOpen ? '' : 'rotate-180'}`}
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
                    transition: { duration: 0.3, ease: 'easeInOut' }
                }}
                style={{ visibility: isOpen ? 'visible' : 'hidden' }}
            >
                <div className="p-6 duration-300">
                    <div className="flex flex-col gap-4 h-ec-input">
                        <Dropdown
                            label={__("Select Template", "easycommerce")}
                            placeholder={__("Select a template", "easycommerce")}
                            options={TEMPLATE_OPTIONS}
                            value={template}
                            onChange={(e) => {
                                setTemplate(e.value);
                            }}
                        />
                    </div>

                    <img
                        className="mt-4 mx-auto"
                        src={`${EASYCOMMERCE.assets}/admin/img/templates/${template}.png`}
                        alt={selectedOption.label}
                    />

                    <input type="hidden" name="product_template" value={template} />
                </div>
            </motion.div>
        </div>
    );
};

export default Template;