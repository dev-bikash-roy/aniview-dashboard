<?php
namespace AVD;
if ( ! defined( 'ABSPATH' ) ) { exit; }

use WP_REST_Server;

use WP_REST_Request;

class REST {
    public static function register() {
        register_rest_route( 'av/v1', '/kpi', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [ __CLASS__, 'kpi' ],
            'permission_callback' => function() { return current_user_can( 'manage_options' ); },
        ] );

        register_rest_route( 'av/v1', '/datasources', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [ __CLASS__, 'get_datasources' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ] );

        register_rest_route( 'av/v1', '/datasources', [
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => [ __CLASS__, 'create_datasource' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ] );

        register_rest_route( 'av/v1', '/datasources/(?P<id>\d+)', [
            'methods'  => WP_REST_Server::EDITABLE,
            'callback' => [ __CLASS__, 'update_datasource' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ] );

        register_rest_route( 'av/v1', '/datasources/(?P<id>\d+)', [
            'methods'  => WP_REST_Server::DELETABLE,
            'callback' => [ __CLASS__, 'delete_datasource' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
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

    public static function check_permissions( $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new \WP_Error( 'rest_nonce', 'Invalid nonce', [ 'status' => 403 ] );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            return new \WP_Error( 'rest_forbidden', 'Forbidden', [ 'status' => 403 ] );
        }
        return true;
    }

    public static function get_datasources() {
        global $wpdb;
        $table = $wpdb->prefix . 'av_datasources';
        return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC" );
    }

    public static function create_datasource( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'av_datasources';

        $data = [
            'name'      => sanitize_text_field( $request['name'] ),
            'type'      => sanitize_text_field( $request['type'] ),
            'config'    => wp_json_encode( $request->get_param( 'config' ) ),
            'is_active' => $request->get_param( 'is_active' ) ? 1 : 0,
        ];

        $wpdb->insert( $table, $data );
        $id = $wpdb->insert_id;

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id=%d", $id ) );
    }

    public static function update_datasource( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'av_datasources';
        $id    = (int) $request['id'];

        $data = [];
        if ( $request->has_param( 'name' ) ) {
            $data['name'] = sanitize_text_field( $request['name'] );
        }
        if ( $request->has_param( 'type' ) ) {
            $data['type'] = sanitize_text_field( $request['type'] );
        }
        if ( $request->has_param( 'config' ) ) {
            $data['config'] = wp_json_encode( $request->get_param( 'config' ) );
        }
        if ( $request->has_param( 'is_active' ) ) {
            $data['is_active'] = $request->get_param( 'is_active' ) ? 1 : 0;
        }

        if ( ! empty( $data ) ) {
            $wpdb->update( $table, $data, [ 'id' => $id ] );
        }

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id=%d", $id ) );
    }

    public static function delete_datasource( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'av_datasources';
        $id    = (int) $request['id'];
        $wpdb->delete( $table, [ 'id' => $id ] );
        return [ 'deleted' => true ];
    }
}

add_action( 'rest_api_init', [ '\AVD\REST', 'register' ] );
