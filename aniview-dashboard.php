<?php
/*
Plugin Name: Aniview Dashboard
Description: Base plugin for an admin analytics dashboard.
Version:     0.1.0
Author:      Your Name
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'AVD_PATH', plugin_dir_path( __FILE__ ) );
define( 'AVD_URL',  plugin_dir_url( __FILE__ ) );

require_once AVD_PATH . 'admin/class-av-admin.php';
