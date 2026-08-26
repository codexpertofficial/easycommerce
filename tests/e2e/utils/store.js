// @ts-check
const { expect } = require( '@playwright/test' );

/**
 * Shared helpers for driving the EasyCommerce storefront in e2e tests.
 *
 * Selectors mirror the live storefront (server-rendered PHP + vanilla JS):
 *   - Shop add-to-cart button ......... .easycommerce-add-to-cart-shop[data-id]
 *   - Checkout form ................... #easycommerce-checkout
 *   - Billing field {f} ............... #easycommerce-field-billing_{f}
 *   - COD radio ....................... input[name="easycommerce-payment_method"][value="cash-on-delivery"]
 *   - Terms ........................... #easycommerce-terms
 *   - Place order ..................... .easycommerce-checkout-main-btn
 */

/**
 * Find a purchasable product from the shop archive.
 *
 * Prefers EC_PRODUCT_ID when set; otherwise scrapes the first add-to-cart
 * button on the shop page. Returns the product id plus its single-product URL.
 *
 * @param {import('@playwright/test').Page} page
 * @returns {Promise<{ id: string, url: string }>}
 */
async function discoverProduct( page ) {
	// The shop *page* (EC_SHOP_PATH) renders the block grid with per-product
	// add-to-cart buttons carrying data-id; the plain /products/ CPT archive
	// does not, so discovery targets the shop page.
	const shopPath = process.env.EC_SHOP_PATH || '/shop/';
	await page.goto( shopPath, { waitUntil: 'domcontentloaded' } );

	const firstCard = page.locator( '.easycommerce-add-to-cart-shop[data-id]' ).first();
	await expect(
		firstCard,
		`shop page (${ shopPath }) should list at least one purchasable product`
	).toBeVisible( { timeout: 20000 } );

	// Prefer an explicit id; otherwise pick the highest-priced card so the
	// checkout exercises the real payment flow — a free cart renders no
	// payment methods and takes a different, less representative path.
	let id = process.env.EC_PRODUCT_ID || '';
	if ( ! id ) {
		const cards = await page.$$eval(
			'.easycommerce-add-to-cart-shop[data-id]',
			( els ) =>
				els.map( ( el ) => {
					let card = el;
					for ( let i = 0; i < 6 && card; i++ ) {
						if ( card.querySelector && card.querySelector( '.easycommerce-product-price-wrap' ) ) {
							break;
						}
						card = card.parentElement;
					}
					const priceEl = card && card.querySelector( '.easycommerce-product-price-wrap' );
					const price = parseFloat( ( priceEl?.textContent || '' ).replace( /[^0-9.]/g, '' ) ) || 0;
					return { id: el.getAttribute( 'data-id' ), price };
				} )
		);
		const priced = cards.filter( ( c ) => c.price > 0 ).sort( ( a, b ) => b.price - a.price );
		id = ( priced[ 0 ] || cards[ 0 ] || {} ).id || ( await firstCard.getAttribute( 'data-id' ) );
	}
	expect( id, 'a product id must be resolvable' ).toBeTruthy();

	// Best-effort single-product URL for the product-page a11y scan.
	const link = page.locator( 'a[href*="/products/"]' ).first();
	let url = '';
	if ( await link.count() ) {
		url = ( await link.getAttribute( 'href' ) ) || '';
	}

	return { id, url };
}

/**
 * Add a product to the guest cart and land on the checkout page.
 *
 * Uses the server-side add-to-cart URL param, which seeds the guest cart and
 * 302-redirects straight to the checkout page (see Front/Init.php).
 *
 * @param {import('@playwright/test').Page} page
 * @param {string} productId
 */
async function addToCartAndGotoCheckout( page, productId ) {
	await page.goto( `/?add-to-cart=${ productId }`, { waitUntil: 'domcontentloaded' } );
	await expect(
		page.locator( '#easycommerce-checkout' ),
		'add-to-cart should redirect to the checkout page with a non-empty cart'
	).toBeVisible( { timeout: 20000 } );
}

/**
 * Select the first real (non-placeholder) option of a native <select>, if any.
 *
 * @param {import('@playwright/test').Locator} select
 * @returns {Promise<boolean>} true when a real option was selected
 */
async function selectFirstRealOption( select ) {
	const optionCount = await select.locator( 'option' ).count();
	if ( optionCount <= 1 ) {
		return false;
	}
	await select.selectOption( { index: 1 } );
	return true;
}

/**
 * Fill the required billing fields, walking the country -> state -> city
 * cascade data-agnostically (each level is AJAX-populated from the prior).
 *
 * @param {import('@playwright/test').Page} page
 */
