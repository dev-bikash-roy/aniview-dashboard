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

// Nothing yet on activation but you can hook table creation here
register_activation_hook( __FILE__, function () {} );
