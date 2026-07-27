<?php
/**
 * Simple Membership (SWPM) plugin integration.
 * Replaces the MemberPress integration. Falls back to native WP auth if SWPM is not active.
 */

namespace BCL\Theme;

defined('ABSPATH') || exit;

/**
 * Return true if an SWPM member (or WP user when SWPM is absent) is logged in.
 */
function swpm_member_logged_in(): bool {
    if (class_exists('SwpmAuth')) {
        return SwpmAuth::get_instance()->is_logged_in();
    }
    return is_user_logged_in();
}

/**
 * Return the current member's data as an array, or null when not authenticated.
 */
function swpm_current_member(): ?array {
    if (!swpm_member_logged_in()) return null;

    if (!class_exists('SwpmAuth')) {
        $user = wp_get_current_user();
        return [
            'member_id'  => $user->ID,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->user_email,
            'username'   => $user->user_login,
            'level_id'   => 0,
            'level_name' => 'Member',
            'plan'       => current_user_can('manage_options') ? 'agency' : 'starter',
            'state'      => 'active',
        ];
    }

    $auth     = SwpmAuth::get_instance();
    $level_id = (int) $auth->get('membership_level');

    return [
        'member_id'  => (int)    $auth->get('member_id'),
        'first_name' => (string) $auth->get('first_name'),
        'last_name'  => (string) $auth->get('last_name'),
        'email'      => (string) $auth->get('email'),
        'username'   => (string) $auth->get('username'),
        'level_id'   => $level_id,
        'level_name' => _swpm_resolve_level_name($level_id),
        'plan'       => _swpm_level_to_plan($level_id),
        'state'      => (string) $auth->get('account_state'),
    ];
}

/**
 * Resolve a SWPM level ID to its display name.
 */
function _swpm_resolve_level_name(int $level_id): string {
    if (!$level_id) return 'Member';
    if (class_exists('SwpmMemberUtils')) {
        $name = SwpmMemberUtils::get_membership_level_name($level_id);
        if ($name) return $name;
    }
    $post = get_post($level_id);
    return $post ? $post->post_title : 'Member';
}

/**
 * Map a SWPM level ID to a BCL plan tier slug.
 *
 * Set in wp-config.php:
 *   define('BCL_SWPM_PLAN_MAP', [5 => 'agency', 6 => 'professional', 7 => 'starter']);
 */
function _swpm_level_to_plan(int $level_id): string {
    $map = defined('BCL_SWPM_PLAN_MAP') ? (array) BCL_SWPM_PLAN_MAP : [];
    return $map[$level_id] ?? 'starter';
}

/**
 * Return the SWPM login page URL, with optional redirect target.
 */
function swpm_get_login_url(string $redirect = ''): string {
    if (class_exists('SwpmSettings')) {
        $url = SwpmSettings::get_instance()->get_value('login_page_url');
        if ($url) {
            return $redirect ? add_query_arg('redirect_to', rawurlencode($redirect), $url) : $url;
        }
    }
    return wp_login_url($redirect ?: home_url('/'));
}

/**
 * Return the URL that logs the current SWPM member out.
 */
function swpm_get_logout_url(): string {
    if (class_exists('SwpmAuth')) {
        return esc_url(add_query_arg('swpm_process_logout', '1', home_url('/')));
    }
    return wp_logout_url(home_url('/'));
}

/**
 * Redirect non-authenticated visitors to the login page.
 * Call before any output on protected templates.
 */
function require_member_login(): void {
    if (!swpm_member_logged_in()) {
        wp_safe_redirect(swpm_get_login_url(get_permalink() ?: home_url('/')));
        exit;
    }
}

/**
 * Check whether the current member may access a piece of content.
 * Uses the 'required_plan' ACF field and the BCL plan hierarchy.
 */
function user_can_access_content(int $post_id, ?int $user_id = null): bool {
    $required = get_field('required_plan', $post_id) ?: 'starter';
    if ($required === 'starter') return true;

    if (!swpm_member_logged_in()) return false;

    $member = swpm_current_member();
    if (!$member || $member['state'] !== 'active') return false;

    $hierarchy = ['starter' => 1, 'professional' => 2, 'agency' => 3];
    return ($hierarchy[$member['plan']] ?? 0) >= ($hierarchy[$required] ?? 99);
}

/**
 * Return the current member's plan slug.
 * Kept for backwards-compatibility with template-functions.php.
 */
function member_plan(int $user_id = 0): string {
    $member = swpm_current_member();
    return $member ? $member['plan'] : 'starter';
}
