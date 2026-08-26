// @ts-check
const { defineConfig, devices } = require( '@playwright/test' );

/**
 * Playwright config for EasyCommerce storefront regression suite.
 *
 * Covers two acceptance criteria of issue #3149:
 *   1. Accessibility (axe-core) scan of key storefront/checkout views.
 *   2. End-to-end mobile checkout driving a full COD sandbox purchase.
 *
 * The storefront is server-rendered PHP + vanilla JS (not React), so the
 * suite runs against a live WordPress instance rather than a bundler dev
 * server. Point it at any environment via EC_BASE_URL.
 *
 *   EC_BASE_URL   base URL of the store            (default https://easycommerce.test)
 *   EC_PRODUCT_ID product id used for the checkout  (default: auto-discovered)
 *
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig( {
	testDir: './tests/e2e',
	// A full guest checkout crosses several AJAX geo lookups; give it room.
	timeout: 90 * 1000,
	expect: { timeout: 15 * 1000 },
	// Order-creating tests mutate the store; never run them against each other.
	fullyParallel: false,
	workers: 1,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 1 : 0,
	reporter: process.env.CI
		? [ [ 'github' ], [ 'list' ], [ 'html', { open: 'never' } ] ]
		: [ [ 'list' ], [ 'html', { open: 'never' } ] ],

	use: {
		baseURL: process.env.EC_BASE_URL || 'https://easycommerce.test',
		// Local *.test hosts commonly use self-signed certificates.
		ignoreHTTPSErrors: true,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},

	projects: [
		{
			// Mobile viewport for both a11y and checkout, per issue #3149.
			name: 'mobile-chromium',
			use: { ...devices[ 'Pixel 5' ] },
		},
	],
} );
