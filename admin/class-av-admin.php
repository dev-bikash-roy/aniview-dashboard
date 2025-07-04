<?php
namespace AVD;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Admin {
    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'assets' ] );
    }

    public static function menu() {
        add_menu_page(
            'AV Dashboard',
            'AV Dashboard',
            'manage_options',
            'avd-dashboard',
            [ __CLASS__, 'dashboard_page' ],
            'dashicons-chart-area',
            80
        );

        add_submenu_page(
            'avd-dashboard',
            'Data Sources',
            'Data Sources',
            'manage_options',
            'avd-datasources',
            [ __CLASS__, 'data_sources_page' ]
        );
    }

    public static function dashboard_page() {
        echo '<div class="wrap"><h1>AV Dashboard</h1>';
        include AVD_PATH . 'admin/views/dashboard.php';
        echo '</div>';
    }

    public static function data_sources_page() {
        global $wpdb;
        $table   = $wpdb->prefix . 'av_datasources';
        $sources = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC" );

        echo '<div class="wrap"><h1>Data Sources</h1>';
        include AVD_PATH . 'admin/views/data-sources.php';
        echo '</div>';
    }

    public static function assets( $hook ) {
        if ( strpos( $hook, 'avd-dashboard' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'avd-css',
            AVD_URL . 'assets/css/dashboard.css',
            [],
            AVD_VER
        );

        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js',
            [],
            null,
            true
        );

        wp_enqueue_script(
            'avd-js',
            AVD_URL . 'assets/js/dashboard.js',
            [ 'chartjs', 'wp-element', 'wp-api' ],
            AVD_VER,
            true
        );

        wp_localize_script( 'avd-js', 'avdData', [
            'root'  => esc_url_raw( rest_url( 'av/v1/' ) ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
        ] );
    }
}

Admin::init();