async function fillBilling( page ) {
	const field = ( name ) => page.locator( `#easycommerce-field-billing_${ name }` );

	await field( 'first_name' ).fill( 'Ada' );
	await field( 'last_name' ).fill( 'Lovelace' );
	await field( 'email' ).fill( 'ada.e2e@example.com' );
	await field( 'phone' ).fill( '5551234567' );
	await field( 'address_1' ).fill( '221B Baker Street' );

	// Pin to a country that reliably has states + cities so the required
	// city field can be satisfied. The country -> state -> city selects are
	// each AJAX-populated from the prior; wait for options rather than sleeping.
	const country = field( 'country' );
	await expect( country ).toBeVisible();
	await country.selectOption( process.env.EC_COUNTRY || 'US' );

	// State: wait for /geo/states to populate, then pick the first real one.
	const state = field( 'state' );
	if ( ( await state.count() ) && ( await state.evaluate( ( el ) => el.tagName ) ) === 'SELECT' ) {
		await expect
			.poll( async () => state.locator( 'option' ).count(), {
				timeout: 15000,
				message: 'state options should populate after selecting a country',
			} )
			.toBeGreaterThan( 1 );
		await selectFirstRealOption( state );
	}

	// City is required; it may render as a <select> (AJAX) or a text <input>.
	const city = field( 'city' );
	await expect( city ).toBeVisible();
	if ( ( await city.evaluate( ( el ) => el.tagName ) ) === 'SELECT' ) {
		await expect
			.poll( async () => city.locator( 'option' ).count(), {
				timeout: 15000,
				message: 'city options should populate after selecting a state',
			} )
			.toBeGreaterThan( 1 );
		const picked = await selectFirstRealOption( city );
		// Some locales re-render the select as a text input; refetch and type.
		if ( ! picked && ( await field( 'city' ).evaluate( ( el ) => el.tagName ) ) === 'INPUT' ) {
			await field( 'city' ).fill( 'Springfield' );
		}
	} else {
		await city.fill( 'Springfield' );
	}

	// Postcode is optional but harmless to provide.
	const postcode = field( 'postcode' );
	if ( await postcode.count() ) {
		await postcode.fill( '12345' );
	}
}

/**
 * Ensure the "Same as Billing" toggle is checked so shipping mirrors billing.
 *
 * The checkbox ships with a `checked` attribute but is not reliably checked at
 * runtime; leaving it unchecked forces the (empty) shipping address through
 * validation and the order is rejected with "First name is required". Checking
 * it also re-triggers the shipping-method calculation from the billing address.
 *
 * @param {import('@playwright/test').Page} page
 */
async function ensureSameAsBilling( page ) {
	// Set the property and fire `change` directly: a click-based check() races
	// the summary re-render this very toggle triggers ("did not change its
	// state"). Dispatching change still runs the handler that mirrors the
	// address and recalculates shipping.
	await page.evaluate( () => {
		const c = document.querySelector( '#easycommerce-checkout-same-as-shipping' );
		if ( c && ! c.checked ) {
			c.checked = true;
			c.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}
	} );
}

/**
 * Select a shipping method for physical carts.
 *
 * Filling the billing address triggers /cart/shipping/calculate, which
 * re-renders the (required) shipping-method radios in the order summary.
 * Non-physical carts render no radios, so absence is not an error.
 *
 * @param {import('@playwright/test').Page} page
 */
async function chooseShippingMethod( page ) {
	const method = page.locator( '.easycommerce-shipping-method' ).first();
	try {
		await method.waitFor( { state: 'attached', timeout: 20000 } );
	} catch {
		return; // No shipping methods rendered: cart has no physical items.
	}
	// May already be checked; check() re-fires change so the cart is updated.
	await method.check( { force: true } );
}

/**
 * Choose Cash on Delivery and accept terms, then place the order.
 *
 * @param {import('@playwright/test').Page} page
 * @returns {Promise<void>}
 */
async function placeCodOrder( page ) {
	// COD radio may be pre-checked/hidden when it is the only method; force it.
	const cod = page.locator(
		'input[name="easycommerce-payment_method"][value="cash-on-delivery"]'
	);
	if ( await cod.count() ) {
		await cod.check( { force: true } );
	}

	const terms = page.locator( '#easycommerce-terms' );
	if ( await terms.count() ) {
		await terms.check( { force: true } );
	}

	await page.locator( '.easycommerce-checkout-main-btn' ).click();
}

module.exports = {
	discoverProduct,
	addToCartAndGotoCheckout,
	fillBilling,
	ensureSameAsBilling,
	chooseShippingMethod,
	placeCodOrder,
	selectFirstRealOption,
};
