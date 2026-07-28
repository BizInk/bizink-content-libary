<?php

defined('ABSPATH') || exit;

define('BCL_DB_VERSION', '1.1.0');

function bcl_create_database_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bcl_analytics';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        content_id mediumint(9) DEFAULT 0 NOT NULL,
        event varchar(50) NOT NULL,
        page_url varchar(255) NOT NULL DEFAULT '',
        duration_seconds decimal(10,2) DEFAULT NULL,
        time datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY content_id (content_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    update_option('bcl_db_version', BCL_DB_VERSION);
}

// Creates/updates the table on plugin activation, and also picks up schema
// changes for sites where the plugin is already active (no reactivation needed).
function bcl_maybe_upgrade_database() {
    if (get_option('bcl_db_version') !== BCL_DB_VERSION) {
        bcl_create_database_table();
    }
}
add_action('plugins_loaded', 'bcl_maybe_upgrade_database');
