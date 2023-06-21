<?php

if ( ! defined('ABSPATH') ) {
    die('Direct access not permitted.');
}

add_filter('cron_schedules', 'custom_cron_schedule');
add_action('admin_menu', 'va_menu');
add_action('wp_loaded', 'va_comprobar_enlaces');