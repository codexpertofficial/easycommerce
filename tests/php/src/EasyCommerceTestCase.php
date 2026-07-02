<?php

namespace EasyCommerce\Tests;

use EasyCommerce\Tests\Factories\EasyCommerceFactory;
use WP_REST_Response;
use WP_REST_Request;
use WP_UnitTestCase;

/**
 * Abstract base class for all EasyCommerce PHPUnit tests.
 *
 * Mirrors dokan-lite/tests/php/src/DokanTestCase.php
 *
 * Provides:
 *  - $this->factory->order  — OrderFactory
 *  - $this->factory->refund — RefundFactory
 *  - DB transaction rollback between tests (via WP_UnitTestCase)
 *  - REST helpers: rest_get(), rest_post(), rest_put(), rest_delete()
 */
abstract class EasyCommerceTestCase extends WP_UnitTestCase {

	/**
	 * EasyCommerce fixture factory.
	 *
	 * @var EasyCommerceFactory
	 */
	protected $factory;

	/**
	 * REST API namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'easycommerce/v1';

	public function set_up(): void {
		parent::set_up();
		$this->factory = new EasyCommerceFactory();
	}

	public function tear_down(): void {
		parent::tear_down();
	}

	// ── REST helpers ──────────────────────────────────────────────────────────

	protected function rest_get( string $route, array $params = [] ): WP_REST_Response {
		return $this->rest_request( 'GET', $route, [], $params );
	}

	protected function rest_post( string $route, array $body = [] ): WP_REST_Response {
		return $this->rest_request( 'POST', $route, $body );
	}

	protected function rest_put( string $route, array $body = [] ): WP_REST_Response {
		return $this->rest_request( 'PUT', $route, $body );
	}

	protected function rest_delete( string $route ): WP_REST_Response {
		return $this->rest_request( 'DELETE', $route );
	}

	private function rest_request( string $method, string $route, array $body = [], array $query = [] ): WP_REST_Response {
		$request = new WP_REST_Request( $method, '/' . $this->namespace . $route );

		if ( ! empty( $query ) ) {
			$request->set_query_params( $query );
		}
		if ( ! empty( $body ) ) {
			$request->set_body_params( $body );
		}

		return rest_get_server()->dispatch( $request );
	}
}
