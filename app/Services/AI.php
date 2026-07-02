<?php
namespace EasyCommerce\Services;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Request;
use EasyCommerce\Traits\Cleaner;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Attribute;
use EasyCommerce\Models\Attribute_Value;
use WP_Block_Type_Registry as Registry;

/**
 * Class AI
 * Handles AI related operations for the EasyCommerce plugin.
 *
 * Provides integration with external AI services for features like:
 * - Product description generation
 * - Image generation and editing
 * - Report analysis from natural language queries
 * - Product attribute generation
 * - Spelling correction
 * - Block template design
 *
 * @package EasyCommerce\Services
 * @since 1.16
 */
class AI {

	use Cleaner;
	use Request;

	/**
	 * HTTP headers to include in API requests.
	 *
	 * @since 1.16
	 * @var array
	 */
	private $headers = array();

	/**
	 * Base URL for the AI API endpoint.
	 *
	 * @since 1.16
	 * @var string
	 */
	private $api_base;
	
	/**
	 * Constructor.
	 *
	 * Initializes the AI service. No initialization needed as credentials
	 * are fetched dynamically from WordPress options.
	 *
	 * @since 1.16
	 */
	public function __construct() {}

	/**
	 * Checks if the AI service is ready to use.
	 *
	 * Verifies that API credentials are configured in WordPress options.
	 *
	 * @since 1.16
	 *
	 * @return bool True if API credentials are configured, false otherwise.
	 */
	private function prepare() {
		return ! empty( get_option( 'easycommerce_api' )->email ) || apply_filters( 'easycommerce-pro_licensed', false );
	}

	/**
	 * Makes an API call to the external AI service.
	 *
	 * Sends a request to the EasyCommerce AI API endpoint with the provided
	 * data and configuration. Includes API credentials from WordPress options.
	 *
	 * @since 1.16
	 *
	 * @param string $path   The API endpoint path (e.g., 'ask', 'write', 'paint').
	 * @param array  $data   The data to send with the request.
	 * @param string $method The HTTP method to use. Default 'POST'.
	 * @param array  $config  Optional. Additional configuration options.
	 *                        - timeout: Request timeout in seconds (default 60)
	 *                        - ssl: Enable SSL verification (default true)
	 *                        - headers: Additional headers to include.
	 *
	 * @return object|\WP_Error The response from the API as an object, or WP_Error on failure.
	 */
	private function call( $path, $data, $method = 'POST', $config = [] ) {
		$api = get_option( 'easycommerce_api' );

		if( empty( $api->email ) ) {
			return [
				'success' => false,
				'message' => __( 'You don\'t have your EasyCommerce API connected!', 'easycommerce' )
			];
		}

		$config = array_merge( [ 'timeout' => 60, 'ssl' => true, 'headers' => [ 'email' => $api->email ] ], $config );

		$args = array_merge(
			array(
				'method'    => $method,
				'body'      => $data,
				'headers'   => $config['headers'],
				'timeout'   => $config['timeout'],
				'sslverify' => $config['ssl'],
			),
			$data
		);

		$request = wp_remote_request( easycommerce_dev_store( "/wp-json/easycommerce/v1/hub/ai/{$path}" ), $args );

		$response = json_decode( wp_remote_retrieve_body( $request ) );

		// The hub is the authoritative credit ledger. Sync the local balance to the
		// `remaining` it reports so the optimistic pre-deduction self-corrects (and
		// failed calls, which the hub never charges, get refunded on the next call).
		if ( isset( $response->data->remaining ) && is_numeric( $response->data->remaining ) ) {
			easycommerce_ai_update( array( 'remaining' => (int) $response->data->remaining ) );
		}

		// Record one usage row per successful AI request. Single-shot features log
		// here. `agent` is excluded: one user action (a Copilot question, a Smart
		// Search) fans out into several agent turns, and the end user should see one
		// row - so the conversation boundary logs it instead (Abstracts\Agent and
		// search_intent call log_conversation()). The hub still bills per turn.
		if ( ! empty( $response->success ) && 'agent' !== $path ) {
			$this->log_usage( $path, $data, $response );
		}

		return $response;
	}

	/**
	 * Insert a row into the AI usage log.
	 *
	 * @param string $type     The hub path (write, paint, enhance, agent, ...).
	 * @param array  $data      Request body sent to the hub.
	 * @param object $response  Decoded hub response.
	 */
	private function log_usage( $type, $data, $response ) {
		$credit = isset( $response->data->credit ) ? (int) $response->data->credit : $this->default_credit( $type );

		$this->insert_log(
			$type,
			$this->usage_input( $type, is_array( $data ) ? $data : array() ),
			$this->usage_output( $response ),
			$credit
		);
	}

