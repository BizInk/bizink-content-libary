<?php

defined('ABSPATH') || exit;


function bcl_create_database_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bcl_analytics';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        content_id mediumint(9) DEFAULT 0 NOT NULL,
        content_type varchar(50) NOT NULL,
        views mediumint(9) DEFAULT 0 NOT NULL,
        engagement mediumint(9) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}