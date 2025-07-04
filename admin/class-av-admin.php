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
    }

    public static function dashboard_page() {
        echo '<div class="wrap"><h1>AV Dashboard</h1>';
        include AVD_PATH . 'admin/views/dashboard.php';
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
