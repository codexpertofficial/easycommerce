import React from 'react';
import './assets/style.css'

const APIScreen = ({
	onClose,
	switchVariationModalTab,
	switchCreateModalTab,
}) => {
	const logo = `${EASYCOMMERCE.assets}common/img/ec-logo.png`;
	
	return (
		<div className="fixed z-[9999] top-0 left-0 w-screen h-screen flex justify-center items-center bg-[#0000003B] backdrop-blur-sm">
			<div className="relative w-[600px] py-[32px] rounded-xl bg-white">
				<button
					className="absolute top-[-18px] right-[-23px] group w-6 h-6 rounded-full bg-white hover:bg-[#fa4109] transition-colors duration-200 flex items-center justify-center"
					onClick={onClose}
					aria-label="Close"
				>
					<svg
						width="16"
						height="16"
						viewBox="0 0 24 24"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
						className="w-4 h-4"
					>
						<path
							fillRule="evenodd"
							clipRule="evenodd"
							d="M7.26042 7.26042C7.60764 6.91319 8.17015 6.91319 8.51731 7.26042L12 10.7431L15.4827 7.26042C15.8299 6.91319 16.3924 6.91319 16.7396 7.26042C17.0868 7.60764 17.0868 8.17015 16.7396 8.51731L13.2569 12L16.7396 15.4827C17.0868 15.8299 17.0868 16.3924 16.7396 16.7396C16.3924 17.0868 15.8299 17.0868 15.4827 16.7396L12 13.2569L8.51731 16.7396C8.17009 17.0868 7.60759 17.0868 7.26042 16.7396C6.91325 16.3924 6.91319 15.8299 7.26042 15.4827L10.7431 12L7.26042 8.51731C6.91319 8.17009 6.91319 7.60759 7.26042 7.26042Z"
							className="fill-[#3C3C42] group-hover:fill-white transition-colors duration-300"
						/>
					</svg>
				</button>

				<div className="flex flex-col items-center gap-6">
					<img
						src={logo}
						alt="easycommerce"
						className="pointer-events-none w-[92px]"
					/>
					<div className="flex flex-col items-center gap-[30px]">
						<div className="flex flex-col items-center gap-3">
							<div className="flex flex-col items-center gap-2">
								<h2 className="text-ec-title text-[26px] leading-8 font-inter font-medium">
									Two AI Agents, One Free Key
								</h2>
								<p className="text-[#7F7F98] font-inter text-[15px] text-center whitespace-nowrap">
									Connect a free API key - <strong className="text-ec-title">100 AI credits</strong> free, more with{' '}
									<a href="admin.php?page=easycommerce#/get-pro" onClick={onClose} className="text-ec-primary underline hover:underline">Pro</a>.
								</p>
							</div>

							<div className="w-[92%] mx-auto font-inter flex flex-col gap-4">
								<div className="flex items-start gap-4 p-4 rounded-xl bg-[#F7F5FF] border border-[#ECE7FF] text-left">
									<span className="shrink-0 w-11 h-11 rounded-xl bg-white flex items-center justify-center text-2xl shadow-sm">🧑‍💼</span>
									<div>
										<p className="text-ec-title font-semibold text-base leading-6">Store Copilot - For You</p>
										<p className="text-ec-body text-[14px] leading-[22px] mt-1">Chat to run your store: ask about sales, create and edit products, and update orders - in plain English, with your approval.</p>
									</div>
								</div>

								<div className="flex items-start gap-4 p-4 rounded-xl bg-[#F7F5FF] border border-[#ECE7FF] text-left">
									<span className="shrink-0 w-11 h-11 rounded-xl bg-white flex items-center justify-center text-2xl shadow-sm">🛍️</span>
									<div>
										<p className="text-ec-title font-semibold text-base leading-6">Shopping Agent - For Your Shoppers</p>
										<p className="text-ec-body text-[14px] leading-[22px] mt-1">A 24/7 storefront agent that helps customers find the right product, answers their questions, and places orders for them.</p>
									</div>
								</div>

								<p className="text-center text-[15px] text-ec-body mt-1">
									Plus <strong className="text-ec-title font-medium">AI Writer</strong>, <strong className="text-ec-title font-medium">Smart Search</strong>, <strong className="text-ec-title font-medium">Image Editor</strong> &amp; more.
								</p>

								<p className="text-left text-[13px] leading-[20px] text-[#8A8A8A] pt-4 border-t border-[#EFEFEF]">
									Each AI request transmits only the relevant records - the item you're working on, or a shopper's query and its products - to EasyCommerce's AI service. Your bulk customer and order data is never uploaded, sold, or used to train models.{' '}
									<a
										href="https://easycommerce.dev/docs/troubleshooting/use-of-api-key/"
										className="text-ec-primary underline hover:underline"
										target="_blank"
										rel="noopener noreferrer"
									>
										Learn more.
									</a>
								</p>
							</div>
						</div>

						<div className="w-[90%] flex justify-between items-center gap-4">
							<button
								className="w-full h-12 flex flrx-1 justify-center items-center font-medium font-inter text-ec-primary 
                                hover:text-white text-base leading-[26px] bg-white hover:bg-ec-primary rounded-lg border 
                                border-ec-primary transition-all ease-in-out duration-300"
								onClick={switchVariationModalTab}
							>
								I have an API key
							</button>
							<button
								className="w-full h-12 flex flrx-1 justify-center items-center font-medium font-inter text-base 
                                leading-[26px] text-white bg-ec-primary rounded-lg hover:bg-ec-secondary transition-all 
                                ease-in-out duration-300"
								onClick={switchCreateModalTab}
							>
								I want an API key
							</button>
						</div>
					</div>
                    {/*<a
                        className="text-sm text-ec-primary border-b border-ec-primary pb-1"
                        href="admin.php?page=easycommerce-settings&menu=ai"
                        target="_blank"
                    >
                        I want to use my own API key
                    </a>*/}
				</div>
			</div>
		</div>
	);
};

export default APIScreen;
