import React from 'react';

const Reviews = () => {
	const reviews = [
        {
            title: 'Un plugin e-commerce WordPress moderne et rapide',
            quote: 'Je teste EasyCommerce depuis plusieurs semaines et c’est une vraie bouffée d’air frais. Installation simple, interface claire, tout fonctionne sans extensions inutiles.',
            image: 'reviewer-1.png',
            name: 'Michaël KIHL',
            role: 'Freelance Web Developer',
            link: 'https://wordpress.org/support/topic/easycommerce-un-plugin-e-commerce-wordpress-moderne-et-rapide/',
            profileLink: 'https://wordpress.org/support/users/michaelkihl/'
        },
		{
			title: 'Customer support is also very excellent',
			quote: 'It’s a really Good plugin. They are new in marketplace but providing some wonderful features that really helpful. Their customer support was also very excellent.',
			image: 'reviewer-2.png',
			name: 'Faysal Khan',
			role: 'Independent Contractor @Apple',
			link: 'https://wordpress.org/support/topic/wonderful-plagin/',
			profileLink: 'https://wordpress.org/support/users/iamfaysalofficial/'
		},
		{
			title: 'Great plugin to replace woocommerce',
			quote: 'I have experienced it very fast and suitable for my wordpress fast loading preference. it is much lighter than woocommerce.',
			image: 'reviewer-3.png',
			name: 'Laonama',
			role: 'VPS management & Support Tech at VNC',
			link: 'https://wordpress.org/support/topic/great-plugin-to-replace-woocommerce/',
			profileLink: 'https://wordpress.org/support/users/laonama/'
		}
	];

	return (
        <div className='pt-[120px] pb-[0px]'>
            <h2 className='text-[#1A0180] text-4xl text-center mb-[60px] font-medium'>Trusted by Businesses Like Yours</h2>

            <div className="grid grid-cols-3 gap-8">
				{reviews.map((review, index) => (
					<div key={index} className="border border-[#2724351A] rounded-xl p-8 h-[380px] flex flex-col justify-between">
						<div>
							{/* Stars */}
							<div className="flex gap-3 items-center mb-3">
								<svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fillRule="evenodd" clipRule="evenodd" d="M9.9574 16.1153L4.81372 18.8195C3.97095 19.2626 3.41808 18.8619 3.57918 17.9226L4.56154 12.195L0.400212 8.13871C-0.281604 7.4741 -0.0713498 6.82446 0.871763 6.68742L6.62257 5.85178L9.19442 0.640659C9.6158 -0.213161 10.2986 -0.213945 10.7204 0.640659L13.2922 5.85178L19.043 6.68742C19.9853 6.82434 20.197 7.47349 19.5146 8.13871L15.3533 12.195L16.3356 17.9226C16.4966 18.861 15.9446 19.263 15.1011 18.8195L9.9574 16.1153Z" fill="#F99D1D"/>
								</svg>
								<svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fillRule="evenodd" clipRule="evenodd" d="M9.9574 16.1153L4.81372 18.8195C3.97095 19.2626 3.41808 18.8619 3.57918 17.9226L4.56154 12.195L0.400212 8.13871C-0.281604 7.4741 -0.0713498 6.82446 0.871763 6.68742L6.62257 5.85178L9.19442 0.640659C9.6158 -0.213161 10.2986 -0.213945 10.7204 0.640659L13.2922 5.85178L19.043 6.68742C19.9853 6.82434 20.197 7.47349 19.5146 8.13871L15.3533 12.195L16.3356 17.9226C16.4966 18.861 15.9446 19.263 15.1011 18.8195L9.9574 16.1153Z" fill="#F99D1D"/>
								</svg>
								<svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fillRule="evenodd" clipRule="evenodd" d="M9.9574 16.1153L4.81372 18.8195C3.97095 19.2626 3.41808 18.8619 3.57918 17.9226L4.56154 12.195L0.400212 8.13871C-0.281604 7.4741 -0.0713498 6.82446 0.871763 6.68742L6.62257 5.85178L9.19442 0.640659C9.6158 -0.213161 10.2986 -0.213945 10.7204 0.640659L13.2922 5.85178L19.043 6.68742C19.9853 6.82434 20.197 7.47349 19.5146 8.13871L15.3533 12.195L16.3356 17.9226C16.4966 18.861 15.9446 19.263 15.1011 18.8195L9.9574 16.1153Z" fill="#F99D1D"/>
								</svg>
								<svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fillRule="evenodd" clipRule="evenodd" d="M9.9574 16.1153L4.81372 18.8195C3.97095 19.2626 3.41808 18.8619 3.57918 17.9226L4.56154 12.195L0.400212 8.13871C-0.281604 7.4741 -0.0713498 6.82446 0.871763 6.68742L6.62257 5.85178L9.19442 0.640659C9.6158 -0.213161 10.2986 -0.213945 10.7204 0.640659L13.2922 5.85178L19.043 6.68742C19.9853 6.82434 20.197 7.47349 19.5146 8.13871L15.3533 12.195L16.3356 17.9226C16.4966 18.861 15.9446 19.263 15.1011 18.8195L9.9574 16.1153Z" fill="#F99D1D"/>
								</svg>
								<svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fillRule="evenodd" clipRule="evenodd" d="M9.9574 16.1153L4.81372 18.8195C3.97095 19.2626 3.41808 18.8619 3.57918 17.9226L4.56154 12.195L0.400212 8.13871C-0.281604 7.4741 -0.0713498 6.82446 0.871763 6.68742L6.62257 5.85178L9.19442 0.640659C9.6158 -0.213161 10.2986 -0.213945 10.7204 0.640659L13.2922 5.85178L19.043 6.68742C19.9853 6.82434 20.197 7.47349 19.5146 8.13871L15.3533 12.195L16.3356 17.9226C16.4966 18.861 15.9446 19.263 15.1011 18.8195L9.9574 16.1153Z" fill="#F99D1D"/>
								</svg>
							</div>

							{/* title */}
							<h3 className='text-[#1A0180] text-xl mb-5 font-medium'>
								<a href={review.link} target="_blank" rel="noopener noreferrer" className='hover:underline'>
									{review.title}
								</a>
							</h3>

							{/* Quote */}
							<div className="">
								<svg width="25" height="22" viewBox="0 0 25 22" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M7.79852 0C2.48889 4.99674 0 10.3159 0 15.0708C0 18.9393 2.98667 21.76 5.97333 21.76C8.62815 21.76 10.7852 19.6646 10.7852 17.0856C10.7852 13.7813 8.2963 12.0889 4.64593 12.0889C4.64593 8.13985 5.89037 5.80266 9.78963 1.93422L7.79852 0ZM21.6533 0C16.3437 4.99674 13.8548 10.3159 13.8548 15.0708C13.8548 18.9393 16.8415 21.76 19.8282 21.76C22.483 21.76 24.64 19.6646 24.64 17.0856C24.64 13.7813 22.1511 12.0889 18.5007 12.0889C18.5007 8.13985 19.7452 5.80266 23.6444 1.93422L21.6533 0Z" fill="#BFC0C5"/>
								</svg>

								<p className='text-[#272435] text-base mt-4'>{review.quote}</p>
							</div>
						</div>

						<div className="border-t border-[#EBEBEB] pt-5">
							<a href={review.profileLink} target="_blank" rel="noopener noreferrer" className="flex gap-3 items-center hover:underline">
								<img src={`${EASYCOMMERCE.assets}admin/img/reviewers/${review.image}`} className='rounded-full w-11 h-11 object-cover border border-solid border-[#0115460D]' alt="user" />

								<div className="">
									<h4 className='text-[#272435] text-base font-medium'>{review.name}</h4>
									<p className='text-[#737791] text-sm'>{review.role}</p>
								</div>
							</a>
						</div>
					</div>
				))}
            </div>
        </div>
    );
};

export default Reviews;