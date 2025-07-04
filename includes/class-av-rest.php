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

        register_rest_route( 'av/v1', '/timeseries', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [ __CLASS__, 'timeseries' ],
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

    public static function timeseries( $request ) {
        return [
            [ 'label' => '2025-07-02 00h', 'value' => 0.2 ],
            [ 'label' => '2025-07-02 01h', 'value' => 0.3 ],
            [ 'label' => '2025-07-02 02h', 'value' => 0.1 ],
        ];
    }
}

add_action( 'rest_api_init', [ '\AVD\REST', 'register' ] );
