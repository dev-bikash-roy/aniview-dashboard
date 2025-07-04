<?php
namespace AVD;
if ( ! defined( 'ABSPATH' ) ) { exit; }

use WP_REST_Server;

class REST {
    public static function register() {
        register_rest_route( 'av/v1', '/kpi', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [ __CLASS__, 'kpi' ],
            'permission_callback' => function() { return current_user_can( 'manage_options' ); },
        ] );
    }

    public static function kpi() {
        return [
            'inventory'       => 79,
            'impression'      => 11,
            'revenue'         => 0.02,
            'cpm'             => 1.41,
            'ctr'             => 0,
            'completion_rate' => 54.55,
        ];
    }
}

add_action( 'rest_api_init', [ '\AVD\REST', 'register' ] );
