import React, { useState, useEffect, useRef } from "react";
import { toast } from "react-toastify";

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
            toast.error('Please select a CSV file');
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
                    for (const [key, label] of Object.entries(defaultFields)) {
                        const labelLower = label.toLowerCase().replace(/[^a-z0-9]/g, '');
                        if (headerLower === labelLower || headerLower.includes(labelLower) || labelLower.includes(headerLower)) {
                            autoMapping[index] = key;
                            break;
                        }
                    }
                });
                setMapping(autoMapping);
                setStep('mapping');
                toast.success('CSV uploaded successfully');
            } else {
                toast.error(data.data || 'Failed to upload CSV');
            }
        } catch (error) {
            toast.error('Upload failed');
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
                toast.success('Columns mapped successfully');
            } else {
                toast.error(data.data || 'Failed to map columns');
            }
        } catch (error) {
            toast.error('Mapping failed');
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
                toast.error(startData.data || 'Failed to start import');
                setIsLoading(false);
                return;
            }

            const importId = startData.data.import_id;
            setImportId(importId);
            toast.success('Import started in background...');

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
                        toast.success(`Import completed! ${s.imported} products imported`);
                        if (s.errors?.length > 0) {
                            toast.warn(`${s.errors.length} errors occurred`);
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
            toast.error('Import failed to start');
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
            'status': 'Status',
            'title': 'Title',
            'summary': 'Summary',
            'description': 'Description',
            'brands': 'Brands',
            'tags': 'Tags',
            'slug': 'Slug',
            'thumbnail_url': 'Thumbnail Url',
            'categories': 'Categories',
            'attribute_names': 'Attribute Names',
            'attribute_values_name': 'Attribute Values Name',
            'attribute_values_value': 'Attribute Values Value',
            'variation_names': 'Variation Names',
            'variation_types': 'Variation Types',
            'variation_status': 'Variation Status',
            'regular_prices': 'Regular Prices',
            'sale_prices': 'Sale Prices',
            'skus': 'SKUs',
            'stock_quantities': 'Stock Quantities',
            'stock_limits': 'Stock Limits',
            'variation_attribute_names': 'Variation Attribute Names',
            'variation_attribute_values': 'Variation Attribute Values',
            'managed_stocks': 'Managed Stocks',
            'tax_classes': 'Tax Classes',
            'thumbnail_urls': 'Thumbnail URLs',
            'width_values': 'Width Values',
            'width_units': 'Width Units',
            'height_values': 'Height Values',
            'height_units': 'Height Units',
            'weight_values': 'Weight Values',
            'weight_units': 'Weight Units',
            'length_values': 'Length Values',
            'length_units': 'Length Units',
            'downloads': 'Downloads',
            'meta_gallery_urls': 'Meta Gallery URLs',
            'meta_gallery_titles': 'Meta Gallery Titles',
            'meta_templates': 'Meta Templates',
            'show_reviews': 'Show Reviews',
            'review_text_mandatory': 'Review Text Mandatory',
            'hide_from_shop': 'Hide From Shop',
            'noindex': 'Noindex',
            'published_date': 'Published Date',
        };
    };

    const renderUploadStep = () => (
        <div className="p-6">
            <h3 className="text-xl font-semibold mb-4">Upload CSV File</h3>
            <div className="mb-4">
                <span className="text-sm text-gray-600 mb-2 block">
                    Download the {" "}
                    <strong className="text-blue-600">
                        <a className="text-[#9B3FFF]" href={EASYCOMMERCE.sampleCsvUrl} target="_blank" rel="noopener noreferrer">
                            Demo CSV {" "}
                        </a>
                    </strong> file. Add your data, upload it below, and click continue. To learn more, {" "}
                    <a className="text-[#9B3FFF]" href="https://easycommerce.dev/docs/products/csv-importer" target="_blank" rel="noopener noreferrer"><strong>click here</strong></a>.
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
                    Cancel
                </button>
                <button
                    onClick={uploadCsv}
                    disabled={isLoading || !csvFile}
                    className="px-4 py-2 bg-[#9C3EFE] text-white rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    {isLoading ? 'Uploading...' : 'Continue'}
                </button>
            </div>
        </div>
    );

    const renderMappingStep = () => (
        <div className="p-6">
            <h3 className="text-xl font-semibold mb-4">Column Mapping</h3>
            <div className="mb-4 max-h-96 overflow-y-auto">
                <table className="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr className="bg-gray-50">
                            <th className="border border-gray-300 p-2 text-left">Column Name</th>
                            <th className="border border-gray-300 p-2 text-left">Map to Field</th>
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
                                        <option value="">Do not import</option>
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
                    Back
                </button>
                <button
                    onClick={mapColumns}
                    disabled={isLoading}
                    className="px-4 py-2 bg-[#9B3FFF] text-white rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    {isLoading ? 'Mapping...' : 'Continue'}
                </button>
            </div>
        </div>
    );

    const renderImportStep = () => (
        <div className="p-6">
            <h3 className="text-xl font-semibold mb-4">Import Products</h3>
            
            {!isLoading ? (
                <div className="mb-4">
                    <p className="text-gray-600">Products are ready to import. Click the button below to start importing.</p>
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
                        <span>Importing... {progress.toFixed(1)}%</span>
                        {importStatus && (
                            <span>
                                {importStatus.processed} of {importStatus.total} products
                            </span>
                        )}
                    </div>
                    {importStatus && importStatus.errors.length > 0 && (
                        <div className="mt-2 text-sm text-red-600">
                            {importStatus.errors.length} error(s) occurred
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
                    Back
                </button>
                <button
                    onClick={importProducts}
                    disabled={isLoading}
                    className="px-4 py-2 bg-[#9B3FFF] text-white rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    {isLoading ? 'Importing...' : 'Import Now'}
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
                            <span className="ml-2">Upload CSV</span>
                        </li>
                        <li className={`flex items-center ml-8 ${step === 'mapping' || step === 'import' ? 'text-[#000000]' : ''}`}>
                            <span className={`flex items-center justify-center w-8 h-8 rounded-full shrink-0 ${step === 'mapping' || step === 'import' ? 'bg-[#9B3FFF] text-white' : 'bg-gray-200'}`}>
                                2
                            </span>
                            <span className="ml-2">Column Mapping</span>
                        </li>
                        <li className={`flex items-center ml-8 ${step === 'import' ? 'text-[#000000]' : ''}`}>
                            <span className={`flex items-center justify-center w-8 h-8 rounded-full shrink-0 ${step === 'import' ? 'bg-[#9B3FFF] text-white' : 'bg-gray-200'}`}>
                                3
                            </span>
                            <span className="ml-2">Import</span>
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