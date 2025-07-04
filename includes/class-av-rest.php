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

        register_rest_route( 'av/v1', '/rank', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [ __CLASS__, 'rank' ],
            'permission_callback' => function() { return current_user_can( 'manage_options' ); },
            'args' => [
                'dimension' => [ 'required' => true ],
                'start'     => [],
                'end'       => [],
            ],
        ] );
    }

    public static function kpi( $request ) {
        // Example static data for demo purposes only
        $series = [];
        for ( $i = 6; $i >= 0; $i-- ) {
            $date  = date( 'Y-m-d', strtotime( "-$i days" ) );
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

    public static function rank( $request ) {
        $dimension = $request->get_param( 'dimension' );

        $data = [];
        switch ( $dimension ) {
            case 'country':
                $data = [
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
                break;

            case 'os':
                $data = [
                    [ 'label' => 'Windows', 'impression' => 1500, 'revenue' => 70 ],
                    [ 'label' => 'iOS',     'impression' => 1100, 'revenue' => 60 ],
                    [ 'label' => 'Android', 'impression' => 1000, 'revenue' => 55 ],
                    [ 'label' => 'Mac',     'impression' => 900,  'revenue' => 50 ],
                    [ 'label' => 'Linux',   'impression' => 400,  'revenue' => 20 ],
                ];
                break;

            case 'channel':
                $data = [
                    [ 'label' => 'Display',   'impression' => 1600, 'revenue' => 80 ],
                    [ 'label' => 'Video',     'impression' => 1400, 'revenue' => 70 ],
                    [ 'label' => 'Native',    'impression' => 1000, 'revenue' => 55 ],
                    [ 'label' => 'Instream',  'impression' => 800,  'revenue' => 45 ],
                    [ 'label' => 'Rewarded',  'impression' => 500,  'revenue' => 25 ],
                ];
                break;
        }

        return array_slice( $data, 0, 10 );
    }
}

add_action( 'rest_api_init', [ '\AVD\REST', 'register' ] );
