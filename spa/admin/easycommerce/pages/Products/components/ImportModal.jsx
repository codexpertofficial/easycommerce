import React, { useState, useRef } from 'react';
import { toast } from 'react-toastify';
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

const ImportModal = ({ isOpen, onClose }) => {
    const [step, setStep] = useState('upload');
    const [headers, setHeaders] = useState([]);
    const [mapping, setMapping] = useState({});
    const [isLoading, setIsLoading] = useState(false);
    const fileInputRef = useRef(null);

    const fields = window.get_defaults ? window.get_defaults() : {};

    const handleFileUpload = async (e) => {
        e.preventDefault();
        const file = fileInputRef.current.files[0];
        if (!file) {
            toast.error(__( 'Please select a CSV file.', 'easycommerce' ));
            return;
        }

        const formData = new FormData();
        formData.append('csv_file', file);
        formData.append('action', 'easycommerce_upload_csv');
        formData.append('nonce', EASYCOMMERCE.nonce);

        setIsLoading(true);
        try {
            const response = await fetch(EASYCOMMERCE.ajax_url, {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();

            if (data.success) {
                setHeaders(data.data.headers);
                setStep('mapping');
                toast.success(__( 'CSV uploaded successfully.', 'easycommerce' ));
            } else {
                toast.error(data.data || __( 'Upload failed.', 'easycommerce' ));
            }
        } catch (error) {
            toast.error(__( 'Upload failed.', 'easycommerce' ));
        }
        setIsLoading(false);
    };

    const handleMapping = async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('action', 'easycommerce_mapping_csv');
        formData.append('nonce', EASYCOMMERCE.nonce);

        setIsLoading(true);
        try {
            const response = await fetch(EASYCOMMERCE.ajax_url, {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();

            if (data.success) {
                setStep('done');
                toast.success(__( 'Mapping saved.', 'easycommerce' ));
            } else {
                toast.error(data.data || __( 'Mapping failed.', 'easycommerce' ));
            }
        } catch (error) {
            toast.error(__( 'Mapping failed.', 'easycommerce' ));
        }
        setIsLoading(false);
    };

    const handleImport = async () => {
        const formData = new FormData();
        formData.append('action', 'easycommerce_import_csv');
        formData.append('nonce', EASYCOMMERCE.nonce);

        setIsLoading(true);
        try {
            const response = await fetch(EASYCOMMERCE.ajax_url, {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();

            if (data.success) {
                toast.success(__( 'Products imported successfully.', 'easycommerce' ));
                onClose();
                window.location.reload(); // Refresh to show new products
            } else {
                toast.error(data.data || __( 'Import failed.', 'easycommerce' ));
            }
        } catch (error) {
            toast.error(__( 'Import failed.', 'easycommerce' ));
        }
        setIsLoading(false);
    };

    const resetModal = () => {
        setStep('upload');
        setHeaders([]);
        setMapping({});
        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    const handleClose = () => {
        resetModal();
        onClose();
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div className="flex justify-between items-center mb-4">
                    <h2 className="text-xl font-bold">{ __( 'Import Products', 'easycommerce' ) }</h2>
                    <button onClick={handleClose} className="text-gray-500 hover:text-gray-700">&times;</button>
                </div>

                {/* Progress Bar */}
                <ol className="ec-progress-steps mb-6">
                    <li className={step === 'upload' || step === 'mapping' || step === 'done' ? 'active' : ''}>{ __( 'Upload CSV', 'easycommerce' ) }</li>
                    <li className={step === 'mapping' || step === 'done' ? 'active' : ''}>{ __( 'Column Mapping', 'easycommerce' ) }</li>
                    <li className={step === 'done' ? 'active' : ''}>{ __( 'Import', 'easycommerce' ) }</li>
                </ol>

                {step === 'upload' && (
                    <div className="ec_importer_upload">
                        <p className="mb-4">
                            {createInterpolateElement(
                                __( '<a><b>Click Here</b></a> to download the <c>Demo CSV</c> file.', 'easycommerce' ),
                                {
                                    a: (
                                        <a
                                            href="https://cdn.easycommerce.dev/images/samples/products.csv"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        />
                                    ),
                                    b: <strong />,
                                    c: <strong />,
                                }
                            )}
                            <br />
                            { __( 'Add your data and upload it below to continue.', 'easycommerce' ) }
                        </p>
                        <form onSubmit={handleFileUpload}>
                            <input type="file" ref={fileInputRef} accept=".csv" required className="mb-4" />
                            <button type="submit" disabled={isLoading} className="bg-blue-500 text-white px-4 py-2 rounded">
                                {isLoading ? __( 'Uploading...', 'easycommerce' ) : __( 'Continue', 'easycommerce' )}
                            </button>
                        </form>
                    </div>
                )}

                {step === 'mapping' && (
                    <form onSubmit={handleMapping} className="ec_importer_mapping">
                        <table className="ec-importer-mapping-table w-full border-collapse">
                            <thead>
                                <tr>
                                    <th className="border p-2">{ __( 'Column Name', 'easycommerce' ) }</th>
                                    <th className="border p-2">{ __( 'Map to Field', 'easycommerce' ) }</th>
                                </tr>
                            </thead>
                            <tbody>
                                {headers.map((name, index) => (
                                    <tr key={index}>
                                        <td className="border p-2">{name}</td>
                                        <td className="border p-2">
                                            <select name={`map_to[${index}]`} defaultValue={name.replace(/\s+/g, '_')}>
                                                <option value="">{ __( 'Do not import', 'easycommerce' ) }</option>
                                                {Object.entries(fields).map(([key, label]) => (
                                                    <option key={key} value={key}>{label}</option>
                                                ))}
                                            </select>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        <button type="submit" disabled={isLoading} className="mt-4 bg-blue-500 text-white px-4 py-2 rounded">
                            {isLoading ? __( 'Saving...', 'easycommerce' ) : __( 'Continue', 'easycommerce' )}
                        </button>
                    </form>
                )}

                {step === 'done' && (
                    <div className="ec_importer_done text-center">
                        <h2>{ __( 'Products Mapped Successfully.', 'easycommerce' ) }</h2>
                        <h3>{ __( 'Products are ready to import.', 'easycommerce' ) }</h3>
                        <button onClick={handleImport} disabled={isLoading} className="mt-4 bg-green-500 text-white px-4 py-2 rounded">
                            {isLoading ? __( 'Importing...', 'easycommerce' ) : __( 'Import Now', 'easycommerce' )}
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
};

export default ImportModal;