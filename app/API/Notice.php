<?php
namespace EasyCommerce\API;

defined('ABSPATH') || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Notice as Notice_Model;

class Notice extends API {

	/**
	 * List notices via REST API, optionally filtered by type and screen.
	 *
	 * @param  WP_REST_Request $request Request object containing optional 'type' and 'screen' params.
	 * @return WP_REST_Response Response with 'notices' array and 'total' count.
	 */
	public function list( $request ) {
		$type	= $request->get_param( 'type' );
		$screen = $request->get_param( 'screen' );
		$limit	= $request->get_param( 'limit' );

		$notices = Notice_Model::list( $type, $screen, $limit );

		$this->response_success(
			array(
				'notices'	=> $notices,
				'total'		=> count( $notices ),
			),
			200
		);
	}

    public function dismiss( $request ) {
        $id = $request->get_param( 'id' );

        $notice_model = new Notice_Model;
        
        if( ! $notice_model->remove( $id ) ) {
            $this->response_error(
                array(
                    'message'   => __( 'Notice not dismissed', 'easycommerce' ),
                )
            );
        }

        $this->response_success(
            array(
                'message'   => __( 'Notice dismissed', 'easycommerce' ),
            )
        );
    }
}