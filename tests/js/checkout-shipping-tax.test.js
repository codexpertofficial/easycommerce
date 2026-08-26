/**
 * Checkout shipping and tax refresh behaviour, in jsdom. Run: npm run test:js
 */

const test = require( 'node:test' );
const assert = require( 'node:assert' );
const fs = require( 'fs' );
const path = require( 'path' );
const { JSDOM } = require( 'jsdom' );

const CHECKOUT_JS = path.resolve( __dirname, '../../assets/public/js/checkout.js' );

const COUNTRIES = [ '', 'US', 'CA', 'BD', 'FR' ];

/**
 * Markup for one address block.
 *
 * @param {string} type billing or shipping.
 * @return {string} HTML.
 */
function addressBlock( type ) {
	const options = COUNTRIES.map( ( c ) => `<option value="${ c }">${ c }</option>` ).join( '' );

	return `
		<div class="easycommerce-address easycommerce-checkout-${ type }" data-address_type="${ type }">
			<input  id="easycommerce-field-${ type }_first_name" name="${ type }_address[first_name]" class="easycommerce-field" data-field_id="first_name" value="" />
			<select id="easycommerce-field-${ type }_country"    name="${ type }_address[country]"    class="easycommerce-field" data-field_id="country">${ options }</select>
			<select id="easycommerce-field-${ type }_state"      name="${ type }_address[state]"      class="easycommerce-field" data-field_id="state">
				<option value=""></option><option value="Arizona">Arizona</option><option value="California">California</option>
			</select>
			<select id="easycommerce-field-${ type }_city"       name="${ type }_address[city]"       class="easycommerce-field" data-field_id="city">
				<option value=""></option><option value="Big Park">Big Park</option><option value="Los Angeles">Los Angeles</option>
			</select>
			<input  id="easycommerce-field-${ type }_postcode"   name="${ type }_address[postcode]"   class="easycommerce-field" data-field_id="postcode" value="" />
		</div>`;
}

/** Let jQuery ready callbacks and handlers settle. */
function tick() {
	return new Promise( ( resolve ) => setTimeout( resolve, 0 ) );
}

/** Wait past the debounce that coalesces the country/state/city cascade. */
function settle() {
	return new Promise( ( resolve ) => setTimeout( resolve, 400 ) );
}

/**
 * Build a checkout page and run the script against it.
 *
 * @param {Object}  options              Setup options.
 * @param {boolean} options.withShipping Render the shipping section, as a physical cart does.
 * @return {Promise<Object>} Test handle.
 */
