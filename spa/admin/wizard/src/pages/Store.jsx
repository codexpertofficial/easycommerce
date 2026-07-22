import React, { useState } from "react";
import { __ } from '@wordpress/i18n';

const Store = ({ designs = [], selectedDesign, setSelectedDesign, hasStaticFront = false, hasProducts = false, setAsHomepage = true, setSetAsHomepage = () => {}, importDemoChecked, setImportDemoChecked }) => {
    const [migrationChecked, setMigrationChecked] = useState(false);
    const designChosen = !!selectedDesign;
    const [previewDesign, setPreviewDesign] = useState(null);

    return (
        <div
            className="bg-cover bg-center w-full flex flex-col items-center"
        >
            <div className="mb-8 font-inter">
                <h2 className='text-2xl text-center  text-ec-title font-medium'>
                    {__('Store Setup', 'easycommerce')}
                </h2>

                <p className="text-[#606060] text-[16px] text-center mt-2 max-w-xl">
                    {__('Give your store a head start. Pick a ready-made design and add demo products to preview your storefront. You can change everything later.', 'easycommerce')}
                </p>
            </div>

            <div className="w-full font-inter">
                <div className="flex flex-col w-full space-y-8">

                    {/* Store design selection */}
                    {designs.length > 0 && (
                        <div>
                            <label className="text-ec-body text-[16px] font-medium">
                                {__('Choose a Store Design', 'easycommerce')}
                            </label>
                            <p className="text-[#606060] text-[14px] mt-1 mb-4">
                                {__('Pick a ready-made design and we’ll build a matching homepage and shop for you. You can edit everything later, or skip and start from scratch.', 'easycommerce')}
                            </p>

                            <div className="grid grid-cols-3 gap-4">
                                {designs.map((design) => {
                                    const isSelected = selectedDesign === design.id;
                                    return (
                                        <button
                                            type="button"
                                            key={design.id}
                                            onClick={() => {
                                                // Re-clicking the selected design deselects it, so the
                                                // user can back out and proceed with no template.
                                                if (isSelected) {
                                                    setSelectedDesign("");
                                                    return;
                                                }
                                                setSelectedDesign(design.id);
                                                // A design needs products to look complete - opt an EMPTY store in to
                                                // demo products. Never auto-check when the store already has products
                                                // (the demo import only seeds an empty store anyway).
                                                if (!hasProducts) {
                                                    setImportDemoChecked(true);
                                                }
                                            }}
                                            className={`text-left rounded-[12px] border-2 overflow-hidden transition-all duration-200 ${
                                                isSelected
                                                    ? "border-ec-primary shadow-md"
                                                    : "border-[#E7E7EE] hover:border-ec-primary/50"
                                            }`}
                                        >
                                            <div className="relative aspect-[16/11] bg-[#F7F5FF] overflow-hidden group">
                                                {design.screenshot && (
                                                    <img
                                                        src={design.screenshot}
                                                        alt={design.label}
                                                        className="w-full h-full object-cover object-top transition-[object-position] duration-[3000ms] ease-linear group-hover:object-bottom"
                                                    />
                                                )}
                                                {/* Zoom / preview */}
                                                <span
                                                    role="button"
                                                    tabIndex={0}
                                                    title={__('Preview', 'easycommerce')}
                                                    onClick={(e) => { e.stopPropagation(); setPreviewDesign(design); }}
                                                    onKeyDown={(e) => { if (e.key === "Enter" || e.key === " ") { e.stopPropagation(); setPreviewDesign(design); } }}
                                                    className="absolute top-2 right-2 w-8 h-8 rounded-full bg-ec-primary hover:bg-ec-secondary shadow flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all"
                                                >
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                                        <circle cx="11" cy="11" r="7" />
                                                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                                        <line x1="11" y1="8" x2="11" y2="14" />
                                                        <line x1="8" y1="11" x2="14" y2="11" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div className="p-4">
                                                <div className="flex items-center justify-between">
                                                    <span className="text-ec-title text-[15px] font-medium">
                                                        {design.label}
                                                    </span>
                                                    {isSelected && (
                                                        <span className="text-ec-primary text-[12px] font-medium">
                                                            {__('Selected', 'easycommerce')}
                                                        </span>
                                                    )}
                                                </div>
                                                {design.description && (
                                                    <p className="text-[#606060] text-[12px] mt-1.5 leading-[20px]">
                                                        {design.description}
                                                    </p>
                                                )}
                                            </div>
                                        </button>
                                    );
                                })}
                            </div>

                            {/* Start-from-scratch hint - no template selected means the user
                                keeps their current pages and builds their own. */}
                            {!designChosen && (
                                <p className="text-[#606060] text-[13px] mt-4">
                                    {__('Prefer to build your own? Leave every template unselected and click Next to start from scratch.', 'easycommerce')}
                                </p>
                            )}

                            {/* Homepage opt-in - only touches the site front page on explicit consent */}
                            {designChosen && (
                                <div className="mt-6">
                                    <label className="text-ec-body text-[16px] font-medium">
                                        {__('Homepage', 'easycommerce')}
                                    </label>
                                    <label className="flex items-center space-x-2 mt-2 cursor-pointer w-fit">
                                        <input
                                            type="checkbox"
                                            className="easycommerce-checkbox-input relative pointer checked:bg-ec-primary"
                                            checked={setAsHomepage}
                                            onChange={(e) => setSetAsHomepage(e.target.checked)}
                                        />
                                        <span className="text-[#606060] text-[14px]">
                                            {__('Set the new store home as my site homepage.', 'easycommerce')}
                                            {hasStaticFront && (
                                                <span className="block text-[#B4322E] text-[12px] mt-1">
                                                    {__('Your site already has a homepage - checking this will replace it. Your current page will not be deleted.', 'easycommerce')}
                                                </span>
                                            )}
                                        </span>
                                    </label>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Import demo products */}
                    <div>
                        <label className="text-ec-body text-[16px] font-medium">
                            {__('Demo Products', 'easycommerce')}
                        </label>
                        <div className="flex flex-col space-y-4">
                            <div className="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    id="demoProducts"
                                    className="easycommerce-checkbox-input relative pointer checked:bg-ec-primary"
                                    checked={importDemoChecked}
                                    onChange={(e) => setImportDemoChecked(e.target.checked)}
                                />
                                <label
                                    htmlFor="demoProducts"
                                    className="text-[#606060] text-[14px]"
                                >
                                    {__('Import demo products to preview your storefront and test features. Remove them anytime.', 'easycommerce')}
                                </label>
                            </div>
                        </div>
                    </div>

                    {EASYCOMMERCE?.migratable_platforms_installed?.length > 0 && !EASYCOMMERCE?.migration_addon_installed && (
                        <div className="">
                            <label className="text-ec-body text-[16px] font-medium">
                                Migration
                            </label>
                            <div className="flex flex-col space-y-4">
                                <div className="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        id="wooMigration"
                                        name="woocommerce_migration"
                                        className="easycommerce-checkbox-input relative pointer checked:bg-ec-primary"
                                        checked={migrationChecked}
                                        onChange={(e) => setMigrationChecked(e.target.checked)}
                                    />
                                    <label
                                        htmlFor="wooMigration"
                                        className="text-[#606060] text-[14px]"
                                    >
                                        It looks like you have {EASYCOMMERCE?.migratable_platforms_installed?.join(' and ')} installed. Check this to enable the migration tool
                                    </label>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Design preview lightbox */}
            {previewDesign && (
                <div
                    className="fixed inset-0 z-[9999] bg-black/70 flex items-center justify-center p-8"
                    onClick={() => setPreviewDesign(null)}
                >
                    <div className="relative max-w-[1100px] w-full" onClick={(e) => e.stopPropagation()}>
                        <button
                            type="button"
                            onClick={() => setPreviewDesign(null)}
                            className="absolute -top-10 right-0 text-white text-2xl leading-none"
                            aria-label={__('Close', 'easycommerce')}
                        >
                            ×
                        </button>

                        <div className="max-h-[78vh] overflow-auto rounded-lg bg-black/20">
                            <img
                                src={previewDesign.screenshot}
                                alt={previewDesign.label}
                                className="block mx-auto w-full h-auto rounded-lg shadow-2xl select-none"
                                draggable={false}
                            />
                        </div>
                        <div className="mt-3 text-center text-white">
                            <span className="text-lg font-medium">{previewDesign.label}</span>
                            {previewDesign.description && (
                                <p className="text-sm text-white/80 mt-1">{previewDesign.description}</p>
                            )}
                        </div>
                        <div className="mt-4 flex justify-center">
                            <button
                                type="button"
                                onClick={() => {
                                    setSelectedDesign(previewDesign.id);
                                    if (!hasProducts) {
                                        setImportDemoChecked(true);
                                    }
                                    setPreviewDesign(null);
                                }}
                                className="rounded-lg bg-ec-primary text-white text-sm py-2.5 px-6"
                            >
                                {selectedDesign === previewDesign.id ? __('Selected', 'easycommerce') : __('Use this design', 'easycommerce')}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Store;
