<?php
/*
 Plugin Name: BCL Portal
 Plugin URI:  https://portal.bizinkonline.com/
 Description: Bizink Content Libary Server Plugin
 Author:      Jayden Major
 Version:     0.1.9
 Author URI:  https://portal.bizinkonline.com/
 Text Domain: bizink-content
 License:     GPLv2 or later
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/inc/template-functions.php';
require_once __DIR__ . '/inc/analytics_db.php';
require_once __DIR__ . '/inc/api.php';
require_once __DIR__ . '/inc/simple-membership.php';

register_activation_hook(__FILE__, 'bcl_create_database_table');

// Store ACF JSON in theme so it's version-controlled.
add_filter('acf/settings/save_json', function() {
    return plugin_dir_path( __FILE__ ) . '/acf-json';
});
add_filter('acf/settings/load_json', function($paths) {
    $paths[] = plugin_dir_path( __FILE__ ) . '/acf-json';
    return $paths;
});

// Lock down XML-RPC (MemberPress doesn't need it; reduces attack surface).
add_filter('xmlrpc_enabled', '__return_false');

// Add custom image sizes.
add_action('after_setup_theme', function() {
    add_image_size('bcl_card', 640, 360, true);
    add_image_size('bcl_hero', 1920, 900, true);
});

// Remove emoji cruft.
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

/**
 * Register all BCL content post types.
 */
function register_content_types(): void {
    $types = [
        'bcl_article'    => [ 'singular' => 'Article',        'plural' => 'Articles',         'icon' => 'dashicons-media-document' ],
        'bcl_ebook'      => [ 'singular' => 'eBook',          'plural' => 'eBooks',           'icon' => 'dashicons-book' ],
        'bcl_calculator' => [ 'singular' => 'Calculator',     'plural' => 'Calculators',      'icon' => 'dashicons-calculator' ],
        'bcl_tool'       => [ 'singular' => 'Interactive tool','plural'=> 'Interactive tools','icon' => 'dashicons-admin-tools' ],
        'bcl_pdf'        => [ 'singular' => 'PDF template',   'plural' => 'PDF templates',    'icon' => 'dashicons-pdf' ],
        'bcl_excel'      => [ 'singular' => 'Excel template', 'plural' => 'Excel templates',  'icon' => 'dashicons-media-spreadsheet' ],
    ];

    foreach ($types as $slug => $meta) {
        register_post_type($slug, [
            'labels' => [
                'name'          => $meta['plural'],
                'singular_name' => $meta['singular'],
                'add_new_item'  => 'Add new ' . strtolower($meta['singular']),
                'edit_item'     => 'Edit ' . strtolower($meta['singular']),
            ],
            'public'            => true,
            'show_in_rest'      => true,
            'has_archive'       => false,
            'menu_postion'      => 5,
            'menu_icon'         => $meta['icon'],
            'supports'          => $slug == 'bcl_article' ? ['title', 'editor', 'excerpt', 'thumbnail', 'revisions']:['title', 'thumbnail', 'revisions'],
            'taxonomies'        => ['bcl_topic'],
            'rewrite'           => ['slug' => str_replace('bcl_', '', $slug)],
            'capability_type'   => 'post',
        ]);
    }
}
add_action('init', __NAMESPACE__ . '\\register_content_types');

/**
 * Shared topic taxonomy used across all BCL content types.
 */
function register_topics_taxonomy(): void {
    register_taxonomy('bcl_topic', array_keys([
        'bcl_article' => 1, 'bcl_ebook' => 1, 'bcl_calculator' => 1,
        'bcl_tool' => 1, 'bcl_pdf' => 1, 'bcl_excel' => 1,
    ]), [
        'labels'            => [
            'name'          => 'Topics',
            'singular_name' => 'Topic',
        ],
        'public'            => true,
        'show_in_rest'      => true,
        'hierarchical'      => true,
        'rewrite'           => ['slug' => 'topic'],
    ]);

    register_taxonomy('bcl_region', array_keys([
        'bcl_article' => 1, 'bcl_ebook' => 1, 'bcl_calculator' => 1,
        'bcl_tool' => 1, 'bcl_pdf' => 1, 'bcl_excel' => 1,
    ]), [
        'labels'            => [
            'name'          => 'Regions',
            'singular_name' => 'Region',
        ],
        'public'            => true,
        'show_in_rest'      => true,
        'hierarchical'      => true,
        'rewrite'           => ['slug' => 'region'],
    ]);
}
add_action('init', __NAMESPACE__ . '\\register_topics_taxonomy');

// Portal Email
function bcl_password_email(string $message, string $key, string $user_login, WP_User $user_data){
    return str_replace('portalcontent.bizinkonline.com/wp-login.php','portal.bizinkonline.com/resetpassword',$message);
}
add_filter('retrieve_password_message','bcl_password_email', 10,1);

function bcl_mail_args(array $args){
    if(empty($args['headers'])){
        $args['headers'] = [];
    }
    if(gettype($args['headers']) == 'string'){
        $args['headers'] = [$args['headers']];
    }
    if(array_key_exists('Reply-To',$args['headers'])){
        $args['headers']['Reply-To'] = 'support@bizinkonline.com';
    }
    else{
        array_push($args['headers'],['Reply-To'=> 'support@bizinkonline.com']);
    }
    
}
add_filter('wp_mail', 'bcl_mail_args', 10,1);

function bcl_from_name(string $from_name){
    return 'BCL Portal';
}
add_filter('wp_mail_from_name','bcl_from_name', 10,1);

function bcl_from_email(string $from_email){
    return 'hello@bizinkonline.com';
}
add_filter('wp_mail_from','bcl_from_email', 10,1);


// Theme Updater
require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$myUpdateChecker = PucFactory::buildUpdateChecker('https://github.com/BizInk/bizink-content-libary',__FILE__,'bizink-content-libary');
$myUpdateChecker->setBranch('master');