async function setup( { withShipping = true } = {} ) {
	const shipping = withShipping
		? `<label><input type="checkbox" id="easycommerce-checkout-same-as-shipping" name="billing_as_shipping" /></label>
		   <div class="easycommerce-checkout-shipping-same-as-address">${ addressBlock( 'shipping' ) }</div>`
		: '';

	const dom = new JSDOM(
		`<!doctype html><html><body>
			<form id="easycommerce-checkout">
				${ addressBlock( 'billing' ) }
				${ shipping }
				<div class="easycommerce-cart-wrapper">
					<input class="easycommerce-cart-quantity-input" product-id="785" price-id="1" data-stock-count="99" data-type="physical" value="1" />
					<input class="easycommerce-cart-quantity-input" product-id="786" price-id="1" data-stock-count="99" data-type="physical" value="1" />
				</div>
				<div class="easycommerce-summary-wrapper"></div>
				<div class="easycommerce-payment-methods">
					<div class="easycommerce-payment_method easycommerce-payment_method-cash-on-delivery-wrap">
						<input type="radio" id="easycommerce-payment_method-cash-on-delivery" class="easycommerce-payment_method" name="easycommerce-payment_method" value="cash-on-delivery" checked />
					</div>
				</div>
				<button type="submit" class="easycommerce-checkout-main-btn">Confirm Order</button>
				<div class="easycommerce-css-loader-wrapper" style="display: none;"></div>
				<div id="easycommerce-checkout-order-error"></div>
			</form>
		</body></html>`,
		{ runScripts: 'dangerously', url: 'https://example.test/checkout/' }
	);

	const { window } = dom;
	const jQuery = require( 'jquery' )( window );

	window.jQuery = jQuery;
	window.$ = jQuery;
	// jsdom has no layout, so scrolling an error message into view is a no-op.
	window.Element.prototype.scrollIntoView = () => {};
	window.wp = { i18n: { __: ( text ) => text, sprintf: ( text ) => text } };
	window.EASYCOMMERCE = {
		rest_base: '/wp-json/easycommerce/v1',
		nonce: 'test-nonce',
		// The store only ships to the US.
		shipping: { countries: { US: 'United States' } },
		customer: { address: { billing: {}, shipping: {} } },
	};

	const requests = [];
	const held = [];

	jQuery.ajax = ( options ) => {
		requests.push( options );

		if ( options.beforeSend ) {
			options.beforeSend( { setRequestHeader: () => {} } );
		}

		// Geo lookups gate the shipping update, so finish them unless held.
		const isHeld = held.some( ( part ) => String( options.url ).includes( part ) );

		if ( ! isHeld && String( options.url ).includes( '/geo/' ) && options.complete ) {
			options.complete();
		}

		return jQuery.Deferred();
	};

	const script = window.document.createElement( 'script' );
	script.textContent = fs.readFileSync( CHECKOUT_JS, 'utf8' );
	window.document.body.appendChild( script );

	// Wait for jQuery ready, which is when checkout.js binds its handlers.
	await new Promise( ( resolve ) => jQuery( window.document ).ready( resolve ) );
	await tick();

	const field = ( type, id ) => jQuery( `#easycommerce-field-${ type }_${ id }` );
	const cartRequests = () => requests.filter( ( r ) => String( r.url ).includes( '/cart/shipping' ) );

	return {
		window,
		jQuery,
		field,
		cartRequests,
		requests,
		reset: () => ( requests.length = 0 ),
		/** Keep matching requests open until finish() is called. */
		hold: ( part ) => held.push( part ),
		/** Complete a captured request, success first, then complete. */
		finish( part, response ) {
			const request = requests.find( ( r ) => String( r.url ).includes( part ) );

			if ( ! request ) {
				throw new Error( `no request matching ${ part }` );
			}

			if ( response && request.success ) {
				request.success( response );
			}
			if ( request.complete ) {
				request.complete();
			}
		},
		/** Set a field value and fire the change the checkout listens for. */
		async set( type, id, value ) {
			field( type, id ).val( value ).trigger( 'change' );
			await settle();
		},
		/** Address sent with the last cart request, by address type. */
		sentAddress( type ) {
			const last = cartRequests().pop();
			if ( ! last ) {
				return null;
			}
			return last.data
				.filter( ( pair ) => pair.name.startsWith( `${ type }_address[` ) )
				.reduce( ( carry, pair ) => {
					carry[ pair.name.replace( `${ type }_address[`, '' ).replace( ']', '' ) ] = pair.value;
					return carry;
				}, {} );
		},
	};
}

test( 'country alone is enough to recalculate', async () => {
	const page = await setup();

	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true );
	page.reset();

	await page.set( 'billing', 'country', 'US' );

	assert.ok(
		page.cartRequests().length > 0,
		'a country region must be found without waiting for state, city or postcode'
	);
	assert.equal( page.sentAddress( 'shipping' ).country, 'US' );
} );

test( 'country and state are sent once a state is picked', async () => {
	const page = await setup();

	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true );
	await page.set( 'billing', 'country', 'US' );
	page.reset();

	await page.set( 'billing', 'state', 'California' );

	const sent = page.sentAddress( 'shipping' );
	assert.equal( sent.country, 'US' );
	assert.equal( sent.state, 'California' );
} );

test( 'country, state and city are sent once a city is picked', async () => {
	const page = await setup();

	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true );
	await page.set( 'billing', 'country', 'US' );
	await page.set( 'billing', 'state', 'Arizona' );
	page.reset();

	await page.set( 'billing', 'city', 'Big Park' );

	const sent = page.sentAddress( 'shipping' );
	assert.equal( sent.country, 'US' );
	assert.equal( sent.state, 'Arizona' );
	assert.equal( sent.city, 'Big Park' );
} );

test( 'country, state, city and postcode are all sent together', async () => {
	const page = await setup();

	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true );
	await page.set( 'billing', 'country', 'US' );
	await page.set( 'billing', 'state', 'Arizona' );
	await page.set( 'billing', 'city', 'Big Park' );
	page.reset();

	await page.set( 'billing', 'postcode', '86351' );

	const sent = page.sentAddress( 'shipping' );
	assert.deepEqual(
		{ country: sent.country, state: sent.state, city: sent.city, postcode: sent.postcode },
		{ country: 'US', state: 'Arizona', city: 'Big Park', postcode: '86351' }
	);
} );

