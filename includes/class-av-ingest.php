<?php
namespace AVD;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Ingest {
    public static function init() {
        add_action( 'avd_ingest_hourly', [ __CLASS__, 'run' ] );
    }

    public static function install() {
        global $wpdb;
        $table = $wpdb->prefix . 'av_metrics';
        $charset = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $sql = "CREATE TABLE $table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            datasource_id bigint(20) unsigned NOT NULL,
            metric_date date NOT NULL,
            hour tinyint(2) NOT NULL,
            inventory bigint(20) unsigned NOT NULL,
            impression bigint(20) unsigned NOT NULL,
            revenue decimal(10,2) NOT NULL,
            cpm decimal(10,2) NOT NULL,
            ctr decimal(10,2) NOT NULL,
            completion_rate decimal(10,2) NOT NULL,
            PRIMARY KEY  (id),
            KEY datasource_date_hour (datasource_id, metric_date, hour)
        ) $charset;";
        dbDelta( $sql );
    }

    public static function schedule() {
        if ( ! wp_next_scheduled( 'avd_ingest_hourly' ) ) {
            wp_schedule_event( time(), 'hourly', 'avd_ingest_hourly' );
        }
    }

    public static function run() {
        global $wpdb;
        $ds_table = $wpdb->prefix . 'av_datasources';
        $metrics = $wpdb->prefix . 'av_metrics';

        $sources = $wpdb->get_results( "SELECT id FROM $ds_table WHERE active = 1" );
        foreach ( $sources as $ds ) {
            $inventory  = rand( 50, 100 );
            $impression = rand( 0, $inventory );
            $revenue    = round( $impression * 0.02, 2 );
            $cpm        = $impression ? round( ( $revenue / $impression ) * 1000, 2 ) : 0;
            $ctr        = $inventory ? round( ( $impression / $inventory ) * 100, 2 ) : 0;
            $completion = rand( 0, 100 );

            $wpdb->insert( $metrics, [
                'datasource_id'   => $ds->id,
                'metric_date'     => current_time( 'Y-m-d' ),
                'hour'            => (int) current_time( 'H' ),
                'inventory'       => $inventory,
                'impression'      => $impression,
                'revenue'         => $revenue,
                'cpm'             => $cpm,
                'ctr'             => $ctr,
                'completion_rate' => $completion,
            ], [
                '%d','%s','%d','%d','%d','%f','%f','%f','%f'
            ] );
        }
    }
}