	/**
	 * Log one conversational AI use (Copilot / shopping agent / smart search) as a
	 * single row, even though it spanned several hub turns. The caller provides the
	 * question, final answer, and the total credits the turns consumed.
	 *
	 * @param string $type   Log type (copilot, shopping_agent, search, ...).
	 * @param string $input  User question.
	 * @param string $output Final answer.
	 * @param int    $credit Total credits consumed across the conversation.
	 */
	public function log_conversation( $type, $input, $output, $credit ) {
		$this->insert_log( $type, mb_substr( (string) $input, 0, 10000 ), mb_substr( (string) $output, 0, 10000 ), (int) $credit );
	}

	/**
	 * Default per-type credit cost when the hub did not report one.
	 *
	 * @param string $type
	 * @return int
	 */
	private function default_credit( $type ) {
		$defaults = array(
			'write'               => 1,
			'generate_attributes' => 1,
			'builder'             => 3,
			'fixspell'            => 1,
			'agent'               => 1,
			'removebg'            => 50,
			'paint'               => 50,
			'enhance'             => 50,
		);
		return $defaults[ $type ] ?? 1;
	}

	/**
	 * Insert a row into the AI usage log.
	 *
	 * @param string $type
	 * @param string $input
	 * @param string $output
	 * @param int    $credit
	 */
	private function insert_log( $type, $input, $output, $credit ) {
		$this->ensure_log_table();

		$db = new Database( 'ai_logs' );
		$db->insert_row(
			array(
				'user_id' => get_current_user_id() ?: null,
				'type'    => $type,
				'input'   => $input,
				'output'  => $output,
				'credit'  => (int) $credit,
			)
		);
	}

	/**
	 * Extract a human-readable prompt/question from the request body.
	 *
	 * @param string $type
	 * @param array  $data
	 * @return string
	 */
	private function usage_input( $type, $data ) {
		$input = '';

		if ( 'agent' === $type ) {
			// The user's question is the last `user` message in the history.
			$messages = json_decode( $data['messages'] ?? '[]', true );
			if ( is_array( $messages ) ) {
				foreach ( array_reverse( $messages ) as $message ) {
					if ( ( $message['role'] ?? '' ) === 'user' && ! empty( $message['content'] ) ) {
						$input = $message['content'];
						break;
					}
				}
			}
			if ( '' === $input ) {
				$input = $data['system_prompt'] ?? '';
			}
		} else {
			foreach ( array( 'prompt', 'phrase', 'query', 'product' ) as $key ) {
				if ( ! empty( $data[ $key ] ) ) {
					$input = is_scalar( $data[ $key ] ) ? $data[ $key ] : wp_json_encode( $data[ $key ] );
					break;
				}
			}
			if ( '' === $input ) {
				$input = wp_json_encode( $data );
			}
		}

		return mb_substr( (string) $input, 0, 10000 );
	}

	/**
	 * Extract the assistant answer (or an image marker) from the hub response.
	 *
	 * @param object $response
	 * @return string
	 */
	private function usage_output( $response ) {
		$data = $response->data ?? null;

		if ( ! $data ) {
			return '';
		}

		// Image endpoints return a (base64) URL - store a marker, not the blob.
		if ( isset( $data->url ) ) {
			return '[image]';
		}

		if ( isset( $data->message ) && is_string( $data->message ) ) {
			return mb_substr( $data->message, 0, 10000 );
		}

		// Agent turns return a message object; the answer is its content.
		if ( isset( $data->message->content ) && is_string( $data->message->content ) ) {
			return mb_substr( $data->message->content, 0, 10000 );
		}

		return '';
	}

	/**
	 * Create the ai_logs table on first use (option-guarded) so the usage log
	 * works without waiting for a full plugin reinstall. The column definition
	 * lives in app/Config/tables.php (single source of truth).
	 */
	private function ensure_log_table() {
		// Bump when the ai_logs columns change; dbDelta then adds the new columns
		// to an already-created table.
		$schema_version = '2';

		if ( get_option( 'easycommerce_ai_logs_schema' ) === $schema_version ) {
			return;
		}

		global $easycommerce_tables;
		if ( empty( $easycommerce_tables['ai_logs'] ) ) {
			require_once EASYCOMMERCE_PLUGIN_DIR . 'app/Config/tables.php';
		}

		if ( ! empty( $easycommerce_tables['ai_logs'] ) ) {
			$def = $easycommerce_tables['ai_logs'];
			$db  = new Database( 'ai_logs' );
			$db->create_table( $def['columns'], $def['options'] ?? array() );
			update_option( 'easycommerce_ai_logs_schema', $schema_version );
		}
	}