test( 'a country, state and city burst is coalesced into one cart request', async () => {
	const page = await setup();

	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true );
	page.reset();

	// No wait between them, as the geo cascade fires them back to back.
	page.field( 'billing', 'country' ).val( 'US' ).trigger( 'change' );
	page.field( 'billing', 'state' ).val( 'Arizona' ).trigger( 'change' );
	page.field( 'billing', 'city' ).val( 'Big Park' ).trigger( 'change' );
	await settle();

	assert.equal(
		page.cartRequests().length,
		1,
		'one country pick must not write the cart three times'
	);
} );

test( 'a geo lookup landing after the debounce still yields one cart request', async () => {
	const page = await setup();

	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true );
	page.hold( '/geo/cities' );
	await page.set( 'billing', 'country', 'US' );
	page.reset();

	page.field( 'billing', 'state' ).val( 'Arizona' ).trigger( 'change' );
	await settle();

	assert.equal(
		page.cartRequests().length,
		0,
		'the cart must wait while the city select is still being repopulated'
	);

	// The cities response arrives late and re-fires change on the city select.
	page.finish( '/geo/cities', { success: true, data: { cities: [ 'Big Park' ] } } );
	await settle();

	assert.equal( page.cartRequests().length, 1, 'one address change is one write' );
} );

test( 'billing is mirrored into shipping while same as billing is checked', async () => {
	const page = await setup();

	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true );
	await page.set( 'billing', 'country', 'US' );
	await page.set( 'billing', 'state', 'Arizona' );
	await page.set( 'billing', 'city', 'Big Park' );
	await page.set( 'billing', 'postcode', '86351' );

	assert.equal( page.field( 'shipping', 'state' ).val(), 'Arizona' );
	assert.equal( page.field( 'shipping', 'city' ).val(), 'Big Park' );
	assert.equal( page.field( 'shipping', 'postcode' ).val(), '86351' );
} );

test( 'a separate shipping address drives its own recalculation', async () => {
	const page = await setup();

	await page.set( 'billing', 'country', 'US' );
	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', false );
	page.reset();

	await page.set( 'shipping', 'country', 'US' );
	await page.set( 'shipping', 'state', 'California' );

	const sent = page.sentAddress( 'shipping' );
	assert.equal( sent.state, 'California' );
} );

test( 'the shipping location is cleared when the billing country has no shipping', async () => {
	const page = await setup();

	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true );
	await page.set( 'billing', 'country', 'US' );
	await page.set( 'billing', 'state', 'Arizona' );
	await page.set( 'billing', 'city', 'Big Park' );
	page.reset();

	// BD is not in EASYCOMMERCE.shipping.countries.
	await page.set( 'billing', 'country', 'BD' );

	assert.equal( page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked' ), false );
	assert.equal( page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'disabled' ), true );

	assert.equal( page.field( 'shipping', 'country' ).val(), '', 'a stale country would keep a country level cost' );
	assert.equal( page.field( 'shipping', 'state' ).val(), '' );
	assert.equal( page.field( 'shipping', 'city' ).val(), '' );
	assert.equal( page.field( 'shipping', 'postcode' ).val(), '' );

	assert.ok( page.cartRequests().length > 0, 'the cart must refresh so the old cost drops off' );
} );

test( 'toggling same as billing recalculates both ways', async () => {
	const page = await setup();
	const checkbox = page.jQuery( '#easycommerce-checkout-same-as-shipping' );

	checkbox.prop( 'checked', true );
	await page.set( 'billing', 'country', 'US' );
	await page.set( 'billing', 'state', 'Arizona' );
	await page.set( 'billing', 'city', 'Big Park' );

	page.reset();
	checkbox.prop( 'checked', false ).trigger( 'change' );
	await tick();

	assert.ok( page.cartRequests().length > 0, 'unchecking must recalculate' );
	assert.equal( page.field( 'shipping', 'state' ).val(), '', 'the mirrored address must not linger' );
	assert.equal( page.field( 'shipping', 'city' ).val(), '' );

	page.reset();
	checkbox.prop( 'checked', true ).trigger( 'change' );
	await tick();

	assert.ok( page.cartRequests().length > 0, 'rechecking must recalculate' );
} );

