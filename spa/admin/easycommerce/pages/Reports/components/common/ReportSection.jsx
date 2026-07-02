import React from 'react';
import Container from './Container';

const ReportSection = ({ title, link, ctaLink, ctaLabel, children, className = '' }) => {
	return (
		<div className={`my-6 ${className}`}>
			<Container title={title} link={link} cta_link={ctaLink} cta_label={ctaLabel}>
				{children}
			</Container>
		</div>
	);
};

export default ReportSection;
