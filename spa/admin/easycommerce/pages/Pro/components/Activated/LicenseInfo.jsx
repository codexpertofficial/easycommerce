import React, { useState, useEffect } from 'react';

import LicenseInfoSkeleton from './LicenseInfoSkeleton';

const deleteWarningBg = `${EASYCOMMERCE.assets}admin/img/delete-warning-bg.png`;
const deleteIcon = `${EASYCOMMERCE.assets}admin/img/common-delete.png`;

const LicenseInfo = () => {
    const [errorMsg, setErrorMsg] = useState('');
    const [loading, setLoading] = useState(false);
    const [showPopup, setShowPopup] = useState(false);

    const [licenseInfo, setLicenseInfo] = useState(null)
    const [fetching, setFetching] = useState(true);

    useEffect(() => {
        setFetching(true);

        fetch(`${EASYCOMMERCE.rest_base}/pro/license`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                if (data) {
                    setLicenseInfo(data.data.license);
                }
            })
            .catch((error) => {
                setErrorMsg(error.message || 'Failed to fetch license information.');
            })
            .finally(() => {
                setFetching(false);
            });
    }, []);
    
    const maskKey = (key) => {
        if (!key) return '';
        const visible = 6;
        if (key.length <= visible) return key;
        return '*'.repeat(key.length - visible) + ' ' + key.slice(-visible);
    };

    const handleDeactivation = async () => {
        setLoading(true);
        try {
            const response = await fetch(`${EASYCOMMERCE.rest_base}/pro/license`, {
				method: 'DELETE',
				headers: {
					'Content-Type': 'application/json',
					"X-WP-Nonce": EASYCOMMERCE.nonce,
				},
			});

            if (response.ok) {
                setErrorMsg('');
                window.location.reload();
            } else {
                setErrorMsg('Failed to deactivate license. Please try again.');
            }
        } catch (error) {
            setErrorMsg('An error occurred while deactivating the license. Please try again.');
        } finally {
            setLoading(false);
            setShowPopup(false);
        }
    }

	return (
        <div className="border border-ec-table-stock rounded-xl p-8">
            <div className="flex items-center justify-between mb-8">
                <h2 className="text-ec-title text-2xl font-medium">
                    Your active license details
                </h2>

                <button
                    onClick={() => setShowPopup(true)}
                    className='rounded-md px-2.5 py-1.5 text-ec-red bg-transparent border border-ec-red hover:bg-ec-red hover:text-white duration-300'
                >
                    Deactivate
                </button>
            </div>

            {fetching? (
                <LicenseInfoSkeleton />
            ) : (
                <div className="flex flex-col gap-4">
                    <div className="flex items-center gap-3">
                        <div className="bg-[#FD7F510D] rounded-md flex items-center justify-center w-8 h-8">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 3.8C18 5.8954 16.2953 7.6 14.2 7.6C13.7578 7.6 13.4 7.24218 13.4 6.8C13.4 6.35782 13.7578 6 14.2 6C15.4133 6 16.4 5.01328 16.4 3.8C16.4 2.58672 15.4133 1.6 14.2 1.6H3.8C2.58672 1.6 1.6 2.58672 1.6 3.8C1.6 5.01328 2.58672 6 3.8 6C4.24218 6 4.6 6.35782 4.6 6.8C4.6 7.24218 4.24218 7.6 3.8 7.6C1.7046 7.6 0 5.89532 0 3.8C0 1.70468 1.70468 0 3.8 0H14.2C16.2954 0 18 1.70468 18 3.8ZM15.4 10.8V15.2C15.4 15.3453 15.3609 15.4875 15.2859 15.6117L14.0859 17.6117C13.9414 17.8523 13.6813 18 13.4 18H5.6C5.30546 18 5.03516 17.8383 4.89532 17.5797L3.49532 14.9797C3.43282 14.8633 3.40001 14.7328 3.40001 14.6V10.8C3.40001 9.47656 4.47657 8.4 5.80001 8.4C6.08047 8.4 6.35001 8.44844 6.60001 8.5375V5.1999C6.60001 3.87646 7.67657 2.7999 9.00001 2.7999C10.3234 2.7999 11.4 3.87646 11.4 5.1999V8.6585L14.8968 10.0569C15.2007 10.1788 15.4 10.4727 15.4 10.8ZM13.8 11.3414L10.3032 9.94296C9.9993 9.82108 9.80008 9.52734 9.80008 9.2V5.2C9.80008 4.7586 9.44148 4.4 9.00008 4.4C8.55868 4.4 8.20008 4.7586 8.20008 5.2V13.6C8.20008 14.0422 7.84226 14.4 7.40008 14.4C6.9579 14.4 6.60008 14.0422 6.60008 13.6V10.8C6.60008 10.3586 6.24148 10 5.80008 10C5.35868 10 5.00008 10.3586 5.00008 10.8V14.3984L6.0782 16.4H12.9478L13.8009 14.9781V11.3413L13.8 11.3414Z" fill="#FD7F51"/>
                            </svg>
                        </div>

                        <h3 className='text-[#7F7F98] text-base'>
                            Email: <span className='text-ec-body'>{licenseInfo.email}</span>
                        </h3>
                    </div>
                    
                    <div className="flex items-center gap-3">
                        <div className="bg-[#FF27970D] rounded-md flex items-center justify-center w-8 h-8">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12.1448 2.43979C11.559 1.85395 10.6094 1.85395 10.0237 2.43979C9.43789 3.02563 9.43789 3.97534 10.0237 4.56111L11.438 5.9756C12.0238 6.56144 12.9735 6.56144 13.5592 5.9756C14.145 5.38976 14.145 4.44005 13.5592 3.85428L12.1448 2.43979ZM12.8517 5.26817C12.6564 5.46345 12.3401 5.46345 12.1448 5.26817L10.7311 3.85433C10.5358 3.65904 10.5358 3.34269 10.7311 3.14741C10.9264 2.95212 11.2427 2.95212 11.438 3.14741L12.8517 4.56125C13.0476 4.75653 13.0469 5.07288 12.8517 5.26817ZM14.9728 2.43979L13.5591 1.02595C12.1929 -0.341009 9.97799 -0.342308 8.6105 1.024C7.95311 1.68079 7.58405 2.57193 7.58472 3.50077V6.29398L0.43934 13.4393C-0.146447 14.0252 -0.146447 14.9749 0.43934 15.5606C1.02513 16.1464 1.97476 16.1465 2.56048 15.5606L3.62076 14.4996L4.68105 15.56C5.26618 16.1458 6.21581 16.1465 6.80168 15.5613C7.25209 15.1115 7.36924 14.4267 7.09523 13.8532L7.74676 13.2016C8.49398 13.559 9.38959 13.244 9.74693 12.4967C10.0216 11.9226 9.90444 11.2371 9.45404 10.7873L8.39375 9.72691L9.70657 8.41397H12.4995C14.4321 8.41462 15.9993 6.84848 16 4.91587C15.9993 3.98764 15.6302 3.09652 14.9728 2.43979ZM8.74653 11.495C8.9418 11.6903 8.9418 12.0066 8.74653 12.2019C8.55126 12.3972 8.23494 12.3972 8.03967 12.2019C7.84441 12.0066 7.52808 12.0066 7.33282 12.2019L6.09549 13.4393C5.90022 13.6346 5.90022 13.951 6.09549 14.1463C6.29076 14.3415 6.29076 14.6579 6.09549 14.8532C5.90022 15.0485 5.5839 15.0485 5.38864 14.8532L4.32835 13.7928L7.68684 10.4333L8.74653 11.495ZM14.266 6.6826C13.798 7.15323 13.1614 7.41685 12.4983 7.4149L9.49834 7.41425C9.36556 7.41425 9.23864 7.46698 9.14491 7.56071L3.97437 12.733C3.77911 12.9283 3.46213 12.9283 3.26687 12.733C3.0716 12.5378 3.0716 12.2208 3.26687 12.0255L8.43874 6.85381C8.53247 6.76008 8.58519 6.63315 8.58519 6.501V3.50149C8.58454 2.12084 9.70274 1.00189 11.0832 1.00122C11.7465 1.00122 12.383 1.2642 12.8516 1.73417L14.266 3.14866C15.2293 4.12312 15.2215 5.69976 14.266 6.6826Z" fill="#FF2797"/>
                            </svg>
                        </div>

                        <h3 className='text-[#7F7F98] text-base'>
                            License Key: <span className='text-ec-body'>{maskKey(licenseInfo.key)}</span>
                        </h3>
                    </div>
                    
                    <div className="flex items-center gap-3">
                        <div className="bg-[#FD51620D] rounded-md flex items-center justify-center w-8 h-8">
                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14.1368 1.03789H12.9021V0.644211C12.9021 0.286316 12.6158 0 12.2579 0C11.9 0 11.6137 0.286316 11.6137 0.644211V1.05579H8.75053V0.644211C8.75053 0.286316 8.46421 0 8.10632 0C7.74842 0 7.4621 0.286316 7.4621 0.644211V1.05579H4.58105V0.644211C4.58105 0.286316 4.29474 0 3.93684 0C3.57895 0 3.29263 0.286316 3.29263 0.644211V1.05579H2.05789C0.930526 1.05579 0 1.98632 0 3.11368V14.9421C0 16.0695 0.930526 17 2.05789 17H14.1189C15.2463 17 16.1768 16.0695 16.1768 14.9421V3.09579C16.1947 1.96842 15.2821 1.03789 14.1368 1.03789ZM2.07579 2.32632H3.31053V2.73789C3.31053 3.09579 3.59684 3.38211 3.95474 3.38211C4.31263 3.38211 4.59895 3.09579 4.59895 2.73789V2.32632H7.48V2.73789C7.48 3.09579 7.76631 3.38211 8.12421 3.38211C8.4821 3.38211 8.76842 3.09579 8.76842 2.73789V2.32632H11.6495V2.73789C11.6495 3.09579 11.9358 3.38211 12.2937 3.38211C12.6516 3.38211 12.9379 3.09579 12.9379 2.73789V2.32632H14.1726C14.6021 2.32632 14.96 2.68421 14.96 3.11368V4.33053H1.28842V3.11368C1.28842 2.66632 1.64632 2.32632 2.07579 2.32632ZM14.1368 15.7295H2.07579C1.64632 15.7295 1.28842 15.3716 1.28842 14.9421V5.60105H14.9063V14.9421C14.9242 15.3716 14.5663 15.7295 14.1368 15.7295Z" fill="#FD5162"/>
                                <path d="M4.88048 6.88867H3.32363C3.03732 6.88867 2.80469 7.1213 2.80469 7.40762V7.96236C2.80469 8.24867 3.03732 8.4813 3.32363 8.4813H4.88048C5.16679 8.4813 5.39942 8.24867 5.39942 7.96236V7.40762C5.39942 7.1213 5.16679 6.88867 4.88048 6.88867Z" fill="#FD5162"/>
                                <path d="M12.8626 6.88867H11.3236C11.0373 6.88867 10.8047 7.1213 10.8047 7.40762V7.96236C10.8047 8.24867 11.0373 8.4813 11.3236 8.4813H12.8805C13.1668 8.4813 13.3994 8.24867 13.3994 7.96236V7.40762C13.3994 7.1213 13.1668 6.88867 12.8626 6.88867Z" fill="#FD5162"/>
                                <path d="M8.86485 6.88867H7.30801C7.02169 6.88867 6.78906 7.1213 6.78906 7.40762V7.96236C6.78906 8.24867 7.02169 8.4813 7.30801 8.4813H8.86485C9.15117 8.4813 9.3838 8.24867 9.3838 7.96236V7.40762C9.3838 7.1213 9.15117 6.88867 8.86485 6.88867Z" fill="#FD5162"/>
                                <path d="M4.88048 9.89648H3.32363C3.03732 9.89648 2.80469 10.1291 2.80469 10.4154V10.9702C2.80469 11.2565 3.03732 11.4891 3.32363 11.4891H4.88048C5.16679 11.4891 5.39942 11.2565 5.39942 10.9702V10.4154C5.39942 10.1291 5.16679 9.89648 4.88048 9.89648Z" fill="#FD5162"/>
                                <path d="M12.8626 9.89648H11.3236C11.0373 9.89648 10.8047 10.1291 10.8047 10.4154V10.9702C10.8047 11.2565 11.0373 11.4891 11.3236 11.4891H12.8805C13.1668 11.4891 13.3994 11.2565 13.3994 10.9702V10.4154C13.3994 10.1291 13.1668 9.89648 12.8626 9.89648Z" fill="#FD5162"/>
                                <path d="M8.86485 9.89648H7.30801C7.02169 9.89648 6.78906 10.1291 6.78906 10.4154V10.9702C6.78906 11.2565 7.02169 11.4891 7.30801 11.4891H8.86485C9.15117 11.4891 9.3838 11.2565 9.3838 10.9702V10.4154C9.3838 10.1291 9.15117 9.89648 8.86485 9.89648Z" fill="#FD5162"/>
                                <path d="M4.88048 12.9199H3.32363C3.03732 12.9199 2.80469 13.1526 2.80469 13.4389V13.9936C2.80469 14.2799 3.03732 14.5126 3.32363 14.5126H4.88048C5.16679 14.5126 5.39942 14.2799 5.39942 13.9936V13.4389C5.39942 13.1526 5.16679 12.9199 4.88048 12.9199Z" fill="#FD5162"/>
                                <path d="M12.8626 12.9199H11.3236C11.0373 12.9199 10.8047 13.1526 10.8047 13.4389V13.9936C10.8047 14.2799 11.0373 14.5126 11.3236 14.5126H12.8805C13.1668 14.5126 13.3994 14.2799 13.3994 13.9936V13.4389C13.3994 13.1526 13.1668 12.9199 12.8626 12.9199Z" fill="#FD5162"/>
                                <path d="M8.86485 12.9199H7.30801C7.02169 12.9199 6.78906 13.1526 6.78906 13.4389V13.9936C6.78906 14.2799 7.02169 14.5126 7.30801 14.5126H8.86485C9.15117 14.5126 9.3838 14.2799 9.3838 13.9936V13.4389C9.3838 13.1526 9.15117 12.9199 8.86485 12.9199Z" fill="#FD5162"/>
                            </svg>
                        </div>

                        <h3 className='text-[#7F7F98] text-base'>
                            Expiry Date: <span className='text-ec-body'>{licenseInfo.expiry}</span>
                        </h3>
                    </div>
                </div>
            )}

            {errorMsg && <span className='text-ec-red mt-5 block'>{errorMsg}</span>}

            {showPopup && <div className="fixed top-0 left-0 w-screen h-screen flex items-center justify-center bg-[#00000082] backdrop-blur-sm z-[999999]">
                <div className="relative w-[451px] flex flex-col justify-center items-center bg-white rounded-[22px] pb-[40px]">
                    <div
                        className="w-[451px] pt-[30px] pb-[30px] flex flex-col justify-center items-center mb-[24px] rounded-t-[22px] bg-cover bg-center bg-no-repeat"
                        style={{ backgroundImage: `url(${deleteWarningBg})` }}
                    >
                        <div
                            className="w-[120px] h-[120px] bg-white flex justify-center items-center rounded-xl shadow-[0px_18px_22.2px_0px_#DBD3FF"
                        >
                            <img src={deleteIcon} alt="delete-attribute" className="w-[80px] h-[80px]" />
                        </div>
                        <button 
                            onClick={() => setShowPopup(false)} 
                            className="group absolute w-[24px] h-[24px] top-[-15px] left-[446px] bg-white rounded-full hover:bg-[#FF3A52] flex items-center justify-center transition-colors duration-200"
                        >
                            <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.260418 1.10798C0.607642 0.768042 1.17015 0.768042 1.51731 1.10798L5 4.5176L8.48269 1.10798C8.82991 0.768042 9.39241 0.768042 9.73958 1.10798C10.0868 1.44792 10.0868 1.99862 9.73958 2.33851L6.2569 5.74813L9.73958 9.15775C10.0868 9.49769 10.0868 10.0484 9.73958 10.3883C9.39236 10.7282 8.82985 10.7282 8.48269 10.3883L5 6.97866L1.51731 10.3883C1.17009 10.7282 0.607587 10.7282 0.260418 10.3883C-0.0867505 10.0483 -0.086806 9.49764 0.260418 9.15775L3.7431 5.74813L0.260418 2.33851C-0.086806 1.99857 -0.086806 1.44787 0.260418 1.10798Z" fill="#3C3C42" className="transition-colors duration-200 group-hover:fill-white"/>
                            </svg>
                            
                        </button>
                    </div>

                    <div className="flex flex-col justify-center items-center mb-6">
                        <h3 className="font-inter font-medium text-xl text-ec-title mb-2">
                            Are you sure?
                        </h3>
                        <p className="w-9/12 mx-auto text-center font-inter font-normal text-base text-ec-body">
                            Deactivating the license will remove all associated benefits and features from your account.
                        </p>
                    </div>

                    <div className="flex justify-between items-center gap-[14px]">
                        <button
                            className="w-[181px] h-[45px] font-inter font-normal text-base border bg-white text-ec-title border-ec-title rounded-lg px-10 py-[10px]"
                            onClick={() => setShowPopup(false)}
                        >
                            No, Return
                        </button>
                        <button
                            className="w-[181px] h-[45px] flex items-center justify-center font-inter font-normal text-base rounded-lg border bg-ec-red border-ec-red hover:bg-[#FF3A52CC] hover:border-[#FF3A52CC] text-white transition"
                            onClick={handleDeactivation}
                            type="button"
                        >
                            {loading ? (
                                <img src={`${EASYCOMMERCE.assets}/admin/img/loading.gif`} alt="loading" className='h-8 w-auto' />
                            ) : (
                                'Deactivate'
                            )}
                        </button>
                    </div>
                </div>
            </div>}
        </div>
    );
};

export default LicenseInfo;
