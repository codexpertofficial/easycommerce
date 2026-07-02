<?php
namespace EasyCommerce\Abstracts;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Database;
use EasyCommerce\Services\AI as AI_Service;
use EasyCommerce\Traits\Rest;
use EasyCommerce\Traits\Auth;

abstract class Agent {

	use Rest;
	use Auth;

	protected $ai;
	protected $db;

	/**
	 * Usage-log type for this agent. Subclasses override (copilot, shopping_agent).
	 *
	 * @var string
	 */
	protected $log_type = 'agent';

	public function __construct() {
		$this->ai = new AI_Service();
		$this->db = new Database( 'agent_sessions' );
	}

	public function handle( $request ) {
		$cost    = 2;
		$credits = easycommerce_get_ai_credits();

		if ( $credits < $cost ) {
			$this->response_error( array( 'message' => __( 'No AI credits available. Please upgrade or use your own API key.', 'easycommerce' ) ), 402 );
			return;
		}

		easycommerce_deduct_ai_credits( $cost, $credits );

		$message    = sanitize_text_field( $request->get_param( 'message' ) );
		$session_id = sanitize_text_field( $request->get_param( 'session_id' ) );

		if ( empty( $message ) ) {
			$this->response_error( array( 'message' => __( '`message` is required.', 'easycommerce' ) ) );
			return;
		}

		if ( empty( $session_id ) ) {
			$session_id = wp_generate_uuid4();
		}

		$result = $this->run_message( $session_id, $message );

		wp_send_json( $this->build_response( $result, $session_id ) );
	}

	protected function build_response( array $result, string $session_id ): array {
		return array(
			'session_id' => $session_id,
			'reply'      => $result['reply'],
		);
	}

	public function run_message( string $session_id, string $message ): array {
		$history   = $this->load_session( $session_id );
		$history[] = array( 'role' => 'user', 'content' => $message );

		$reply = $this->run_agent( $history );

		$history[] = array( 'role' => 'assistant', 'content' => $reply );
		$this->save_session( $session_id, $history );

		return array( 'reply' => $reply );
	}

	protected function run_agent( array &$history ): string {
		$tools         = $this->get_tools();
		$system_prompt = $this->get_system_prompt();

		// A single user question spans several hub turns. Track the question and the
		// total credits the turns consumed, then log it as ONE client usage row.
		$question     = $this->last_user_message( $history );
		$total_credit = 0;
		$reply        = __( 'I was unable to complete your request. Please try again.', 'easycommerce' );

		for ( $i = 0; $i < 10; $i++ ) {
			$response = $this->ai->agent( $history, $tools, $system_prompt );

			if ( ! is_object( $response ) || empty( $response->success ) ) {
				$reply = __( 'Sorry, I had trouble processing your request. Please try again.', 'easycommerce' );
				break;
			}

			$total_credit += isset( $response->data->credit ) ? (int) $response->data->credit : 0;

			$finish_reason = $response->data->finish_reason ?? 'stop';
			$msg           = $response->data->message;

			if ( $finish_reason === 'tool_calls' || ! empty( $msg->tool_calls ) ) {
				$history[] = json_decode( wp_json_encode( $msg ), true );

				foreach ( $msg->tool_calls as $tool_call ) {
					$fn_name = $tool_call->function->name;
					$fn_args = json_decode( $tool_call->function->arguments, true ) ?: array();
					$result  = $this->dispatch_tool( $fn_name, $fn_args );

					$history[] = array(
						'role'         => 'tool',
						'tool_call_id' => $tool_call->id,
						'content'      => wp_json_encode( $result ),
					);
				}

				continue;
			}

			$reply = $msg->content ?? '';
			break;
		}

		$this->ai->log_conversation( $this->log_type, $question, $reply, $total_credit );

		return $reply;
	}

	/**
	 * The most recent user message in a history array.
	 *
	 * @param array $history
	 * @return string
	 */
	protected function last_user_message( array $history ): string {
		foreach ( array_reverse( $history ) as $message ) {
			if ( ( $message['role'] ?? '' ) === 'user' && ! empty( $message['content'] ) ) {
				return (string) $message['content'];
			}
		}
		return '';
	}

	protected function load_session( string $session_id ): array {
		$row = $this->db->get_row( array( 'session_id' => $session_id ) );
		if ( ! $row ) {
			return array();
		}
		return json_decode( $row->history, true ) ?: array();
	}

	protected function save_session( string $session_id, array $history ): void {
		if ( count( $history ) > 30 ) {
			$history = array_slice( $history, -30 );
			foreach ( $history as $idx => $msg ) {
				if ( ( $msg['role'] ?? '' ) === 'user' ) {
					$history = array_values( array_slice( $history, $idx ) );
					break;
				}
			}
		}

		$existing = $this->db->get_row( array( 'session_id' => $session_id ) );
		$data     = array(
			'history'    => wp_json_encode( $history ),
			'updated_at' => current_time( 'mysql' ),
		);

		if ( $existing ) {
			$this->db->update_row( $existing->id, $data );
		} else {
			$this->db->insert_row( array_merge( array( 'session_id' => $session_id ), $data ) );
		}
	}

	abstract protected function dispatch_tool( string $name, array $args ): array;
	abstract protected function get_tools(): array;
	abstract protected function get_system_prompt(): string;
}