test( 'emptying the shipping address is sent, so the old one stops charging', async () => {
	const page = await setup();

	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', false );
	await page.set( 'shipping', 'country', 'US' );

	const sent = page.cartRequests().pop();
	assert.ok( sent, 'the address must reach the cart' );
	page.reset();

	// The customer clears it again.
	await page.set( 'shipping', 'country', '' );

	const cleared = page.cartRequests().pop();

	assert.ok( cleared, 'clearing must be sent too, or the cart keeps the old address' );

	const country = cleared.data.find( ( pair ) => pair.name === 'shipping_address[country]' );
	assert.equal( country.value, '', 'and it must be sent as empty' );
} );

test( 'a blank shipping address on first load sends nothing', async () => {
	const page = await setup();

	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', false );
	page.reset();

	// Nothing has ever been sent, so there is nothing to clear.
	await page.set( 'shipping', 'state', 'Arizona' );

	assert.equal( page.cartRequests().length, 0, 'no country yet means no pointless request' );
} );

test( 'a quantity change re-bands shipping while same as billing is checked', async () => {
	const page = await setup();

	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true );
	await page.set( 'billing', 'country', 'US' );
	page.reset();

	page.jQuery( '.easycommerce-cart-quantity-input' ).first().val( 20 ).trigger( 'input' );

	// The quantity request is debounced by 400ms.
	await new Promise( ( resolve ) => setTimeout( resolve, 500 ) );

	const update = page.requests.find( ( r ) => String( r.url ).includes( '/cart/update' ) );
	assert.ok( update, 'the quantity must reach the cart' );

	update.success( { success: true, data: { cart: { fragments: {} } } } );
	await settle();

	assert.ok(
		page.cartRequests().length > 0,
		'weight and quantity tiers are matched server side, so 2 -> 20 must re-band'
	);
} );

test( 'a digital cart with no shipping section still refreshes tax', async () => {
	const page = await setup( { withShipping: false } );

	assert.equal( page.jQuery( '.easycommerce-checkout-shipping' ).length, 0 );
	page.reset();

	await page.set( 'billing', 'country', 'US' );

	assert.ok(
		page.cartRequests().length > 0,
		'tax is rendered by this request, so a digital cart must trigger it too'
	);
	assert.equal( page.sentAddress( 'billing' ).country, 'US' );
} );

test( 'a digital cart refreshes tax when the city changes', async () => {
	const page = await setup( { withShipping: false } );

	await page.set( 'billing', 'country', 'US' );
	await page.set( 'billing', 'state', 'Arizona' );
	page.reset();

	await page.set( 'billing', 'city', 'Big Park' );

	assert.ok( page.cartRequests().length > 0, 'a city level tax rate depends on the city' );
	assert.equal( page.sentAddress( 'billing' ).city, 'Big Park' );
} );

test( 'two cart rows changed together both reach the cart', async () => {
	const page = await setup();

	page.reset();

	const rows = page.jQuery( '.easycommerce-cart-quantity-input' );

	// Inside the debounce window: a shared timer would drop the first row.
	rows.eq( 0 ).val( 4 ).trigger( 'input' );
	rows.eq( 1 ).val( 7 ).trigger( 'input' );

	await new Promise( ( resolve ) => setTimeout( resolve, 600 ) );

	const updates = page.requests.filter( ( r ) => String( r.url ).includes( '/cart/update' ) );

	assert.equal( updates.length, 2, 'each row debounces on its own' );
	assert.deepEqual(
		updates.map( ( r ) => [ String( r.data.id ), r.data.quantity ] ).sort(),
		[ [ '785', 4 ], [ '786', 7 ] ]
	);
} );

test( 'clearing the quantity field sends nothing', async () => {
	const page = await setup();

	page.reset();

	// parseInt( "" ) is NaN, which serialises as "NaN" and absint()s to 0.
	page.jQuery( '.easycommerce-cart-quantity-input' ).first().val( '' ).trigger( 'input' );

	await new Promise( ( resolve ) => setTimeout( resolve, 600 ) );

	const updates = page.requests.filter( ( r ) => String( r.url ).includes( '/cart/update' ) );

	assert.equal( updates.length, 0, 'an empty field must not commit a quantity' );
} );

