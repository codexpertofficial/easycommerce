<?php
namespace EasyCommerce\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Request
 *
 * Outbound HTTP helper using WordPress HTTP API.
 *
 * Method signatures:
 *   call( string $method, string $url, array $body = [], array $headers = [], array $opts = [] )
 *   get|post|put|delete|patch|head( string $url, array $body = [], array $headers = [], array $opts = [] )
 *
 * - All inputs that are applicable (body, headers, opts) must be arrays.
 * - By default arrays in $body are auto-encoded to JSON for non-GET requests (set $opts['json'] = false to disable).
 * - For GET requests, $body array is appended as query params.
 * - $headers is an associative array of header => value.
 *
 * Normalized response (always returned; won't call wp_send_json_*):
 *  [
 *    'success' => (bool),            // true for 2xx responses
 *    'status'  => (int),             // HTTP status code (0 on WP_Error)
 *    'content' => (mixed|null),      // JSON payload's `data` key when present, else the decoded JSON, else the raw body
 *    'error'   => (string|null),     // error message on failure ("HTTP <code>", or the WP_Error message)
 *  ]
 *
 * Usage examples:
 *   $this->post( 'https://api.example.com/items', ['name' => 'x'], ['Authorization' => 'Bearer ...'], ['timeout' => 20] );
 *   $this->get( 'https://api.example.com/items', ['search' => 'a'] ); // body appended as query
 */
trait Request {

	/**
	 * Default timeout (seconds) for HTTP calls.
	 *
	 * @var int
	 */
	public $request_timeout = 300;

	/**
	 * Perform an HTTP request.
	 *
	 * @param string $method  HTTP method (GET, POST, PUT, DELETE, PATCH, HEAD).
	 * @param string $url     Full request URL.
	 * @param array  $body    Request body. For GET this will be converted to query args. For non-GET arrays are JSON-encoded by default.
	 * @param array  $headers Associative array of headers (header => value).
	 * @param array  $opts    Additional options (array). Supported keys:
	 *                        - json (bool|null) : null = auto (encode arrays to JSON), true = force JSON, false = disable JSON encoding
	 *                        - timeout (int)    : seconds (overrides $this->request_timeout)
	 *                        - sslverify (bool) : whether to verify SSL (default true)
	 *                        - args (array)     : extra args merged into wp_remote_request() args
	 *
	 * @return array Normalized response structure (see trait docblock).
	 */
	public function call( $method, $url, $body = array(), $headers = array(), $opts = array() ) {
		$method  = strtoupper( (string) $method );
		$url     = (string) $url;
		$body    = is_array( $body ) ? $body : (array) $body;
		$headers = is_array( $headers ) ? $headers : (array) $headers;
		$opts    = is_array( $opts ) ? $opts : (array) $opts;

		// Merge headers provided in $opts['headers'] with explicit $headers param.
		if ( isset( $opts['headers'] ) && is_array( $opts['headers'] ) ) {
			$headers = array_merge( $headers, $opts['headers'] );
		}

		$jsonOpt = array_key_exists( 'json', $opts ) ? $opts['json'] : null; // null = auto
		$timeout = isset( $opts['timeout'] ) ? (int) $opts['timeout'] : $this->request_timeout;
		$ssl     = isset( $opts['sslverify'] ) ? (bool) $opts['sslverify'] : true;
		$extra   = isset( $opts['args'] ) && is_array( $opts['args'] ) ? $opts['args'] : array();

		// If GET and body provided as array, append as query args and clear body.
		if ( 'GET' === $method && ! empty( $body ) ) {
			if ( function_exists( 'add_query_arg' ) ) {
				$url = add_query_arg( $body, $url );
			} else {
				$q   = http_build_query( $body );
				$url = rtrim( $url, '?' ) . ( strpos( $url, '?' ) === false ? '?' : '&' ) . $q;
			}
			$body = array();
		}

		// Decide JSON encoding for non-GET request bodies.
		if ( 'GET' !== $method && ! empty( $body ) ) {
			$should_encode = ( $jsonOpt === true ) || ( $jsonOpt === null );
			if ( $should_encode ) {
				$body = wp_json_encode( $body );
				if ( ! isset( $headers['Content-Type'] ) ) {
					$headers['Content-Type'] = 'application/json; charset=utf-8';
				}
			}
			// if $jsonOpt === false and $body is array, leave it as-is -> WP will form-encode
		}

		// If body is an object and json not explicitly false, encode it.
		if ( is_object( $body ) && $jsonOpt !== false ) {
			$body = wp_json_encode( $body );
			if ( ! isset( $headers['Content-Type'] ) ) {
				$headers['Content-Type'] = 'application/json; charset=utf-8';
			}
		}

		// Normalize header keys to strings
		$norm_headers = array();
		foreach ( $headers as $k => $v ) {
			$norm_headers[ (string) $k ] = $v;
		}

		$request_args = array_merge(
			array(
				'method'    => $method,
				'headers'   => $norm_headers,
				'body'      => $body,
				'timeout'   => $timeout,
				'sslverify' => $ssl,
			),
			$extra
		);

		$response = wp_remote_request( $url, $request_args );

		// Handle WP errors gracefully.
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'status'  => 0,
				'content' => null,
				'error'   => $response->get_error_message(),
			);
		}

		$status  = (int) wp_remote_retrieve_response_code( $response );
		$raw     = wp_remote_retrieve_body( $response );
		$hdrs    = wp_remote_retrieve_headers( $response );
		$decoded = $this->maybe_json_decode( $raw );

		return array(
			'success' => ( $status >= 200 && $status < 300 ),
			'status'  => $status,
			'content' => ( $decoded !== null ) ? ( $decoded['data'] ?? $decoded ) : $raw,
			'error'   => ( $status >= 200 && $status < 300 ) ? null : sprintf( 'HTTP %d', $status ),
		);
	}

	/**
	 * Convenience GET.
	 *
	 * Appends $body as query args (if non-empty array).
	 *
	 * @param string $url     URL.
	 * @param array  $body    Query args as associative array.
	 * @param array  $headers Headers array.
	 * @param array  $opts    Additional options (see call()).
	 * @return array Normalized response.
	 */
	public function get( $url, $body = array(), $headers = array(), $opts = array() ) {
		return $this->call( 'GET', $url, $body, $headers, $opts );
	}

	/**
	 * Convenience POST.
	 *
	 * @param string $url     URL.
	 * @param array  $body    Body as array (auto JSON-encoded by default) or string.
	 * @param array  $headers Headers array.
	 * @param array  $opts    Additional options (see call()).
	 * @return array Normalized response.
	 */
	public function post( $url, $body = array(), $headers = array(), $opts = array() ) {
		return $this->call( 'POST', $url, $body, $headers, $opts );
	}

	/**
	 * Convenience PUT.
	 *
	 * @param string $url     URL.
	 * @param array  $body    Body as array (auto JSON-encoded by default) or string.
	 * @param array  $headers Headers array.
	 * @param array  $opts    Additional options (see call()).
	 * @return array Normalized response.
	 */
	public function put( $url, $body = array(), $headers = array(), $opts = array() ) {
		return $this->call( 'PUT', $url, $body, $headers, $opts );
	}

	/**
	 * Convenience DELETE.
	 *
	 * Note: some APIs accept body for DELETE; pass it if needed.
	 *
	 * @param string $url     URL.
	 * @param array  $body    Optional body as array or string.
	 * @param array  $headers Headers array.
	 * @param array  $opts    Additional options (see call()).
	 * @return array Normalized response.
	 */
	public function delete( $url, $body = array(), $headers = array(), $opts = array() ) {
		return $this->call( 'DELETE', $url, $body, $headers, $opts );
	}

	/**
	 * Convenience PATCH.
	 *
	 * @param string $url     URL.
	 * @param array  $body    Body as array (auto JSON-encoded by default) or string.
	 * @param array  $headers Headers array.
	 * @param array  $opts    Additional options (see call()).
	 * @return array Normalized response.
	 */
	public function patch( $url, $body = array(), $headers = array(), $opts = array() ) {
		return $this->call( 'PATCH', $url, $body, $headers, $opts );
	}

	/**
	 * Convenience HEAD.
	 *
	 * @param string $url     URL.
	 * @param array  $body    Ignored for HEAD (kept for consistent signature).
	 * @param array  $headers Headers array.
	 * @param array  $opts    Additional options (see call()).
	 * @return array Normalized response.
	 */
	public function head( $url, $body = array(), $headers = array(), $opts = array() ) {
		return $this->call( 'HEAD', $url, $body, $headers, $opts );
	}

	/**
	 * Attempt to JSON decode a raw response body.
	 *
	 * Returns decoded value when valid JSON, otherwise null.
	 *
	 * @param string $raw Raw response body.
	 * @return mixed|null Decoded JSON (array/object) or null when not JSON.
	 */
	protected function maybe_json_decode( $raw ) {
		if ( ! is_string( $raw ) || '' === $raw ) {
			return null;
		}

		$trim = ltrim( $raw );
		if ( strpos( $trim, '{' ) !== 0 && strpos( $trim, '[' ) !== 0 ) {
			return null;
		}

		$decoded = json_decode( $raw, true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			return $decoded;
		}

		return null;
	}
}
