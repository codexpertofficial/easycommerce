import React, { useEffect, useRef, useState } from 'react';
import { __ } from '@wordpress/i18n';

const crossIcon = `${EASYCOMMERCE.assets}admin/img/icons/cross.png`;

const STEPS = [
	{ id: 'store', label: __('Shop page', 'easycommerce') },
	{ id: 'product', label: __('Purchasable product', 'easycommerce') },
	{ id: 'cart', label: __('Add to cart', 'easycommerce') },
	{ id: 'gateways', label: __('Payment gateways', 'easycommerce') },
	{ id: 'order', label: __('Place order', 'easycommerce') },
	{ id: 'verify', label: __('Verify & clean up', 'easycommerce') },
];

const idleSteps = () =>
	STEPS.reduce((acc, step) => ({ ...acc, [step.id]: { status: 'idle' } }), {});

const errorMessage = (json, fallback) => {
	const data = json?.data;

	if (typeof data === 'string') {
		return data;
	}

	return data?.message || json?.message || fallback;
};

// Server-side checks: the admin's own session.
const adminCall = async (path, method = 'GET', body) => {
	const res = await fetch(`${EASYCOMMERCE.rest_base}${path}`, {
		method,
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': EASYCOMMERCE.nonce,
		},
		body: body ? JSON.stringify(body) : undefined,
	});
	const json = await res.json().catch(() => ({}));

	if (!res.ok || json?.success === false) {
		throw new Error(errorMessage(json, __('The request failed.', 'easycommerce')));
	}

	return json.data;
};

// Cart and order: sent without the nonce so WordPress treats the call as a
// guest, exactly like a customer's browser, and keeps the admin's own cart out of it.
const guestCall = async (path, method = 'GET', body) => {
	const res = await fetch(`${EASYCOMMERCE.rest_base}${path}`, {
		method,
		credentials: 'include',
		headers: { 'Content-Type': 'application/json' },
		body: body ? JSON.stringify(body) : undefined,
	});
	const json = await res.json().catch(() => ({}));

	if (!res.ok || json?.success === false) {
		throw new Error(errorMessage(json, __('The request failed.', 'easycommerce')));
	}

	return json.data;
};

const StatusIcon = ({ status }) => {
	if (status === 'running') {
		return (
			<span className="w-5 h-5 shrink-0 rounded-full border-2 border-ec-primary border-t-transparent animate-spin" />
		);
	}

	const styles = {
		passed: 'bg-[#12B76A] text-white',
		warning: 'bg-[#F79009] text-white',
		failed: 'bg-[#F04438] text-white',
		skipped: 'bg-[#EAECF0] text-[#98A2B3]',
		idle: 'border-2 border-[#D0D5DD] text-transparent',
	};
	const glyph = { passed: '✓', warning: '!', failed: '✕', skipped: '–', idle: '' };

	return (
		<span
			className={`w-5 h-5 shrink-0 rounded-full flex items-center justify-center text-xs font-bold ${styles[status]}`}
		>
			{glyph[status]}
		</span>
	);
};

const detailColor = {
	passed: 'text-[#067647]',
	warning: 'text-[#B54708]',
	failed: 'text-[#B42318]',
};