	/**
	 * Runs an agentic chat completion turn with tool/function-calling support.
	 *
	 * Sends the full conversation history and tool definitions to the AI
	 * server. The response may contain tool_calls that the caller must
	 * execute and loop on until a final text reply is returned.
	 *
	 * @param array  $messages      Full conversation history [{role, content}, ...].
	 * @param array  $tools         Tool definitions in OpenAI function-calling format.
	 * @param string $system_prompt System prompt injected on the server side.
	 *
	 * @return object Response from the AI service.
	 */
	public function agent( array $messages, array $tools, string $system_prompt = '' ) {
		if ( ! $this->prepare() ) {
			return (object) array(
				'success' => false,
				'data'    => (object) array( 'message' => __( 'Service is not ready: Missing API credentials', 'easycommerce' ) ),
			);
		}

		return $this->call( 'agent', array(
			'messages'      => wp_json_encode( $messages ),
			'tools'         => wp_json_encode( $tools ),
			'system_prompt' => $system_prompt,
		) );
	}

	/**
	 * Generates product description or summary using AI.
	 *
	 * Creates compelling product descriptions based on the product name,
	 * optional custom prompt, and desired length.
	 *
	 * @since 1.16
	 *
	 * @param string $product The name of the product to generate description for.
	 * @param string $prompt  Optional. Custom instructions to customize the output.
	 *                        Default empty string.
	 * @param string $length  Optional. Desired length of output. Accepts 'short' or 'long'.
	 *                        Default 'short'.
	 *
	 * @return array {
	 *     Response from the AI service.
	 *
	 *     @type bool   $success Whether the request was successful.
	 *     @type string $message The generated description or error message.
	 * }
	 */
	public function write( $product, $prompt = '', $length = 'short' ) {

		// if the AI credentials are set
		if ( ! $this->prepare() ) {
			return [
				'success' => false,
				'message' => __( 'Service is not ready: Missing API credentials', 'easycommerce' )
			];
		}

		// if we have enough credits
		$cost = 1;
		if ( ( $credits = easycommerce_get_ai_credits() ) < $cost ) {
			return [
				'success' => false,
				'message' => __( 'No AI credits available. Please upgrade to the Pro plan or use your own API key.', 'easycommerce' )
			];
		}

		// Deduct one credit for description
		easycommerce_deduct_ai_credits( $cost, $credits );

		return $this->call( 'write', [ 'product' => $product, 'prompt' => $prompt, 'length' => $length ] );
	}

	/**
	 * Generates product attributes based on product name.
	 *
	 * Uses AI to create relevant product attributes (e.g., Color, Size, Material)
	 * along with their possible values based on the product name.
	 *
	 * @since 1.16
	 *
	 * @param string $product        The name of the product to generate attributes for.
	 * @param string $num_attributes Optional. Number of attributes to generate (e.g., "1-3").
	 *                                Default "1-3".
	 * @param string $num_values     Optional. Number of values per attribute (e.g., "2-5").
	 *                                Default "2-5".
	 *
	 * @return array {
	 *     Response from the AI service.
	 *
	 *     @type bool   $success Whether the request was successful.
	 *     @type string $message The generated attributes or error message.
	 * }
	 */
	public function generate_attributes( $product, $num_attributes = "1-3", $num_values = "2-5" ) {

		// if the AI credentials are set
		if ( ! $this->prepare() ) {
			return [
				'success' => false,
				'message' => __( 'Service is not ready: Missing API credentials', 'easycommerce' )
			];
		}

		// if we have enough credits
		$cost = 1;
		if ( ( $credits = easycommerce_get_ai_credits() ) < $cost ) {
			return [
				'success' => false,
				'message' => __( 'No AI credits available. Please upgrade to the Pro plan or use your own API key.', 'easycommerce' )
			];
		}

		// Deduct one credit for description
		easycommerce_deduct_ai_credits( $cost, $credits );

		return $this->call( 'generate_attributes', [ 'product' => $product, 'num_attributes' => $num_attributes, 'num_values' => $num_values ] );
	}

