const ImagePreviewAISkeleton = () => {
	return (
		<div className="border border-solid border-ec-table-stock rounded-lg flex flex-col items-center justify-center gap-3 h-full relative overflow-hidden bg-gray-50">
			{/* AI Painting blobs */}
			<div className="absolute inset-0">
				<div className="absolute w-[220%] h-[220%] bg-gradient-to-r from-purple-300 via-pink-300 to-yellow-300 opacity-20 rounded-full animate-[blob1_12s_ease-in-out_infinite] mix-blend-multiply"></div>
				<div className="absolute w-[180%] h-[180%] bg-gradient-to-r from-pink-200 via-blue-200 to-purple-200 opacity-30 rounded-full animate-[blob2_15s_ease-in-out_infinite] mix-blend-multiply"></div>
				<div className="absolute w-[200%] h-[200%] bg-gradient-to-r from-yellow-200 via-green-200 to-teal-200 opacity-20 rounded-full animate-[blob3_18s_ease-in-out_infinite] mix-blend-multiply"></div>

				{/* Shimmer streak overlay */}
				<div className="absolute inset-0 bg-gradient-to-r from-white/10 via-white/30 to-white/10 opacity-40 animate-[shimmer_2s_linear_infinite]"></div>
			</div>

			{/* Text */}
			<div className="relative z-10 text-center text-sm text-gray-500">
				Generating image
			</div>

			<style jsx>{`
				@keyframes blob1 {
					0%,
					100% {
						transform: translate(-25%, -25%) scale(1);
					}
					50% {
						transform: translate(25%, 25%) scale(1.2);
					}
				}
				@keyframes blob2 {
					0%,
					100% {
						transform: translate(20%, -20%) scale(1);
					}
					50% {
						transform: translate(-20%, 20%) scale(1.15);
					}
				}
				@keyframes blob3 {
					0%,
					100% {
						transform: translate(-15%, 20%) scale(1);
					}
					50% {
						transform: translate(15%, -20%) scale(1.1);
					}
				}
				@keyframes shimmer {
					0% {
						background-position: -200% 0;
					}
					100% {
						background-position: 200% 0;
					}
				}
				.animate-[shimmer_2s_linear_infinite] {
					background-size: 200% 100%;
					animation: shimmer 2s linear infinite;
				}
				.animate-[blob1_12s_ease-in-out_infinite] {
					animation: blob1 12s linear infinite;
				}
				.animate-[blob2_15s_ease-in-out_infinite] {
					animation: blob2 15s linear infinite;
				}
				.animate-[blob3_18s_ease-in-out_infinite] {
					animation: blob3 18s linear infinite;
				}
			`}</style>
		</div>
	);
};

export default ImagePreviewAISkeleton;
