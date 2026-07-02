import React, { useState, useEffect, useRef } from 'react';

const Header = ({ title, range, setRange, comparison, setComparison }) => {
	const [activeDropdown, setActiveDropdown] = useState(null);
	const dropdownRef = useRef(null);

	useEffect(() => {
		const handleClickOutside = (event) => {
			if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
				setActiveDropdown(null);
			}
		};

		document.addEventListener('mousedown', handleClickOutside);
		return () => document.removeEventListener('mousedown', handleClickOutside);
	}, []);

	const [comparisonOptions, setComparisonOptions] = useState([
		{ label: 'Prev. 30 days', value: 'prev-30' },
		{ label: '30 days last year', value: 'last-year-30' },
	]);

	const rangeOptions = [
		{
			label: 'Last 7 days',
			value: 'last-7',
			comparison: [
				{ label: 'Prev. 7 days', value: 'prev-7' },
				{ label: '7 days last month', value: 'last-month-7' },
				{ label: '7 days last year', value: 'last-year-7' },
			],
		},
		{
			label: 'Last 30 days',
			value: 'last-30',
			comparison: [
				{ label: 'Prev. 30 days', value: 'prev-30' },
				{ label: '30 days last year', value: 'last-year-30' },
			],
		},
		{
			label: 'This week',
			value: 'this-week',
			comparison: [
				{ label: 'Last week', value: 'last-week' },
				{ label: 'Same week last month', value: 'last-month-week' },
				{ label: 'Same week last year', value: 'last-year-week' },
			],
		},
		{
			label: 'This month',
			value: 'this-month',
			comparison: [
				{ label: 'Last month', value: 'last-month' },
				{ label: 'Same month last year', value: 'last-year-month' },
			],
		},
		{
			label: 'This year',
			value: 'this-year',
			comparison: [{ label: 'Last year', value: 'last-year' }],
		},
	];

	const [isScrolled, setIsScrolled] = useState(false);

	useEffect(() => {
		const handleScroll = () => {
			if (window.scrollY > 300) {
				setIsScrolled(true);
			} else {
				setIsScrolled(false);
			}
		};

		window.addEventListener('scroll', handleScroll, { passive: true });

		return () => window.removeEventListener('scroll', handleScroll);
	}, []);

	return (
		<div className="flex items-center justify-between mb-6">
			<div className="product-panel-title">
				<h3>{title}</h3>
			</div>

			<div ref={dropdownRef} className="flex bg-white rounded-md text-[#272435] text-sm ">
				<div className="relative">
					<button
						className="py-2.5 px-5 flex items-center gap-5"
						onClick={() =>
							setActiveDropdown(activeDropdown === 'range' ? null : 'range')
						}
					>
						{range.label}
						<svg
							width="12"
							height="7"
							viewBox="0 0 12 7"
							fill="none"
							xmlns="http://www.w3.org/2000/svg"
						>
							<path
								d="M11 1.53125L6.25 6C6.08333 6.14583 5.90625 6.21875 5.71875 6.21875C5.53125 6.21875 5.36458 6.14583 5.21875 6L0.46875 1.53125C0.15625 1.17708 0.145833 0.822917 0.4375 0.46875C0.770833 0.15625 1.125 0.145833 1.5 0.4375L5.71875 4.4375L9.96875 0.4375C10.3229 0.145833 10.6667 0.145833 11 0.4375C11.2917 0.8125 11.2917 1.17708 11 1.53125Z"
								fill="#272435"
							/>
						</svg>
					</button>

					{activeDropdown === 'range' && (
						<div className="absolute right-0 bg-white top-[calc(100%_+_5px)] w-[160px] flex flex-col border border-[#F0EDFB] shadow-[0px_4px_6px_0px_#0000001A] rounded-lg overflow-hidden">
							{rangeOptions.map((option, index) => (
								<button
									key={index}
									className={`text-left text-sm flex items-center gap-2 text-[#3C3C42] px-3 py-2 hover:bg-[#F0EDFB] duration-300 ${option.value === range.value ? 'bg-[#f0f0f0]' : ''}`}
									onClick={() => {
										setRange({ label: option.label, value: option.value });
										setComparisonOptions(option.comparison);
										setComparison(option.comparison[0]);
										setActiveDropdown(null);
									}}
								>
									{option.label}
								</button>
							))}
						</div>
					)}
				</div>

				<div className="flex items-center px-4 border-x border-ec-table-stock">
					vs.
				</div>

				<div className="relative">
					<button
						className="py-2.5 px-5 flex items-center gap-5"
						onClick={() =>
							setActiveDropdown(
								activeDropdown === 'comparison' ? null : 'comparison',
							)
						}
					>
						{comparison.label}
						<svg
							width="12"
							height="7"
							viewBox="0 0 12 7"
							fill="none"
							xmlns="http://www.w3.org/2000/svg"
						>
							<path
								d="M11 1.53125L6.25 6C6.08333 6.14583 5.90625 6.21875 5.71875 6.21875C5.53125 6.21875 5.36458 6.14583 5.21875 6L0.46875 1.53125C0.15625 1.17708 0.145833 0.822917 0.4375 0.46875C0.770833 0.15625 1.125 0.145833 1.5 0.4375L5.71875 4.4375L9.96875 0.4375C10.3229 0.145833 10.6667 0.145833 11 0.4375C11.2917 0.8125 11.2917 1.17708 11 1.53125Z"
								fill="#272435"
							/>
						</svg>
					</button>

					{activeDropdown === 'comparison' && (
						<div className="absolute z-[999] right-0 bg-white top-[calc(100%_+_5px)] w-[160px] flex flex-col border border-[#F0EDFB] shadow-[0px_4px_6px_0px_#0000001A] rounded-lg overflow-hidden">
							{comparisonOptions.map((option, index) => (
								<button
									key={index}
									className={`text-left text-sm flex items-center gap-2 text-[#3C3C42] px-3 py-2 hover:bg-[#F0EDFB] duration-300 ${option.value === comparison.value ? 'bg-[#f0f0f0]' : ''}`}
									onClick={() => {
										setComparison({ label: option.label, value: option.value });
										setActiveDropdown(null);
									}}
								>
									{option.label}
								</button>
							))}
						</div>
					)}
				</div>
			</div>

			{isScrolled && (
				<div
					className="fixed top-16 right-8 rounded-md border border-ec-primary z-[999]"
					style={{ boxShadow: '0px 4px 4px 0px #00000040' }}
					ref={dropdownRef}
				>
					<div className="flex bg-white rounded-md text-[#272435] text-sm ">
						<div className="relative">
							<button
								className="py-2.5 px-5 flex items-center gap-5"
								onClick={() =>
									setActiveDropdown(activeDropdown === 'range' ? null : 'range')
								}
							>
								{range.label}
								<svg
									width="12"
									height="7"
									viewBox="0 0 12 7"
									fill="none"
									xmlns="http://www.w3.org/2000/svg"
								>
									<path
										d="M11 1.53125L6.25 6C6.08333 6.14583 5.90625 6.21875 5.71875 6.21875C5.53125 6.21875 5.36458 6.14583 5.21875 6L0.46875 1.53125C0.15625 1.17708 0.145833 0.822917 0.4375 0.46875C0.770833 0.15625 1.125 0.145833 1.5 0.4375L5.71875 4.4375L9.96875 0.4375C10.3229 0.145833 10.6667 0.145833 11 0.4375C11.2917 0.8125 11.2917 1.17708 11 1.53125Z"
										fill="#272435"
									/>
								</svg>
							</button>

							{activeDropdown === 'range' && (
								<div className="absolute right-0 bg-white top-[calc(100%_+_5px)] w-[160px] flex flex-col border border-[#F0EDFB] shadow-[0px_4px_6px_0px_#0000001A] rounded-lg overflow-hidden">
									{rangeOptions.map((option, index) => (
										<button
											key={index}
											className={`text-left text-sm flex items-center gap-2 text-[#3C3C42] px-3 py-2 hover:bg-[#F0EDFB] duration-300 ${option.value === range.value ? 'bg-[#f0f0f0]' : ''}`}
											onClick={() => {
												setRange({ label: option.label, value: option.value });
												setComparisonOptions(option.comparison);
												setComparison(option.comparison[0]);
												setActiveDropdown(null);
											}}
										>
											{option.label}
										</button>
									))}
								</div>
							)}
						</div>

						<div className="flex items-center px-4 border-x border-ec-table-stock">
							vs.
						</div>

						<div className="relative">
							<button
								className="py-2.5 px-5 flex items-center gap-5"
								onClick={() =>
									setActiveDropdown(
										activeDropdown === 'comparison' ? null : 'comparison',
									)
								}
							>
								{comparison.label}
								<svg
									width="12"
									height="7"
									viewBox="0 0 12 7"
									fill="none"
									xmlns="http://www.w3.org/2000/svg"
								>
									<path
										d="M11 1.53125L6.25 6C6.08333 6.14583 5.90625 6.21875 5.71875 6.21875C5.53125 6.21875 5.36458 6.14583 5.21875 6L0.46875 1.53125C0.15625 1.17708 0.145833 0.822917 0.4375 0.46875C0.770833 0.15625 1.125 0.145833 1.5 0.4375L5.71875 4.4375L9.96875 0.4375C10.3229 0.145833 10.6667 0.145833 11 0.4375C11.2917 0.8125 11.2917 1.17708 11 1.53125Z"
										fill="#272435"
									/>
								</svg>
							</button>

							{activeDropdown === 'comparison' && (
								<div className="absolute z-[999] right-0 bg-white top-[calc(100%_+_5px)] w-[160px] flex flex-col border border-[#F0EDFB] shadow-[0px_4px_6px_0px_#0000001A] rounded-lg overflow-hidden">
									{comparisonOptions.map((option, index) => (
										<button
											key={index}
											className={`text-left text-sm flex items-center gap-2 text-[#3C3C42] px-3 py-2 hover:bg-[#F0EDFB] duration-300 ${option.value === comparison.value ? 'bg-[#f0f0f0]' : ''}`}
											onClick={() => {
												setComparison({
													label: option.label,
													value: option.value,
												});
												setActiveDropdown(null);
											}}
										>
											{option.label}
										</button>
									))}
								</div>
							)}
						</div>
					</div>
				</div>
			)}
		</div>
	);
};

export default Header;