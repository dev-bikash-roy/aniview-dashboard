<?php
namespace AVD;
if ( ! defined( 'ABSPATH' ) ) { exit; }

use WP_REST_Server;
use WP_REST_Request;

class REST {

    public static function register() {
        // KPI
        register_rest_route( 'av/v1', '/kpi', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'kpi' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ] );

        // Timeseries
        register_rest_route( 'av/v1', '/timeseries', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'timeseries' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
            'args' => [
                'metric' => [ 'required' => true ],
                'from'   => [ 'required' => true ],
                'to'     => [ 'required' => true ],
                'gran'   => [ 'default' => 'hour' ],
            ],
        ] );

        // Ranked dimensions (country, os, channel)
        register_rest_route( 'av/v1', '/rank', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'rank' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
            'args' => [
                'dimension' => [ 'required' => true ],
                'start'     => [],
                'end'       => [],
            ],
        ] );

        // Data Sources CRUD
        register_rest_route( 'av/v1', '/datasources', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_datasources' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ] );
        register_rest_route( 'av/v1', '/datasources', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ __CLASS__, 'create_datasource' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ] );
        register_rest_route( 'av/v1', '/datasources/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => [ __CLASS__, 'update_datasource' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ] );
        register_rest_route( 'av/v1', '/datasources/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => [ __CLASS__, 'delete_datasource' ],
            'permission_callback' => [ __CLASS__, 'check_permissions' ],
        ] );
    }

    public static function check_permissions( WP_REST_Request $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new \WP_Error( 'rest_nonce', 'Invalid nonce', [ 'status' => 403 ] );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            return new \WP_Error( 'rest_forbidden', 'Forbidden', [ 'status' => 403 ] );
        }
        return true;
    }

    public static function kpi( WP_REST_Request $request ) {
        // Dummy time-series data
        $series = [];
        for ( $i = 6; $i >= 0; $i-- ) {
            $date = date( 'Y-m-d', strtotime( "-$i days" ) );
            $series[] = [
                'date'       => $date,
                'revenue'    => rand(1, 10),
                'inventory'  => rand(50, 150),
                'impression' => rand(20, 100),
            ];
        }

        return [
            'inventory'       => 79,
            'impression'      => 11,
            'revenue'         => 0.02,
            'cpm'             => 1.41,
            'ctr'             => 0,
            'completion_rate' => 54.55,
            'series'          => $series,
        ];
    }

    public static function timeseries( WP_REST_Request $request ) {
        return [
            [ 'label' => '2025-07-02 00h', 'value' => 0.2 ],
            [ 'label' => '2025-07-02 01h', 'value' => 0.3 ],
            [ 'label' => '2025-07-02 02h', 'value' => 0.1 ],
        ];
    }

    public static function rank( WP_REST_Request $request ) {
        $dimension = $request->get_param( 'dimension' );

        switch ( $dimension ) {
            case 'country':
                return [
                    [ 'label' => 'US',    'impression' => 1200, 'revenue' => 60 ],
                    [ 'label' => 'UK',    'impression' => 900,  'revenue' => 45 ],
                    [ 'label' => 'CA',    'impression' => 850,  'revenue' => 40 ],
                    [ 'label' => 'DE',    'impression' => 800,  'revenue' => 38 ],
                    [ 'label' => 'FR',    'impression' => 780,  'revenue' => 35 ],
                    [ 'label' => 'ES',    'impression' => 760,  'revenue' => 32 ],
                    [ 'label' => 'IT',    'impression' => 740,  'revenue' => 30 ],
                    [ 'label' => 'IN',    'impression' => 700,  'revenue' => 28 ],
                    [ 'label' => 'BR',    'impression' => 650,  'revenue' => 25 ],
                    [ 'label' => 'AU',    'impression' => 600,  'revenue' => 22 ],
                ];

            case 'os':
                return [
                    [ 'label' => 'Windows', 'impression' => 1500, 'revenue' => 70 ],
                    [ 'label' => 'iOS',     'impression' => 1100, 'revenue' => 60 ],
                    [ 'label' => 'Android', 'impression' => 1000, 'revenue' => 55 ],
                    [ 'label' => 'Mac',     'impression' => 900,  'revenue' => 50 ],
                    [ 'label' => 'Linux',   'impression' => 400,  'revenue' => 20 ],
                ];

            case 'channel':
                return [
                    [ 'label' => 'Display',   'impression' => 1600, 'revenue' => 80 ],
                    [ 'label' => 'Video',     'impression' => 1400, 'revenue' => 70 ],
                    [ 'label' => 'Native',    'impression' => 1000, 'revenue' => 55 ],
                    [ 'label' => 'Instream',  'impression' => 800,  'revenue' => 45 ],
                    [ 'label' => 'Rewarded',  'impression' => 500,  'revenue' => 25 ],
                ];
        }

        return [];
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
        if ( $request->has_param( 'name' ) )      { $data['name'] = sanitize_text_field( $request['name'] ); }
        if ( $request->has_param( 'type' ) )      { $data['type'] = sanitize_text_field( $request['type'] ); }
        if ( $request->has_param( 'config' ) )    { $data['config'] = wp_json_encode( $request->get_param( 'config' ) ); }
        if ( $request->has_param( 'is_active' ) ) { $data['is_active'] = $request->get_param( 'is_active' ) ? 1 : 0; }

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

// Register all routes
add_action( 'rest_api_init', [ '\\AVD\\REST', 'register' ] );
