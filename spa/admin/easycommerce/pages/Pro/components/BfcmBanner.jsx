import React, { useState } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * The Black Friday / Cyber Monday strip on top of the Pro page.
 *
 * Drawn only while the campaign runs (`easycommerce_promo_offer_on()`), and
 * gone for seven days once dismissed. Both facts are read from the localized
 * bootstrap, so the banner is either there on first render or never.
 */
const BfcmBanner = ( { medium = 'propage' } ) => {
	const promo = EASYCOMMERCE.promo || {};
	const [ visible, setVisible ] = useState(
		'1' === String( promo.active ) && '1' !== String( promo.banner_hidden )
	);

	if ( ! visible ) {
		return null;
	}

	const dismiss = () => {
		setVisible( false );
		apiFetch( { path: '/easycommerce/v1/promo/banner/dismiss', method: 'POST' } ).catch( () => {} );
	};

	return (
		<section
			aria-label={ __( 'Black Friday Cyber Monday offer', 'easycommerce' ) }
			className="relative mb-6 flex flex-wrap items-center gap-3.5 overflow-hidden rounded-2xl bg-[#0B0B0F] px-5 py-5 text-white md:flex-nowrap md:gap-6 md:px-7"
		>
			<span
				aria-hidden="true"
				className="pointer-events-none absolute -top-8 right-16 select-none text-[190px] leading-none font-black tracking-[-0.06em] text-[#fa9624] opacity-10"
			>
				70
			</span>

			<div className="relative min-w-0 basis-full pe-8 md:flex-1 md:basis-auto md:pe-0">
				<p className="m-0 text-[32px] leading-none font-black tracking-[-0.03em] md:text-[40px]">
					{ __( 'Flat', 'easycommerce' ) }{ ' ' }
					<em className="text-[#fa9624] not-italic">{ __( '70% OFF', 'easycommerce' ) }</em>
				</p>
				<p className="mt-2.5 mb-0 text-sm leading-snug text-[#BDBDCA]">
					<strong className="font-bold text-white">{ __( 'BFCM 2026', 'easycommerce' ) }</strong>
					{ ' · ' }
					{ __( 'Black Friday + Cyber Monday. Lowest price of the year on EasyCommerce Pro.', 'easycommerce' ) }
				</p>
			</div>

			<a
				href={ `https://easycommerce.dev/pricing?utm_source=inplugin&utm_medium=${ medium }&utm_campaign=BFCM2026` }
				target="_blank"
				rel="noopener noreferrer"
				className="relative inline-flex w-full shrink-0 items-center justify-center rounded-lg bg-[#fa9624] px-6 py-3 text-sm font-extrabold whitespace-nowrap !text-black no-underline transition hover:brightness-110 focus-visible:brightness-110 md:w-auto"
			>
				{ __( 'See BFCM pricing', 'easycommerce' ) } &rarr;
			</a>

			<button
				type="button"
				onClick={ dismiss }
				aria-label={ __( 'Dismiss offer', 'easycommerce' ) }
				className="absolute end-2 top-2 inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent p-0 text-2xl leading-none text-[#BDBDCA] transition-colors hover:bg-white/10 hover:text-white focus-visible:bg-white/10 focus-visible:text-white md:relative md:end-auto md:top-auto md:-me-2"
			>
				&times;
			</button>
		</section>
	);
};

export default BfcmBanner;
