<?php

defined('ABSPATH') || exit;

/**
 * "Member Levels" post type: the purchasable products backing Stripe
 * subscriptions. Each level stores a monthly and yearly price per enabled
 * currency (see acf-json/group_member_level.json); checkout builds Stripe
 * price_data from these directly, so no Stripe price IDs need to be kept in
 * sync manually. Publish status doubles as the "active" flag.
 */

function bcl_register_member_level_post_type()
{
    register_post_type('bcl_member_level', array(
        'labels' => array(
            'name'          => 'Member Levels',
            'singular_name' => 'Member Level',
            'add_new_item'  => 'Add new member level',
            'edit_item'     => 'Edit member level',
        ),
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'show_in_rest'    => true,
        'has_archive'     => false,
        'menu_icon'       => 'dashicons-money-alt',
        'menu_position'   => 6,
        'supports'        => array('title', 'page-attributes'),
        'capability_type' => 'post',
    ));
}
add_action('init', 'bcl_register_member_level_post_type');

// Keep the level's Stripe product (name, description, active status) in sync
// whenever it's saved in the admin.
//
// This post type is show_in_rest, so the block editor saves it through the
// REST API rather than the classic $_POST/nonce flow — ACF persists field
// values there via its own REST update_callback, which never fires
// acf/save_post. We hook both: rest_after_insert_{post_type} (fires after
// core *and* ACF's REST field callback have both persisted, per
// WP_REST_Posts_Controller::update_item()) for the block editor, and
// acf/save_post as a fallback for any classic-form save path.
add_action('rest_after_insert_bcl_member_level', function ($post) {
    if (function_exists('bcl_stripe_sync_product_for_level')) {
        bcl_stripe_sync_product_for_level($post->ID);
    }
});

add_action('acf/save_post', function ($post_id) {
    if (!is_numeric($post_id) || get_post_type($post_id) !== 'bcl_member_level') {
        return;
    }
    if (function_exists('bcl_stripe_sync_product_for_level')) {
        bcl_stripe_sync_product_for_level((int) $post_id);
    }
}, 20);

/**
 * Look up a single price for a Member Level.
 *
 * @param int    $level_id Post ID of the bcl_member_level.
 * @param string $interval 'month' or 'year'.
 * @param string $currency Currency code, e.g. 'USD'.
 * @param bool   $upfront  True for the one-time upfront fee charged with the
 *                         first payment, instead of the recurring price.
 * @return float|null Null when no price is set for that currency.
 */
function bcl_member_level_price($level_id, $interval, $currency, $upfront = false)
{
    if (!function_exists('get_fields')) {
        return null;
    }
    $group_name = ($interval === 'year' ? 'yearly' : 'monthly') . ($upfront ? '_upfront_price' : '_price');
    $fields     = get_fields($level_id);
    $group      = $fields[$group_name] ?? array();
    $value      = $group[strtolower($currency)] ?? null;
    return ($value !== null && $value !== '') ? (float) $value : null;
}

