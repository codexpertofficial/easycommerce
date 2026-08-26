// @ts-check
const { test, expect } = require( '@playwright/test' );
const {
	discoverProduct,
	addToCartAndGotoCheckout,
	fillBilling,
	ensureSameAsBilling,
	chooseShippingMethod,
	placeCodOrder,
} = require( './utils/store' );

/**
 * End-to-end mobile checkout regression (issue #3149).
 *
 * Runs on the Pixel 5 viewport (see playwright.config.js) and drives a full
 * guest purchase against a live store: shop -> add to cart -> billing info ->
 * shipping -> Cash on Delivery -> place order, then asserts an order was
 * created (the app redirects to the customer dashboard single-order view,
 * URL hash `#orders/{id}`).
 *
 * COD is used deliberately: it is an offline gateway, so the flow needs no
 * external payment sandbox and the assertion stays deterministic.
 */

test( 'guest completes a mobile COD checkout and an order is created', async ( { page } ) => {
	const { id } = await discoverProduct( page );

	await test.step( 'add product to cart and reach checkout', async () => {
		await addToCartAndGotoCheckout( page, id );
	} );

	await test.step( 'fill billing details', async () => {
		await fillBilling( page );
	} );

	await test.step( 'keep shipping same as billing', async () => {
		await ensureSameAsBilling( page );
	} );

	await test.step( 'select a shipping method', async () => {
		await chooseShippingMethod( page );
	} );

	await test.step( 'select Cash on Delivery and place the order', async () => {
		// The app navigates to the dashboard order view on success.
		await Promise.all( [
			page.waitForURL( /#orders\/\d+/, { timeout: 45000 } ),
			placeCodOrder( page ),
		] );
	} );

	await test.step( 'assert an order id is present in the confirmation URL', async () => {
		const match = page.url().match( /#orders\/(\d+)/ );
		expect( match, `expected an order id in the redirect URL, got: ${ page.url() }` ).not.toBeNull();
		const orderId = Number( match[ 1 ] );
		expect( orderId ).toBeGreaterThan( 0 );
	} );
} );