test( 'a plain string error from the server is shown, not swallowed', async () => {
	const page = await setup();

	page.reset();
	page.jQuery( '.easycommerce-cart-quantity-input' ).first().val( 9 ).trigger( 'input' );
	await new Promise( ( resolve ) => setTimeout( resolve, 600 ) );

	const update = page.requests.find( ( r ) => String( r.url ).includes( '/cart/update' ) );
	assert.ok( update, 'the quantity must reach the cart' );

	// response_error( 'Product is out of stock!' ) serialises data as a string.
	update.error( { responseText: '{"success":false,"data":"Product is out of stock!"}' } );
	await tick();

	assert.equal(
		page.jQuery( '#easycommerce-checkout-order-error' ).text(),
		'Product is out of stock!',
		'the real reason must reach the customer'
	);
} );

test( 'the address is posted, not put in a query string', async () => {
	const page = await setup();

	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true );
	page.reset();

	await page.set( 'billing', 'country', 'US' );

	const request = page.cartRequests().pop();

	assert.ok( request, 'the cart must be asked to recalculate' );
	assert.equal( request.type, 'POST', 'name, email, phone and address must not land in access logs' );
	assert.ok( String( request.url ).includes( '/cart/shipping/calculate' ) );
} );

test( 'no country means no geo lookup, so no error banner on load', async () => {
	const page = await setup();

	// The country select starts blank, and the load-time change fires the cascade.
	assert.equal(
		page.requests.filter( ( r ) => String( r.url ).includes( '/geo/' ) ).length,
		0,
		'/geo/states/ has no route without a country and its 404 surfaces as an error'
	);

	page.reset();
	await page.set( 'billing', 'country', 'US' );

	assert.ok(
		page.requests.some( ( r ) => String( r.url ).includes( '/geo/states/US' ) ),
		'picking a country still loads its states'
	);
} );

test( 'selecting a payment method sends one request, not two', async () => {
	const page = await setup();

	page.reset();

	// The class sits on the wrapper div as well as the radio.
	page.jQuery( '#easycommerce-payment_method-cash-on-delivery' ).prop( 'checked', true ).trigger( 'change' );
	await tick();

	const calls = page.requests.filter( ( r ) => String( r.url ).includes( '/cart/payment' ) );

	assert.equal( calls.length, 1, 'one selection is one write' );
} );

test( 'a second submit while the order is open sends nothing', async () => {
	const page = await setup();

	page.field( 'billing', 'first_name' ).val( 'John' );
	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true ).trigger( 'change' );
	await tick();
	page.reset();

	const submit = () => page.window.document
		.getElementById( 'easycommerce-checkout' )
		.dispatchEvent( new page.window.Event( 'submit', { bubbles: true, cancelable: true } ) );

	submit();
	await tick();
	// Pressing Enter in a text input submits the form again.
	submit();
	await tick();

	const orders = page.requests.filter( ( r ) => String( r.url ).endsWith( '/orders' ) );
	assert.equal( orders.length, 1, 'the order must be placed once' );
} );

test( 'a second submit does not reach the gateway either', async () => {
	const page = await setup();

	page.field( 'billing', 'first_name' ).val( 'John' );
	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true ).trigger( 'change' );
	await tick();

	// A gateway binds on the form after checkout.js, as stripe-payment.js does.
	let gatewayRuns = 0;
	page.jQuery( '#easycommerce-checkout' ).on( 'submit', function ( e ) {
		gatewayRuns++;
		e.preventDefault();
	} );

	page.reset();

	const submit = () => page.window.document
		.getElementById( 'easycommerce-checkout' )
		.dispatchEvent( new page.window.Event( 'submit', { bubbles: true, cancelable: true } ) );

	submit();
	await tick();
	// Enter in any text input submits the form again while the first order is open.
	submit();
	await tick();

	assert.equal( gatewayRuns, 1, 'the gateway must not be handed a second submit' );
	assert.equal(
		page.requests.filter( ( r ) => String( r.url ).endsWith( '/orders' ) ).length,
		1,
		'and only one order is sent'
	);
} );