function bcl_member_levels(WP_REST_Request $request)
{
    $query = new WP_Query(array(
        'post_type'      => 'bcl_member_level',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    ));

    $currencies = function_exists('bcl_stripe_enabled_currencies') ? bcl_stripe_enabled_currencies() : array('NZD');
    $levels     = array();

    foreach ($query->posts as $post) {
        $fields          = function_exists('get_fields') ? (get_fields($post->ID) ?: array()) : array();
        $monthly         = $fields['monthly_price'] ?? array();
        $yearly          = $fields['yearly_price'] ?? array();
        $monthly_upfront = $fields['monthly_upfront_price'] ?? array();
        $yearly_upfront  = $fields['yearly_upfront_price'] ?? array();

        $pricing = array();
        foreach ($currencies as $code) {
            $key = strtolower($code);
            $pricing[$code] = array(
                'symbol'          => BCL_STRIPE_CURRENCIES[$code]['symbol'] ?? '',
                'monthly'         => isset($monthly[$key]) && $monthly[$key] !== '' ? (float) $monthly[$key] : null,
                'yearly'          => isset($yearly[$key]) && $yearly[$key] !== '' ? (float) $yearly[$key] : null,
                'monthly_upfront' => isset($monthly_upfront[$key]) && $monthly_upfront[$key] !== '' ? (float) $monthly_upfront[$key] : null,
                'yearly_upfront'  => isset($yearly_upfront[$key]) && $yearly_upfront[$key] !== '' ? (float) $yearly_upfront[$key] : null,
            );
        }

        $levels[] = array(
            'id'          => $post->ID,
            'name'        => $post->post_title,
            'description' => $fields['description'] ?? '',
            'pricing'     => $pricing,
        );
    }
    wp_reset_postdata();

    $response = new WP_REST_Response($levels, 200);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

add_action('rest_api_init', function () {
    register_rest_route('bcl/v1', '/member-levels', array(
        'methods'             => 'GET',
        'callback'            => 'bcl_member_levels',
        'permission_callback' => '__return_true',
    ));
});

// Admin list columns: preview pricing in the first enabled currency, and the
// synced Stripe product.
add_filter('manage_bcl_member_level_posts_columns', function ($columns) {
    $date = $columns['date'] ?? null;
    unset($columns['date']);
    $columns['bcl_monthly_price']    = 'Monthly price';
    $columns['bcl_yearly_price']     = 'Yearly price';
    $columns['bcl_stripe_product_id'] = 'Stripe product';
    if ($date !== null) {
        $columns['date'] = $date;
    }
    return $columns;
});

add_action('manage_bcl_member_level_posts_custom_column', function ($column, $post_id) {
    if ($column === 'bcl_stripe_product_id') {
        echo bcl_stripe_product_id_admin_markup($post_id);
        return;
    }

    if ($column !== 'bcl_monthly_price' && $column !== 'bcl_yearly_price') {
        return;
    }
    $currencies = function_exists('bcl_stripe_enabled_currencies') ? bcl_stripe_enabled_currencies() : array('NZD');
    $currency   = $currencies[0] ?? 'NZD';
    $interval   = $column === 'bcl_monthly_price' ? 'month' : 'year';
    $price      = bcl_member_level_price($post_id, $interval, $currency);
    echo $price !== null
        ? esc_html(BCL_STRIPE_CURRENCIES[$currency]['symbol'] . number_format($price, 2) . ' ' . $currency)
        : '—';
}, 10, 2);

/**
 * Escaped HTML for a level's Stripe product id: a link to the Stripe
 * dashboard (test or live, matching the configured secret key). Levels
 * saved before Stripe product syncing was added won't have this meta yet,
 * so it's created lazily here on first view rather than requiring a resave.
 */
function bcl_stripe_product_id_admin_markup($post_id)
{
    $product_id = get_post_meta($post_id, 'bcl_stripe_product_id', true);

    if (empty($product_id) && function_exists('bcl_stripe_product_id_for_level')) {
        $result = bcl_stripe_product_id_for_level($post_id);
        if (is_wp_error($result)) {
            return '<span style="color:#a00;" title="' . esc_attr($result->get_error_message()) . '">Not synced</span>';
        }
        $product_id = $result;
    }

    if (empty($product_id)) {
        return '—';
    }

    $secret_key = function_exists('bcl_stripe_option') ? bcl_stripe_option('secret_key') : '';
    $is_test    = strpos($secret_key, 'sk_test_') === 0;
    $url        = 'https://dashboard.stripe.com/' . ($is_test ? 'test/' : '') . 'products/' . $product_id;

    return '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($product_id) . '</a>';
}

// Edit screen: show the synced Stripe product id (read-only) under the title.
add_action('edit_form_after_title', function ($post) {
    if ($post->post_type !== 'bcl_member_level') {
        return;
    }
    echo '<p style="margin: 0 0 1em;"><strong>Stripe product:</strong> ' . bcl_stripe_product_id_admin_markup($post->ID) . '</p>';
});
