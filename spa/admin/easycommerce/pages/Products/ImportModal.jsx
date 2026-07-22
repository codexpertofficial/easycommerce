import React, { useState, useEffect, useRef } from "react";
import { toast } from "react-toastify";
import { __, sprintf, _n } from '@wordpress/i18n';

const ImportModal = ({ onClose, onImportComplete }) => {
    const [step, setStep] = useState('upload');
    const [csvFile, setCsvFile] = useState(null);
    const [headers, setHeaders] = useState([]);
    const [mapping, setMapping] = useState({});
    const [isLoading, setIsLoading] = useState(false);
    const [progress, setProgress] = useState(0);
    const [importId, setImportId] = useState(null);
    const [importStatus, setImportStatus] = useState(null);
    const pollingIntervalRef = useRef(null);

    const handleFileChange = (e) => {
        setCsvFile(e.target.files[0]);
    };

    const uploadCsv = async () => {
        if (!csvFile) {
            toast.error(__('Please select a CSV file', 'easycommerce'));
            return;
        }

        setIsLoading(true);
        const formData = new FormData();
        formData.append('csv_file', csvFile);

        try {
            const response = await fetch(`${EASYCOMMERCE.rest_base}/importer/upload`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                setHeaders(data.data.headers);
                const autoMapping = {};
                const defaultFields = getDefaultFields();
                data.data.headers.forEach((header, index) => {
                    const headerLower = header.toLowerCase().replace(/[^a-z0-9]/g, '');
                    for (const key of Object.keys(defaultFields)) {
                        const keyLower = key.toLowerCase().replace(/[^a-z0-9]/g, '');
                        if (headerLower === keyLower || headerLower.includes(keyLower) || keyLower.includes(headerLower)) {
                            autoMapping[index] = key;
                            break;
                        }
                    }
                });
                setMapping(autoMapping);
                setStep('mapping');
                toast.success(__('CSV uploaded successfully', 'easycommerce'));
            } else {
                toast.error(data.data || __('Failed to upload CSV', 'easycommerce'));
            }
        } catch (error) {
            toast.error(__('Upload failed', 'easycommerce'));
        } finally {
            setIsLoading(false);
        }
    };

    const handleMappingChange = (headerIndex, field) => {
        setMapping(prev => ({
            ...prev,
            [headerIndex]: field
        }));
    };

    const mapColumns = async () => {
        setIsLoading(true);
        try {
            const response = await fetch(`${EASYCOMMERCE.rest_base}/importer/map`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
                body: JSON.stringify({ mapping }),
            });

            const data = await response.json();

            if (data.success) {
                setStep('import');
                toast.success(__('Columns mapped successfully', 'easycommerce'));
            } else {
                toast.error(data.data || __('Failed to map columns', 'easycommerce'));
            }
        } catch (error) {
            toast.error(__('Mapping failed', 'easycommerce'));
        } finally {
            setIsLoading(false);
        }
    };

    const importProducts = async () => {
        setIsLoading(true);
        setProgress(0);
        setImportStatus(null);

        try {
            // Start import
            const startRes = await fetch(`${EASYCOMMERCE.rest_base}/importer/import`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
            });

            const startData = await startRes.json();
            if (!startData.success) {
                toast.error(startData.data || __('Failed to start import', 'easycommerce'));
                setIsLoading(false);
                return;
            }

            const importId = startData.data.import_id;
            setImportId(importId);
            toast.success(__('Import started in background...', 'easycommerce'));

            const poll = async () => {
                const res = await fetch(`${EASYCOMMERCE.rest_base}/importer/import`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': EASYCOMMERCE.nonce,
                    },
                    body: JSON.stringify({ import_id: importId }),
                });

                const data = await res.json();
                if (data.success && data.data) {
                    const s = data.data;
                    setImportStatus(s);
                    setProgress(s.progress || 0);

                    if (s.status === 'completed') {
                        // translators: %d: number of products imported.
                        toast.success(sprintf(_n('Import completed! %d product imported', 'Import completed! %d products imported', s.imported, 'easycommerce'), s.imported));
                        if (s.errors?.length > 0) {
                            // translators: %d: number of errors that occurred during import.
                            toast.warn(sprintf(_n('%d error occurred', '%d errors occurred', s.errors.length, 'easycommerce'), s.errors.length));
                        }
                        setIsLoading(false);
                        onImportComplete();
                    } else {
                        setTimeout(poll, 2000);
                    }
                }
            };

            poll();

        } catch (err) {
            toast.error(__('Import failed to start', 'easycommerce'));
            setIsLoading(false);
        }
    };

    useEffect(() => {
        return () => {
            if (pollingIntervalRef.current) {
                clearInterval(pollingIntervalRef.current);
            }
        };
    }, []);

    const getDefaultFields = () => {
        return {
            'status': __('Status', 'easycommerce'),
            'title': __('Title', 'easycommerce'),
            'summary': __('Summary', 'easycommerce'),
            'description': __('Description', 'easycommerce'),
            'brands': __('Brands', 'easycommerce'),
            'tags': __('Tags', 'easycommerce'),
            'slug': __('Slug', 'easycommerce'),
            'thumbnail_url': __('Thumbnail Url', 'easycommerce'),
            'categories': __('Categories', 'easycommerce'),
            'attribute_names': __('Attribute Names', 'easycommerce'),
            'attribute_values_name': __('Attribute Values Name', 'easycommerce'),
            'attribute_values_value': __('Attribute Values Value', 'easycommerce'),
            'variation_names': __('Variation Names', 'easycommerce'),
            'variation_types': __('Variation Types', 'easycommerce'),
            'variation_status': __('Variation Status', 'easycommerce'),
            'regular_prices': __('Regular Prices', 'easycommerce'),
            'sale_prices': __('Sale Prices', 'easycommerce'),
            'skus': __('SKUs', 'easycommerce'),
            'stock_quantities': __('Stock Quantities', 'easycommerce'),
            'stock_limits': __('Stock Limits', 'easycommerce'),
            'variation_attribute_names': __('Variation Attribute Names', 'easycommerce'),
            'variation_attribute_values': __('Variation Attribute Values', 'easycommerce'),
            'managed_stocks': __('Managed Stocks', 'easycommerce'),
            'tax_classes': __('Tax Classes', 'easycommerce'),
            'thumbnail_urls': __('Thumbnail URLs', 'easycommerce'),
            'width_values': __('Width Values', 'easycommerce'),
            'width_units': __('Width Units', 'easycommerce'),
            'height_values': __('Height Values', 'easycommerce'),
            'height_units': __('Height Units', 'easycommerce'),
            'weight_values': __('Weight Values', 'easycommerce'),
            'weight_units': __('Weight Units', 'easycommerce'),
            'length_values': __('Length Values', 'easycommerce'),
            'length_units': __('Length Units', 'easycommerce'),
            'downloads': __('Downloads', 'easycommerce'),
            'meta_gallery_urls': __('Meta Gallery URLs', 'easycommerce'),
            'meta_gallery_titles': __('Meta Gallery Titles', 'easycommerce'),
            'meta_templates': __('Meta Templates', 'easycommerce'),
            'show_reviews': __('Show Reviews', 'easycommerce'),
            'review_text_mandatory': __('Review Text Mandatory', 'easycommerce'),
            'hide_from_shop': __('Hide From Shop', 'easycommerce'),
            'noindex': __('Noindex', 'easycommerce'),
            'published_date': __('Published Date', 'easycommerce'),
        };
    };

    const renderUploadStep = () => (
        <div className="p-6">
            <h3 className="text-xl font-semibold mb-4">{__('Upload CSV File', 'easycommerce')}</h3>
            <div className="mb-4">
                <span className="text-sm text-gray-600 mb-2 block">
                    {__('Download the', 'easycommerce')} {" "}
                    <strong className="text-blue-600">
                        <a className="text-[#9B3FFF]" href={EASYCOMMERCE.sampleCsvUrl} target="_blank" rel="noopener noreferrer">
                            {__('Demo CSV', 'easycommerce')} {" "}
                        </a>
                    </strong> {__('file. Add your data, upload it below, and click continue. To learn more,', 'easycommerce')} {" "}
                    <a className="text-[#9B3FFF]" href="https://easycommerce.dev/docs/products/csv-importer" target="_blank" rel="noopener noreferrer"><strong>{__('click here', 'easycommerce')}</strong></a>.
                </span>
            </div>
            <div className="mb-4">
                <input
                    type="file"
                    accept=".csv"
                    onChange={handleFileChange}
                    className="w-full p-2 border border-gray-300 rounded"
                />
            </div>
            <div className="flex justify-end gap-3">
                <button
                    onClick={onClose}
                    className="px-4 py-2 bg-gray-300 text-gray-700 rounded"
                >
                    {__('Cancel', 'easycommerce')}
                </button>
                <button
                    onClick={uploadCsv}
                    disabled={isLoading || !csvFile}
                    className="px-4 py-2 bg-[#9C3EFE] text-white rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    {isLoading ? __('Uploading...', 'easycommerce') : __('Continue', 'easycommerce')}
                </button>
            </div>
        </div>
    );

    const renderMappingStep = () => (
        <div className="p-6">
            <h3 className="text-xl font-semibold mb-4">{__('Column Mapping', 'easycommerce')}</h3>
            <div className="mb-4 max-h-96 overflow-y-auto">
                <table className="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr className="bg-gray-50">
                            <th className="border border-gray-300 p-2 text-left">{__('Column Name', 'easycommerce')}</th>
                            <th className="border border-gray-300 p-2 text-left">{__('Map to Field', 'easycommerce')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {headers.map((header, index) => (
                            <tr key={index}>
                                <td className="border border-gray-300 p-2">{header}</td>
                                <td className="border border-gray-300 p-2">
                                    <select
                                        value={mapping[index] || ''}
                                        onChange={(e) => handleMappingChange(index, e.target.value)}
                                        className="w-full p-1 border border-gray-300 rounded"
                                    >
                                        <option value="">{__('Do not import', 'easycommerce')}</option>
                                        {Object.entries(getDefaultFields()).map(([key, label]) => (
                                            <option key={key} value={key}>{label}</option>
                                        ))}
                                    </select>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <div className="flex justify-end gap-3">
                <button
                    onClick={() => setStep('upload')}
                    className="px-4 py-2 bg-gray-300 text-gray-700 rounded"
                >
                    {__('Back', 'easycommerce')}
                </button>
                <button
                    onClick={mapColumns}
                    disabled={isLoading}
                    className="px-4 py-2 bg-[#9B3FFF] text-white rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    {isLoading ? __('Mapping...', 'easycommerce') : __('Continue', 'easycommerce')}
                </button>
            </div>
        </div>
    );

    const renderImportStep = () => (
        <div className="p-6">
            <h3 className="text-xl font-semibold mb-4">{__('Import Products', 'easycommerce')}</h3>

            {!isLoading ? (
                <div className="mb-4">
                    <p className="text-gray-600">{__('Products are ready to import. Click the button below to start importing.', 'easycommerce')}</p>
                </div>
            ) : (
                <div className="mb-4">
                    <div className="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                        <div
                            className="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                            style={{ width: `${progress}%` }}
                        ></div>
                    </div>
                    <div className="flex justify-between items-center text-sm text-gray-600">
                        <span>{__('Importing...', 'easycommerce')} {progress.toFixed(1)}%</span>
                        {importStatus && (
                            <span>
                                {
                                    // translators: 1: number of products processed, 2: total number of products.
                                    sprintf(__('%1$d of %2$d products', 'easycommerce'), importStatus.processed, importStatus.total)
                                }
                            </span>
                        )}
                    </div>
                    {importStatus && importStatus.errors.length > 0 && (
                        <div className="mt-2 text-sm text-red-600">
                            {sprintf(_n('%d error occurred', '%d errors occurred', importStatus.errors.length, 'easycommerce'), importStatus.errors.length)}
                        </div>
                    )}
                </div>
            )}
            
            <div className="flex justify-end gap-3">
                <button
                    onClick={() => setStep('mapping')}
                    disabled={isLoading}
                    className="px-4 py-2 bg-gray-300 text-gray-700 rounded disabled:opacity-50"
                >
                    {__('Back', 'easycommerce')}
                </button>
                <button
                    onClick={importProducts}
                    disabled={isLoading}
                    className="px-4 py-2 bg-[#9B3FFF] text-white rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    {isLoading ? __('Importing...', 'easycommerce') : __('Import Now', 'easycommerce')}
                </button>
            </div>
        </div>
    );

    return (
        <div className="fixed top-0 left-0 w-screen h-screen flex items-center justify-center bg-[#00000082] backdrop-blur-sm">
            <div className="bg-white relative rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh]">
                <div className="px-6 pt-4">
                    <ol className="flex items-center w-full text-sm font-medium text-center text-gray-500">
                        <li className={`flex items-center ${step === 'upload' ? 'text-[#000000]' : step === 'mapping' || step === 'import' ? 'text-[#000000]' : ''}`}>
                            <span className={`flex items-center justify-center w-8 h-8 rounded-full shrink-0 ${step === 'upload' || step === 'mapping' || step === 'import' ? 'bg-[#9B3FFF] text-white' : 'bg-gray-200'}`}>
                                1
                            </span>
                            <span className="ml-2">{__('Upload CSV', 'easycommerce')}</span>
                        </li>
                        <li className={`flex items-center ml-8 ${step === 'mapping' || step === 'import' ? 'text-[#000000]' : ''}`}>
                            <span className={`flex items-center justify-center w-8 h-8 rounded-full shrink-0 ${step === 'mapping' || step === 'import' ? 'bg-[#9B3FFF] text-white' : 'bg-gray-200'}`}>
                                2
                            </span>
                            <span className="ml-2">{__('Column Mapping', 'easycommerce')}</span>
                        </li>
                        <li className={`flex items-center ml-8 ${step === 'import' ? 'text-[#000000]' : ''}`}>
                            <span className={`flex items-center justify-center w-8 h-8 rounded-full shrink-0 ${step === 'import' ? 'bg-[#9B3FFF] text-white' : 'bg-gray-200'}`}>
                                3
                            </span>
                            <span className="ml-2">{__('Import', 'easycommerce')}</span>
                        </li>
                    </ol>
                </div>

                <div className="overflow-y-auto">
                    {step === 'upload' && renderUploadStep()}
                    {step === 'mapping' && renderMappingStep()}
                    {step === 'import' && renderImportStep()}
                </div>
                <button
                    onClick={onClose}
                    className="group absolute w-[24px] h-[24px] top-[-24px] right-[-19px] bg-white rounded-full hover:bg-[#FF3A52] flex items-center justify-center transition-colors duration-200 z-50"
                >
                    <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fillRule="evenodd" clipRule="evenodd" d="M0.260418 1.10798C0.607642 0.768042 1.17015 0.768042 1.51731 1.10798L5 4.5176L8.48269 1.10798C8.82991 0.768042 9.39241 0.768042 9.73958 1.10798C10.0868 1.44792 10.0868 1.99862 9.73958 2.33851L6.2569 5.74813L9.73958 9.15775C10.0868 9.49769 10.0868 10.0484 9.73958 10.3883C9.39236 10.7282 8.82985 10.7282 8.48269 10.3883L5 6.97866L1.51731 10.3883C1.17009 10.7282 0.607587 10.7282 0.260418 10.3883C-0.0867505 10.0483 -0.086806 9.49764 0.260418 9.15775L3.7431 5.74813L0.260418 2.33851C-0.086806 1.99857 -0.086806 1.44787 0.260418 1.10798Z" fill="#3C3C42" className="transition-colors duration-200 group-hover:fill-white"/>
                    </svg>
                </button>
            </div>
        </div>
    );
};

export default ImportModal;