	/**
	 * Designs a block template using AI.
	 *
	 * Generates a Gutenberg block template based on the provided prompt
	 * and optionally based on a specific product.
	 *
	 * @since 1.16
	 *
	 * @param string $prompt  The instruction describing the desired block design.
	 * @param string $product Optional. The product name to base the design on.
	 *                        Default empty string.
	 *
	 * @return array {
	 *     Response from the AI service.
	 *
	 *     @type bool   $success Whether the request was successful.
	 *     @type string $message The generated block template or error message.
	 * }
	 */
	public function design( $prompt, $product = '' ) {

		// if the AI credentials are set
		if ( ! $this->prepare() ) {
			return [
				'success' => false,
				'message' => __( 'Service is not ready: Missing API credentials', 'easycommerce' )
			];
		}

		// if we have enough credits
		$cost = 3;
		if ( ( $credits = easycommerce_get_ai_credits() ) < $cost ) {
			return [
				'success' => false,
				'message' => __( 'No AI credits available. Please upgrade to the Pro plan or use your own API key.', 'easycommerce' )
			];
		}

		// Deduct credits for the template build
		easycommerce_deduct_ai_credits( $cost, $credits );

		return $this->call( 'builder', [ 'prompt' => $prompt, 'product' => $product ] );
	}

	/**
	 * Fixes misspelled words in a phrase.
	 *
	 * Uses AI to correct spelling errors in the given phrase while
	 * considering the context from existing product names in the store.
	 *
	 * @since 1.16
	 *
	 * @param string $phrase The phrase containing potentially misspelled words.
	 *
	 * @return array {
	 *     Response from the AI service.
	 *
	 *     @type bool   $success Whether the request was successful.
	 *     @type string $message The corrected phrase or error message.
	 * }
	 */
	public function fix_spelling( $phrase ) {

		// if the AI credentials are set
		if ( ! $this->prepare() ) {
			return [
				'success' => false,
				'message' => __( 'Service is not ready: Missing API credentials', 'easycommerce' )
			];
		}

		// if we have enough credits
		$cost = 1;
		if ( ( $credits = easycommerce_get_ai_credits() ) < $cost ) {
			return [
				'success' => false,
				'message' => __( 'No AI credits available. Please upgrade to the Pro plan or use your own API key.', 'easycommerce' )
			];
		}

		// Deduct one credit for description
		easycommerce_deduct_ai_credits( $cost, $credits );

		return $this->call( 'fixspell', [ 'phrase' => $phrase ] );
	}

	/**
	 * Generates an image based on a text prompt.
	 *
	 * Uses AI image generation to create a new image based on the provided
	 * text description. This operation costs 50 AI credits.
	 *
	 * @since 1.16
	 *
	 * @param string $prompt   Optional. The text description of the image to generate.
	 *                         Default empty string.
	 * @param string $size     Optional. The dimensions of the image. Accepts '1024x1024',
	 *                         '1024x1792', or '1792x1024'. Default '1024x1024'.
	 * @param string $quality  Optional. The quality of the image. Accepts 'standard' or
	 *                         'hd'. Default 'standard'.
	 * @param mixed  $product   Optional. Product context for the image generation.
	 *                         Default null.
	 *
	 * @return array {
	 *     Response from the AI service.
	 *
	 *     @type bool   $success Whether the request was successful.
	 *     @type string $message The response message or error message.
	 *     @type string $url     The URL of the generated image (if successful).
	 * }
	 */
	public function draw( $prompt = '', $size = '1024x1024', $quality = 'standard', $product = null ) {

		// if the AI credentials are set
		if ( ! $this->prepare() ) {
			return [
				'success' => false,
				'message' => __( 'Service is not ready: Missing API credentials', 'easycommerce' )
			];
		}

		// if we have enough credits
		$cost = 50;
		if ( ( $credits = easycommerce_get_ai_credits() ) < $cost ) {
			return [
				'success' => false,
				'message' => __( 'No AI credits available. Please upgrade to the Pro plan or use your own API key.', 'easycommerce' )
			];
		}

		// Deduct credits for the image generation
		easycommerce_deduct_ai_credits( $cost, $credits );

		return $this->call( 'paint', [ 'prompt' => $prompt, 'size' => $size, 'quality' => $quality, 'product' => $product ] );
	}

