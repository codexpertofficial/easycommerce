// @ts-check
const { test, expect } = require( '@playwright/test' );
const AxeBuilder = require( '@axe-core/playwright' ).default;
const fs = require( 'fs' );
const path = require( 'path' );
const { discoverProduct, addToCartAndGotoCheckout } = require( './utils/store' );

/**
 * Accessibility regression scan of key storefront/checkout views (issue #3149).
 *
 * Strategy: axe-core scans each view; a view fails only on a *new* critical or
 * serious violation — one whose rule id is not already recorded in
 * a11y-baseline.json. This lets the gate protect against regressions today
 * without blocking on the storefront's pre-existing debt, exactly matching the
 * acceptance criterion "CI fails on a new critical a11y violation".
 *
 * Refresh the baseline after an intentional markup change:
 *   UPDATE_A11Y_BASELINE=1 npx playwright test a11y
 */

const BASELINE_PATH = path.join( __dirname, 'a11y-baseline.json' );
const FAIL_IMPACTS = [ 'critical', 'serious' ];
const UPDATE_MODE = !! process.env.UPDATE_A11Y_BASELINE;

/** @returns {Record<string, string[]>} */
function loadBaseline() {
	try {
		return JSON.parse( fs.readFileSync( BASELINE_PATH, 'utf8' ) );
	} catch {
		return {};
	}
}

function saveBaselineView( viewKey, ruleIds ) {
	const baseline = loadBaseline();
	baseline[ viewKey ] = [ ...new Set( ruleIds ) ].sort();
	fs.writeFileSync( BASELINE_PATH, JSON.stringify( baseline, null, '\t' ) + '\n' );
}

/**
 * Scan the current page and assert no new critical/serious violation appears.
 *
 * @param {import('@playwright/test').Page} page
 * @param {string} viewKey
 */
async function assertNoA11yRegression( page, viewKey ) {
	const results = await new AxeBuilder( { page } )
		.withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa' ] )
		.analyze();

	const failing = results.violations.filter( ( v ) => FAIL_IMPACTS.includes( v.impact ) );
	const failingIds = failing.map( ( v ) => v.id );

	if ( UPDATE_MODE ) {
		saveBaselineView( viewKey, failingIds );
		test.info().annotations.push( {
			type: 'a11y-baseline',
			description: `${ viewKey }: recorded ${ failingIds.length } rule(s)`,
		} );
		return;
	}

	const baseline = loadBaseline()[ viewKey ] || [];
	const regressions = failing.filter( ( v ) => ! baseline.includes( v.id ) );

	const report = regressions
		.map(
			( v ) =>
				`  [${ v.impact }] ${ v.id }: ${ v.help } (${ v.nodes.length } node(s))\n    ${ v.helpUrl }`
		)
		.join( '\n' );

	expect(
		regressions,
		`New a11y violation(s) on "${ viewKey }" view:\n${ report }`
	).toEqual( [] );
}

test.describe( 'Storefront accessibility', () => {
	test( 'shop page has no new critical/serious a11y violations', async ( { page } ) => {
		await page.goto( process.env.EC_SHOP_PATH || '/shop/', { waitUntil: 'domcontentloaded' } );
		await expect( page.locator( 'body' ) ).toBeVisible();
		await assertNoA11yRegression( page, 'shop' );
	} );

	test( 'single product has no new critical/serious a11y violations', async ( { page } ) => {
		const { url } = await discoverProduct( page );
		test.skip( ! url, 'no single-product URL discoverable on the shop archive' );
		await page.goto( url, { waitUntil: 'domcontentloaded' } );
		await expect( page.locator( 'body' ) ).toBeVisible();
		await assertNoA11yRegression( page, 'product' );
	} );

	test( 'checkout has no new critical/serious a11y violations', async ( { page } ) => {
		const { id } = await discoverProduct( page );
		await addToCartAndGotoCheckout( page, id );
		await assertNoA11yRegression( page, 'checkout' );
	} );
} );
