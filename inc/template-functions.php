<?php
namespace BCL\Theme;

defined('ABSPATH') || exit;

/**
 * Return the current member's firm (subscriber) record.
 * Sources brand data from SWPM extra_info JSON, falling back to WP user meta.
 */
function current_subscriber(): ?array {
    if (!swpm_member_logged_in()) return null;

    $member  = swpm_current_member();
    $user_id = $member['member_id'] ?? 0;

    // Try SWPM extra_info JSON for brand overrides (stored by admin).
    $brand = [];
    if (class_exists('SwpmAuth') && $user_id) {
        $extra = SwpmAuth::get_instance()->get('extra_info');
        if ($extra) {
            $brand = (array) json_decode($extra, true);
        }
    }

    // Fall back to WP user meta (for environments that sync SWPM → WP users).
    $wp_id = get_current_user_id();

    return [
        'user_id'         => $user_id,
        'firm_name'       => $brand['firm_name']       ?? ($wp_id ? get_user_meta($wp_id, 'firm_name', true)     : '') ?: '',
        'logo_url'        => $brand['logo_url']        ?? ($wp_id ? get_user_meta($wp_id, 'logo_url', true)      : '') ?: '',
        'brand_primary'   => $brand['brand_primary']   ?? ($wp_id ? get_user_meta($wp_id, 'brand_primary', true) : '') ?: '#1F4E79',
        'brand_secondary' => $brand['brand_secondary'] ?? ($wp_id ? get_user_meta($wp_id, 'brand_secondary', true): '') ?: '#2E75B6',
        'plan'            => $member['plan'] ?? 'starter',
    ];
}

/**
 * Return a localized content-type label.
 */
function content_type_label(string $post_type): string {
    return match ($post_type) {
        'bcl_article'    => 'Article',
        'bcl_ebook'      => 'eBook',
        'bcl_calculator' => 'Calculator',
        'bcl_tool'       => 'Interactive tool',
        'bcl_pdf'        => 'PDF template',
        'bcl_xlsx'       => 'Excel template',
        default          => ucfirst(str_replace('bcl_', '', $post_type)),
    };
}

