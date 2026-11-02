/**
 * The pricing URL for an in-plugin upgrade link.
 *
 * Tagged for the BFCM 2026 campaign while it runs (`EASYCOMMERCE.promo.active`,
 * from `easycommerce_promo_offer_on()`), and back to the regular `BFCM` tag
 * once the campaign is over.
 *
 * @param {string} medium       Where the click came from, e.g. `propage`.
 * @param {string} legacyMedium Medium to use outside the campaign; defaults to `medium`.
 * @return {string}
 */
export const pricingUrl = ( medium, legacyMedium = medium ) => {
	const active = '1' === String( window.EASYCOMMERCE?.promo?.active );

	return active
		? `https://easycommerce.dev/pricing?utm_source=inplugin&utm_medium=${ medium }&utm_campaign=BFCM2026`
		: `https://easycommerce.dev/pricing?utm_source=inplugin&utm_medium=${ legacyMedium }&utm_campaign=BFCM`;
};