test( 'a failed order can be retried', async () => {
	const page = await setup();

	page.field( 'billing', 'first_name' ).val( 'John' );
	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true ).trigger( 'change' );
	await tick();
	page.reset();

	const submit = () => page.window.document
		.getElementById( 'easycommerce-checkout' )
		.dispatchEvent( new page.window.Event( 'submit', { bubbles: true, cancelable: true } ) );

	submit();
	await tick();

	const order = page.requests.find( ( r ) => String( r.url ).endsWith( '/orders' ) );
	assert.ok( order, 'the order must be sent' );

	// The gateway declines: the guard must let the customer try again.
	order.error( { responseText: '{"success":false,"data":"Your card was declined."}' } );
	await tick();

	assert.equal(
		page.jQuery( '#easycommerce-checkout-order-error' ).text(),
		'Your card was declined.'
	);

	submit();
	await tick();

	assert.equal(
		page.requests.filter( ( r ) => String( r.url ).endsWith( '/orders' ) ).length,
		2,
		'an in-flight flag that is never released would strand the customer'
	);
} );

test( 'an order with no redirect restores the form instead of navigating to undefined', async () => {
	const page = await setup();

	page.field( 'billing', 'first_name' ).val( 'John' );
	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true ).trigger( 'change' );
	await tick();
	page.reset();

	const before = page.window.location.href;

	page.window.document
		.getElementById( 'easycommerce-checkout' )
		.dispatchEvent( new page.window.Event( 'submit', { bubbles: true, cancelable: true } ) );
	await tick();

	const order = page.requests.find( ( r ) => String( r.url ).endsWith( '/orders' ) );
	assert.ok( order, 'the order must be sent' );

	// easycommerce_order_redirect can filter the value away entirely.
	order.success( { success: true, data: { message: 'Order created.' } } );
	await tick();

	assert.equal( page.window.location.href, before, 'a missing redirect must not navigate' );
	assert.notEqual(
		page.jQuery( '.easycommerce-checkout-main-btn' )[ 0 ].style.display,
		'none',
		'the button must come back rather than leaving a permanent spinner'
	);
} );

test( 'an object error payload does not print [object Object]', async () => {
	const page = await setup();

	page.field( 'billing', 'first_name' ).val( 'John' );
	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true ).trigger( 'change' );
	await tick();
	page.reset();

	page.window.document
		.getElementById( 'easycommerce-checkout' )
		.dispatchEvent( new page.window.Event( 'submit', { bubbles: true, cancelable: true } ) );
	await tick();

	const order = page.requests.find( ( r ) => String( r.url ).endsWith( '/orders' ) );

	// What WP core sends for an expired nonce: no data.message anywhere.
	order.error( {
		responseText: '{"code":"rest_cookie_invalid_nonce","message":"Cookie check failed","data":{"status":403}}',
	} );
	await tick();

	const shown = page.jQuery( '#easycommerce-checkout-order-error' ).text();

	assert.notEqual( shown, '[object Object]' );
	assert.equal( shown, 'Cookie check failed' );
} );

test( 'a store with no gateway can still place the order', async () => {
	const page = await setup();

	// The container is rendered by every template; only the radios are missing.
	page.jQuery( '.easycommerce-payment-methods' ).html( '<div id="easycommerce-payment_methods"></div>' );
	page.field( 'billing', 'first_name' ).val( 'John' );
	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true ).trigger( 'change' );
	await tick();
	page.reset();

	page.window.document
		.getElementById( 'easycommerce-checkout' )
		.dispatchEvent( new page.window.Event( 'submit', { bubbles: true, cancelable: true } ) );
	await tick();

	assert.equal(
		page.requests.filter( ( r ) => String( r.url ).endsWith( '/orders' ) ).length,
		1,
		'with no gateway configured the order must still be sent'
	);
} );

test( 'a checkout without a last name field does not send \"John undefined\"', async () => {
	const page = await setup();

	// easycommerce_checkout_fields_billing can drop fields; template-2 already does.
	page.jQuery( '#easycommerce-field-billing_last_name' ).remove();
	page.field( 'billing', 'first_name' ).val( 'John' );
	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true ).trigger( 'change' );
	await tick();
	page.reset();

	page.window.document
		.getElementById( 'easycommerce-checkout' )
		.dispatchEvent( new page.window.Event( 'submit', { bubbles: true, cancelable: true } ) );
	await tick();

	const order = page.requests.find( ( r ) => String( r.url ).endsWith( '/orders' ) );
	const name = order.data.find( ( pair ) => pair.name === 'customer[name]' ).value;

	assert.equal( name, 'John' );
} );

