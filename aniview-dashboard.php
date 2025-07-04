<?php
/*
Plugin Name: AV Dashboard
Description: Aniview‑style analytics dashboard base plugin.
Version:     0.1.0
Author:      Your Name
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'AVD_PATH', plugin_dir_path( __FILE__ ) );
define( 'AVD_URL',  plugin_dir_url( __FILE__ ) );
define( 'AVD_VER',  '0.1.0' );

require_once AVD_PATH . 'admin/class-av-admin.php';
require_once AVD_PATH . 'includes/class-av-rest.php';
require_once AVD_PATH . 'includes/class-av-ingest.php';

// ✅ Merge both activation flows
register_activation_hook( __FILE__, function () {
    avd_activate();
    \AVD\Ingest::install();
    \AVD\Ingest::schedule();
});

\AVD\Ingest::init();

// ✅ Keep the datasource table creation logic
function avd_activate() {
    global $wpdb;
    $table_name      = $wpdb->prefix . 'av_datasources';
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta("CREATE TABLE {$table_name} (
        id bigint(20) unsigned NOT NULL auto_increment,
        name varchar(191) NOT NULL,
        type varchar(50) NOT NULL,
        config longtext NULL,
        is_active tinyint(1) NOT NULL default 1,
        last_run datetime NULL,
        created datetime NOT NULL default CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) {$charset_collate};");
}