const TestOrder = () => {
	const [open, setOpen] = useState(false);
	const [running, setRunning] = useState(false);
	const [steps, setSteps] = useState(idleSteps());
	const [last, setLast] = useState(null);
	const [finished, setFinished] = useState(false);

	// Results the finish call stores, collected as the run advances.
	const results = useRef([]);

	useEffect(() => {
		if (!open) {
			return;
		}

		adminCall('/test-order/last')
			.then((data) => setLast(data.run))
			.catch(() => {});
	}, [open]);

	const update = (id, patch) =>
		setSteps((prev) => ({ ...prev, [id]: { ...prev[id], ...patch } }));

	// Runs one step; resolves to the step result and records it.
	const step = async (id, work) => {
		update(id, { status: 'running', message: '', details: [], link: '' });

		let result;

		try {
			result = await work();
		} catch (error) {
			result = { status: 'failed', message: error.message };
		}

		update(id, result);
		results.current.push({ id, status: result.status, message: result.message || '' });

		return result;
	};

	const run = async () => {
		setRunning(true);
		setFinished(false);
		setSteps(idleSteps());
		results.current = [];

		let token = '';
		let aborted = false;

		const skipRest = (from) => {
			let skipping = false;

			STEPS.forEach((s) => {
				if (s.id === from) {
					skipping = true;
					return;
				}
				if (skipping && s.id !== 'verify') {
					update(s.id, { status: 'skipped', message: __('Skipped because an earlier step failed.', 'easycommerce') });
				}
			});
		};

		try {
			const started = await adminCall('/test-order/start', 'POST');
			token = started.token;

			const store = await step('store', () => adminCall('/test-order/check/store'));
			if (store.status === 'failed') {
				aborted = true;
				skipRest('store');
			}

			let product;
			if (!aborted) {
				const found = await step('product', async () => {
					const data = await adminCall('/test-order/check/product');
					product = data.product;
					return data;
				});
				if (found.status === 'failed') {
					aborted = true;
					skipRest('product');
				}
			}

			let hash = '';
			if (!aborted) {
				const cart = await step('cart', async () => {
					const added = await guestCall('/cart', 'POST', {
						products: [{ id: product.product_id, price_id: product.price_id, quantity: 1 }],
					});

					const reread = await guestCall('/cart');

					if (!reread.hash || reread.hash !== added.hash) {
						throw new Error(
							__('The cart did not persist between requests. A cache or security plugin may be blocking the cart session.', 'easycommerce')
						);
					}

					const lines = reread.cart?.items;

					if (!lines || !Object.keys(lines).length) {
						throw new Error(__('The product was added but the cart is empty.', 'easycommerce'));
					}

					hash = reread.hash;

					return { status: 'passed', message: __('Product added to the cart and read back.', 'easycommerce') };
				});
				if (cart.status === 'failed') {
					aborted = true;
					skipRest('cart');
				}
			}

			if (!aborted) {
				const gateways = await step('gateways', () => adminCall('/test-order/check/gateways'));
				if (gateways.status === 'failed') {
					aborted = true;
					skipRest('gateways');
				}
			}

			let orderPlaced = false;
			if (!aborted) {
				const order = await step('order', async () => {
					const data = await guestCall('/orders', 'POST', {
						items: hash,
						customer: { name: 'Test Order', first_name: 'Test', last_name: 'Order', email: started.email },
						billing_address: { first_name: 'Test', last_name: 'Order', email: started.email },
						billing_as_shipping: 1,
						'easycommerce-payment_method': started.payment_method,
						'easycommerce-test-token': token,
					});

					orderPlaced = true;

					return {
						status: 'passed',
						message: __('A test order was placed. No payment gateway was charged.', 'easycommerce'),
						orderId: data.order_id,
					};
				});

				if (order.status === 'failed') {
					aborted = true;
				}
			}

			// Always finish: it removes any partial order and stores the result.
			update('verify', { status: 'running', message: '' });
			const done = await adminCall('/test-order/finish', 'POST', {
				token,
				cart_hash: hash,
				results: results.current,
			});
			token = '';

			if (orderPlaced && done.verified) {
				update('verify', {
					status: 'passed',
					message: __('The order was saved correctly, then removed with its stock, customer and cart.', 'easycommerce'),
				});
			} else if (orderPlaced) {
				update('verify', {
					status: 'failed',
					message: __('The order was placed but could not be read back.', 'easycommerce'),
				});
			} else {
				update('verify', {
					status: 'skipped',
					message: __('No order was placed, nothing to clean up.', 'easycommerce'),
				});
			}

			setLast(done.run);
		} catch (error) {
			update('verify', { status: 'failed', message: error.message });
		}

		setRunning(false);
		setFinished(true);
	};

	const close = () => {
		if (!running) {
			setOpen(false);
		}
	};

	const hasFailure = Object.values(steps).some((s) => s.status === 'failed');
	const hasWarning = Object.values(steps).some((s) => s.status === 'warning');

	return (
		<>
			<button
				onClick={() => setOpen(true)}
				className="flex items-center cursor-pointer p-2 font-inter font-medium text-sm leading-5 text-ec-primary hover:text-ec-primary focus:shadow-none"
			>
				{__('Test Order', 'easycommerce')}
			</button>
			<span>|</span>

			{open && (
				<div
					className="fixed top-0 left-0 w-full h-full bg-[#0000002B] z-[100000]"
					onClick={close}
				>
					<div
						className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] max-w-[95vw] max-h-[90vh] overflow-y-auto rounded-[22px] px-[36px] py-[32px] bg-white shadow-search-box-shadow"
						onClick={(e) => e.stopPropagation()}
					>
						{!running && (
							<button
								onClick={close}
								className="absolute top-3 right-3 flex items-center justify-center w-6 h-6 bg-[#FFFFFF] border border-ec-border rounded-full"
							>
								<img src={crossIcon} alt="" />
							</button>
						)}

						<h2 className="font-inter text-2xl font-medium mb-2 text-ec-title">
							{__('Test your checkout', 'easycommerce')}
						</h2>
						<p className="font-inter font-normal text-sm leading-5 text-ec-body mb-5">
							{__(
								'Runs a real guest checkout from cart to order, then removes the test order again. No payment gateway is charged, and no email is sent.',
								'easycommerce'
							)}
						</p>

						<ul className="flex flex-col gap-3 mb-5">
							{STEPS.map((s) => {
								const state = steps[s.id];

								return (
									<li key={s.id} className="flex items-start gap-3">
										<StatusIcon status={state.status} />
										<div className="grow">
											<div className="font-inter text-sm font-medium text-ec-title">{s.label}</div>
											{state.message && (
												<div
													className={`font-inter text-sm leading-5 ${
														state.status === 'failed'
															? 'text-[#B42318]'
															: state.status === 'warning'
															? 'text-[#B54708]'
															: 'text-ec-body'
													}`}
												>
													{state.message}
												</div>
											)}
											{Array.isArray(state.details) &&
												state.details.map((d, i) => (
													<div
														key={i}
														className={`font-inter text-xs leading-5 ${detailColor[d.status] || 'text-ec-body'}`}
													>
														• {d.message}
													</div>
												))}
											{state.link && (
												<a
													href={state.link}
													className="font-inter text-sm text-ec-primary hover:underline"
												>
													{__('Fix this', 'easycommerce')} →
												</a>
											)}
										</div>
									</li>
								);
							})}
						</ul>

						{finished && (
							<div
								className={`rounded-lg px-4 py-3 mb-4 font-inter text-sm ${
									hasFailure
										? 'bg-[#FEF3F2] text-[#B42318]'
										: hasWarning
										? 'bg-[#FFFAEB] text-[#B54708]'
										: 'bg-[#ECFDF3] text-[#067647]'
								}`}
							>
								{hasFailure
									? __('The test failed. Fix the step marked above and run it again.', 'easycommerce')
									: hasWarning
									? __('The test passed with warnings.', 'easycommerce')
									: __('Everything works. Customers can place orders.', 'easycommerce')}
							</div>
						)}

						<div className="flex items-center justify-between gap-4">
							<span className="font-inter text-xs text-ec-placeholder">
								{last
									? `${__('Last run', 'easycommerce')}: ${new Date(last.time * 1000).toLocaleString()} · ${
											last.passed ? __('passed', 'easycommerce') : __('failed', 'easycommerce')
									  }`
									: ''}
							</span>
							<button
								onClick={run}
								disabled={running}
								className="px-5 py-2.5 rounded-md bg-ec-primary text-white font-inter text-sm font-medium disabled:opacity-60"
							>
								{running
									? __('Running…', 'easycommerce')
									: finished
									? __('Run again', 'easycommerce')
									: __('Run test', 'easycommerce')}
							</button>
						</div>
					</div>
				</div>
			)}
		</>
	);
};

export default TestOrder;