test( 'invalid fields block a gateway handler bound on the form', async () => {
	const page = await setup();

	// A gateway binds directly on the form, as stripe-payment.js does.
	let gatewayCharged = false;
	page.jQuery( '#easycommerce-checkout' ).on( 'submit', function ( e ) {
		e.preventDefault();
		gatewayCharged = true;
	} );

	// Dispatch natively: jQuery.trigger() never reaches native listeners.
	const submit = () => page.window.document
		.getElementById( 'easycommerce-checkout' )
		.dispatchEvent( new page.window.Event( 'submit', { bubbles: true, cancelable: true } ) );

	// first_name is required and empty.
	page.field( 'billing', 'first_name' ).prop( 'required', true ).val( '' );
	submit();
	await tick();

	assert.equal( gatewayCharged, false, 'the card must not be charged before validation passes' );

	// Filled, with shipping mirroring billing, so the gateway may run.
	page.field( 'billing', 'first_name' ).val( 'John' );
	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true ).trigger( 'change' );
	await tick();
	submit();
	await tick();

	assert.equal( gatewayCharged, true, 'a valid form must still reach the gateway' );
} );

test( 'a cleared select does not throw during validation', async () => {
	const page = await setup();

	page.field( 'shipping', 'country' ).val( '' );
	page.field( 'shipping', 'country' ).prop( 'required', true );

	assert.doesNotThrow( () => {
		page.window.document
			.getElementById( 'easycommerce-checkout' )
			.dispatchEvent( new page.window.Event( 'submit', { bubbles: true, cancelable: true } ) );
	} );
} );

test( 'an unselected shipping method stops the order before it is sent', async () => {
	const page = await setup();

	// Summary rendered with two methods and none preselected, plus a gateway.
	page.jQuery( '.easycommerce-summary-wrapper' ).html(
		'<span class="easycommerce-shipping-method-error hidden"></span>' +
		'<input type="radio" name="shipping_method" class="easycommerce-shipping-method" value="1" />' +
		'<input type="radio" name="shipping_method" class="easycommerce-shipping-method" value="2" />'
	);
	page.jQuery( '.easycommerce-payment-methods' ).html(
		'<span class="easycommerce-payment-method-error hidden"></span>' +
		'<input type="radio" name="easycommerce-payment_method" class="easycommerce-payment_method" value="cash-on-delivery" checked />'
	);

	// Satisfy the field validation so only the method check can fail.
	page.field( 'billing', 'first_name' ).val( 'John' );
	page.jQuery( '#easycommerce-checkout-same-as-shipping' ).prop( 'checked', true ).trigger( 'change' );
	await tick();

	let orderPosted = false;
	page.jQuery( '#easycommerce-checkout' ).on( 'payment', () => ( orderPosted = true ) );

	const submit = () => page.window.document
		.getElementById( 'easycommerce-checkout' )
		.dispatchEvent( new page.window.Event( 'submit', { bubbles: true, cancelable: true } ) );

	submit();
	await tick();

	assert.equal( orderPosted, false, 'the order must not be sent with no shipping method chosen' );
	assert.equal(
		page.jQuery( '.easycommerce-shipping-method-error' ).hasClass( 'hidden' ),
		false,
		'the shipping method error must be shown'
	);

	// Choosing a method lets it through.
	page.jQuery( '.easycommerce-shipping-method' ).first().prop( 'checked', true );
	submit();
	await tick();

	assert.equal( orderPosted, true, 'with a method chosen the order proceeds' );
} );

test( 'an unselected payment method stops a digital order', async () => {
	const page = await setup( { withShipping: false } );

	page.jQuery( '.easycommerce-payment-methods' ).html(
		'<span class="easycommerce-payment-method-error hidden"></span>' +
		'<input type="radio" name="easycommerce-payment_method" class="easycommerce-payment_method" value="stripe" />'
	);
	page.field( 'billing', 'first_name' ).val( 'John' );

	let orderPosted = false;
	page.jQuery( '#easycommerce-checkout' ).on( 'payment', () => ( orderPosted = true ) );

	page.window.document
		.getElementById( 'easycommerce-checkout' )
		.dispatchEvent( new page.window.Event( 'submit', { bubbles: true, cancelable: true } ) );
	await tick();

	assert.equal( orderPosted, false, 'a digital cart with no payment method must not post the order' );
	assert.equal( page.jQuery( '.easycommerce-payment-method-error' ).hasClass( 'hidden' ), false );
} );