	/**
	 * Edits an existing image based on a text prompt.
	 *
	 * Uses AI image editing to modify an existing image according to the
	 * provided text description. This operation costs 50 AI credits.
	 *
	 * @since 1.16
	 *
	 * @param string $image   The image to edit (URL or base64 encoded).
	 * @param string $prompt  The text description of how to edit the image.
	 * @param string $size     Optional. The dimensions of the output image.
	 *                         Accepts '1024x1024', '1024x1792', or '1792x1024'.
	 *                         Default '1024x1024'.
	 * @param string $quality  Optional. The quality of the output image.
	 *                         Accepts 'standard' or 'hd'. Default 'standard'.
	 *
	 * @return array {
	 *     Response from the AI service.
	 *
	 *     @type bool   $success Whether the request was successful.
	 *     @type string $message The response message or error message.
	 *     @type string $url     The URL of the edited image (if successful).
	 * }
	 */
	public function edit_image( $image, $prompt, $size = '1024x1024', $quality = 'standard' ) {

		// if the AI credentials are set
		if ( ! $this->prepare() ) {
			return [
				'success' => false,
				'message' => __( 'Service is not ready: Missing API credentials', 'easycommerce' )
			];
		}

		// if we have enough credits
		$cost = 50;
		if ( ( $credits = easycommerce_get_ai_credits() ) < $cost ) {
			return [
				'success' => false,
				'message' => __( 'No AI credits available. Please upgrade to the Pro plan or use your own API key.', 'easycommerce' )
			];
		}

		// Deduct credits for the image edit
		easycommerce_deduct_ai_credits( $cost, $credits );

		return $this->call( 'enhance', [ 'image' => $image, 'prompt' => $prompt, 'size' => $size, 'quality' => $quality ] );
	}

	/**
	 * Extracts a product search keyword from a natural-language query.
	 *
	 * Passes the real product catalog to the AI so it can pick the closest
	 * matching product name or keyword. Returns null when the query does not
	 * clearly map to any known product (e.g. profanity, nonsense, off-topic).
	 *
	 * @param string $query         The raw search string entered by the user.
	 * @param array  $product_titles All product titles from the store.
	 *
	 * @return array|null Associative array with 'search' key, or null on failure / no match.
	 */
	public function search_intent( string $query, array $product_titles = [] ): ?array {

		if ( ! $this->prepare() ) {
			return null;
		}

		$cost = 1;
		if ( ( $credits = easycommerce_get_ai_credits() ) < $cost ) {
			return null;
		}

		$tool = array(
			'type'     => 'function',
			'function' => array(
				'name'        => 'extract_search_filters',
				'description' => 'Extract a product search keyword from a natural language query. Only call this tool when the query clearly relates to a product in the catalog. Pass an empty string for `search` if the query is unrelated, offensive, or has no matching product.',
				'parameters'  => array(
					'type'       => 'object',
					'properties' => array(
						'search' => array(
							'type'        => 'string',
							'description' => 'The best matching product keyword from the catalog. Fix typos and translate natural-language intent (e.g. "keep money" → "wallet"). Use an empty string if no product matches the query.',
						),
					),
					'required'   => array( 'search' ),
				),
			),
		);

		$catalog_context = '';
		if ( ! empty( $product_titles ) ) {
			$listed          = array_slice( $product_titles, 0, 200 );
			$catalog_context = "\n\nAvailable products:\n- " . implode( "\n- ", $listed ) . "\n\nYou MUST pick a keyword that matches one of these products. If the query does not relate to any product (e.g. it is gibberish, profanity, or clearly off-topic), set `search` to an empty string — do NOT guess.";
		}

		$system_prompt = 'You are a product search assistant for an online store. Your job is to map user search queries to real product names in the catalog. Fix typos and understand natural language intent, but ONLY when the query genuinely relates to a product. If the query is nonsense, offensive, or unrelated to any available product, set the `search` field to an empty string.' . $catalog_context . ' You MUST call the extract_search_filters tool.';

		$messages = array(
			array( 'role' => 'user', 'content' => sanitize_text_field( $query ) ),
		);

		$response = $this->agent( $messages, array( $tool ), $system_prompt );

		if ( empty( $response->success ) || ( $response->data->finish_reason ?? '' ) !== 'tool_calls' ) {
			return null;
		}

		foreach ( $response->data->message->tool_calls ?? array() as $call ) {
			if ( ( $call->function->name ?? '' ) !== 'extract_search_filters' ) {
				continue;
			}

			$args = json_decode( $call->function->arguments ?? '{}', true );

			if ( ! is_array( $args ) ) {
				return null;
			}

			$search = sanitize_text_field( $args['search'] ?? '' );

			if ( empty( $search ) ) {
				return null;
			}

			// One search = one usage row (its single agent turn is not auto-logged).
			$this->log_conversation( 'search', $query, $search, isset( $response->data->credit ) ? (int) $response->data->credit : $cost );

			easycommerce_deduct_ai_credits( $cost, $credits );

			return array( 'search' => $search );
		}

		return null;
	}
}