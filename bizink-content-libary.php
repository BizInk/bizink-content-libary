<?php
/*
 Plugin Name: BCL Portal
 Plugin URI:  https://portal.bizinkonline.com/
 Description: Bizink Content Libary Server Plugin
 Author:      Jayden Major
 Version:     0.2
 Author URI:  https://portal.bizinkonline.com/
 Text Domain: bizink-content
 License:     GPLv2 or later
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/inc/template-functions.php';
require_once __DIR__ . '/inc/analytics_db.php';
require_once __DIR__ . '/inc/stripe.php';
require_once __DIR__ . '/inc/member-levels.php';
require_once __DIR__ . '/inc/api.php';
require_once __DIR__ . '/inc/simple-membership.php';
require_once __DIR__ . '/inc/article-migration.php';

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
        'bcl_request'    => [ 'singular' => 'Custom Request', 'plural' => 'Custom Requests',  'icon' => 'dashicons-image-rotate-right' ]
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
            'supports'          => ($slug == 'bcl_article' || $slug == 'bcl_ebook' || $slug == 'bcl_pdf') ? ['title', 'editor', 'excerpt', 'thumbnail', 'revisions']:['title', 'thumbnail', 'revisions','author'],
            'taxonomies'        => $slug == 'bcl_request' ? []:['bcl_topic','region'],
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
        'hierarchical'      => false,
        'rewrite'           => ['slug' => 'region'],
    ]);
}
add_action('init', __NAMESPACE__ . '\\register_topics_taxonomy');

/**
 * Add "Status" and "Author" columns to the Custom Requests admin list.
 */
function bcl_request_add_columns(array $columns): array {
    $date = $columns['date'] ?? null;
    unset($columns['date']);
    $columns['bcl_status'] = 'Status';
    $columns['bcl_messages'] = 'Messages';
    if ($date !== null) {
        $columns['date'] = $date;
    }
    return $columns;
}
add_filter('manage_bcl_request_posts_columns', __NAMESPACE__ . '\\bcl_request_add_columns');

function bcl_request_render_column(string $column, int $post_id): void {
    if ($column === 'bcl_status') {
        $choices = [
            'pending'        => 'Pending',
            'processing'     => 'Processing',
            'done'           => 'Done',
            'clientResponse' => 'Client Response',
            'adminResponse'  => 'Admin Response',
            'cancelled'      => 'Cancelled',
        ];

        $status = get_field('status', $post_id);
        echo esc_html($choices[$status] ?? ($status ?: '—'));
     }
    if ($column === 'bcl_messages') {
        $messages = bcl_branding_request_messages($post_id);
        if (empty($messages)) {
            echo '—';
            return;
        }
        $last = end($messages);
        echo esc_html(count($messages));
        if (!$last['is_admin']) {
            echo ' <span class="awaiting-mod" style="display:inline-block;background:#d63638;color:#fff;border-radius:9px;padding:0 6px;font-size:11px;">Client replied</span>';
        }
        printf('<br><span class="description">%s</span>', esc_html(wp_trim_words($last['message'], 10)));
    }
}
add_action('manage_bcl_request_posts_custom_column', __NAMESPACE__ . '\\bcl_request_render_column', 10, 2);

/**
 * Show a count bubble on the Custom Requests admin menu item for requests
 * that are waiting on an admin (status "pending" or "clientResponse").
 */
function bcl_request_menu_bubble(): void {
    global $menu;

    $query = new \WP_Query([
        'post_type'      => 'bcl_request',
        'post_status'    => ['publish', 'draft', 'pending', 'private', 'future'],
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => false,
        'meta_query'     => [[
            'key'     => 'status',
            'value'   => ['pending', 'clientResponse'],
            'compare' => 'IN',
        ]],
    ]);
    $count = (int) $query->found_posts;
    if ($count < 1) {
        return;
    }

    foreach ($menu as $key => $item) {
        if ($item[2] === 'edit.php?post_type=bcl_request') {
            $menu[$key][0] .= sprintf(
                ' <span class="awaiting-mod count-%1$d"><span class="pending-count">%1$d</span></span>',
                $count
            );
            break;
        }
    }
}
add_action('admin_menu', __NAMESPACE__ . '\\bcl_request_menu_bubble', 999);

/**
 * "Conversation" meta box on the Custom Request edit screen so staff can read
 * and reply to the client's chat messages from the portal. Replies are sent
 * over admin-ajax and the thread polls for new client messages.
 */
function bcl_request_add_chat_metabox(): void {
    add_meta_box('bcl_request_chat', 'Conversation', __NAMESPACE__ . '\\bcl_request_render_chat_metabox', 'bcl_request', 'normal', 'high');
}
add_action('add_meta_boxes', __NAMESPACE__ . '\\bcl_request_add_chat_metabox');

function bcl_request_chat_messages_html(int $post_id): string {
    $messages = bcl_branding_request_messages($post_id);
    if (empty($messages)) {
        return '<p class="bcl-chat-empty"><em>No messages yet.</em></p>';
    }

    $html = '';
    foreach ($messages as $msg) {
        $html .= sprintf(
            '<div class="bcl-chat-msg %s"><div class="bcl-chat-bubble"><div class="bcl-chat-meta"><strong>%s</strong>%s &middot; %s</div><div class="bcl-chat-text">%s</div></div></div>',
            $msg['is_admin'] ? 'is-staff' : 'is-client',
            esc_html($msg['author_name']),
            $msg['is_admin'] ? ' (Staff)' : ' (Client)',
            esc_html(mysql2date('j M Y, g:i a', $msg['created'])),
            esc_html($msg['message'])
        );
    }
    return $html;
}

function bcl_request_render_chat_metabox(\WP_Post $post): void {
    wp_nonce_field('bcl_request_chat', 'bcl_request_chat_nonce');
    $count = count(bcl_branding_request_messages($post->ID));
    ?>
    <style>
        #bcl-chat-thread { max-height: 420px; overflow-y: auto; padding: 8px; background: #fbfbfb; border: 1px solid #dcdcde; border-radius: 4px; margin-bottom: 12px; }
        #bcl-chat-thread .bcl-chat-msg { display: flex; margin-bottom: 8px; }
        #bcl-chat-thread .bcl-chat-msg.is-staff { justify-content: flex-end; }
        #bcl-chat-thread .bcl-chat-bubble { max-width: 75%; padding: 8px 12px; border-radius: 10px; background: #f0f0f1; }
        #bcl-chat-thread .is-staff .bcl-chat-bubble { background: #2271b1; color: #fff; }
        #bcl-chat-thread .bcl-chat-meta { font-size: 11px; opacity: .8; margin-bottom: 2px; }
        #bcl-chat-thread .bcl-chat-text { white-space: pre-wrap; word-break: break-word; }
        #bcl-chat-form { display: flex; gap: 8px; align-items: flex-end; }
        #bcl-chat-form textarea { flex: 1; }
        #bcl-chat-error { color: #d63638; display: none; margin: 6px 0 0; }
    </style>
    <div id="bcl-chat-thread" data-count="<?php echo esc_attr($count); ?>"><?php echo bcl_request_chat_messages_html($post->ID); ?></div>
    <div id="bcl-chat-form">
        <textarea id="bcl_request_chat_reply" name="bcl_request_chat_reply" rows="3" placeholder="Reply to client… (Enter to send, Shift+Enter for a new line)"></textarea>
        <button type="button" class="button button-primary" id="bcl-chat-send">Send</button>
    </div>
    <p id="bcl-chat-error"></p>
    <p class="description">Sending sets the status to "Admin Response" unless the request is Done or Cancelled.</p>
    <script>
    (function () {
        var postId = <?php echo (int) $post->ID; ?>;
        var nonce = <?php echo wp_json_encode(wp_create_nonce('bcl_request_chat_ajax')); ?>;
        var thread = document.getElementById('bcl-chat-thread');
        var input = document.getElementById('bcl_request_chat_reply');
        var button = document.getElementById('bcl-chat-send');
        var errorEl = document.getElementById('bcl-chat-error');

        function scrollToEnd() { thread.scrollTop = thread.scrollHeight; }

        function render(data) {
            if (String(data.count) !== thread.getAttribute('data-count')) {
                thread.innerHTML = data.html;
                thread.setAttribute('data-count', data.count);
                scrollToEnd();
            }
            // Keep the ACF status field in sync so clicking Update doesn't revert it.
            var status = document.querySelector('.acf-field[data-name="status"] select');
            if (status && data.status && status.value !== data.status) {
                status.value = data.status;
                status.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        function request(action, extra) {
            var body = new URLSearchParams(Object.assign({ action: action, post_id: postId, nonce: nonce }, extra || {}));
            return fetch(ajaxurl, { method: 'POST', credentials: 'same-origin', body: body })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.success) throw new Error((res.data && res.data.message) || 'Request failed');
                    return res.data;
                });
        }

        function send() {
            var text = input.value.trim();
            if (!text) return;
            button.disabled = true;
            errorEl.style.display = 'none';
            request('bcl_request_chat_send', { message: text })
                .then(function (data) { input.value = ''; render(data); })
                .catch(function (err) { errorEl.textContent = err.message; errorEl.style.display = 'block'; })
                .finally(function () { button.disabled = false; });
        }

        button.addEventListener('click', send);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
        });

        scrollToEnd();
        setInterval(function () {
            if (document.hidden) return;
            request('bcl_request_chat_fetch').then(render).catch(function () {});
        }, 15000);
    })();
    </script>
    <?php
}

function bcl_request_chat_ajax_post(): int {
    check_ajax_referer('bcl_request_chat_ajax', 'nonce');
    $post_id = absint($_POST['post_id'] ?? 0);
    if (!$post_id || get_post_type($post_id) !== 'bcl_request' || !current_user_can('edit_post', $post_id)) {
        wp_send_json_error(['message' => 'You do not have permission to view this request.'], 403);
    }
    return $post_id;
}

function bcl_request_chat_ajax_payload(int $post_id): array {
    return [
        'count'  => count(bcl_branding_request_messages($post_id)),
        'html'   => bcl_request_chat_messages_html($post_id),
        'status' => get_field('status', $post_id) ?: '',
    ];
}

function bcl_request_chat_ajax_fetch(): void {
    $post_id = bcl_request_chat_ajax_post();
    wp_send_json_success(bcl_request_chat_ajax_payload($post_id));
}
add_action('wp_ajax_bcl_request_chat_fetch', __NAMESPACE__ . '\\bcl_request_chat_ajax_fetch');

function bcl_request_chat_ajax_send(): void {
    $post_id = bcl_request_chat_ajax_post();
    $message = trim(sanitize_textarea_field(wp_unslash($_POST['message'] ?? '')));
    if ($message === '') {
        wp_send_json_error(['message' => 'Message cannot be empty.'], 400);
    }
    bcl_branding_request_add_message($post_id, get_current_user_id(), $message);
    wp_send_json_success(bcl_request_chat_ajax_payload($post_id));
}
add_action('wp_ajax_bcl_request_chat_send', __NAMESPACE__ . '\\bcl_request_chat_ajax_send');

// Fallback: a reply typed but not sent goes out when the post is saved.
// Runs after ACF saves its fields (priority 10) so the status set by the reply wins.
function bcl_request_save_chat_reply($post_id): void {
    if (get_post_type($post_id) !== 'bcl_request') {
        return;
    }
    if (empty($_POST['bcl_request_chat_nonce']) || !wp_verify_nonce($_POST['bcl_request_chat_nonce'], 'bcl_request_chat')) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    $reply = trim(sanitize_textarea_field(wp_unslash($_POST['bcl_request_chat_reply'] ?? '')));
    if ($reply === '') {
        return;
    }
    bcl_branding_request_add_message($post_id, get_current_user_id(), $reply, false);
}
add_action('acf/save_post', __NAMESPACE__ . '\\bcl_request_save_chat_reply', 20);

// Portal Email
function bcl_new_user_email($message,  $user, $blogname){

}
// add_filter('wp_new_user_notification_email','bcl_new_user_email',10,3)

function bcl_password_email(string $message, string $key, string $user_login, $user_data){
    $mail = __( 'Someone has requested a password reset for the following account:', 'bcl_portal' ) . "\n";
    $mail .= sprintf( __( 'Username: %s', 'bcl_portal' ), $user_login ) . "\n";
    $mail .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'bcl_portal' ) . "\n\n";
	$mail .= __( 'To reset your password, visit the following address:', 'bcl_portal' ) . "\n\n";
	$mail .= 'https://portal.bizinkonline.com/resetpassword?action=rp&key='.$key.'&login='. rawurlencode( $user_login ) . "\n\n";

    return $mail;
}
add_filter('retrieve_password_message','bcl_password_email', 10, 4);

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
//add_filter('wp_mail', 'bcl_mail_args');

function bcl_from_name(string $from_name){
    return 'BCL Portal';
}
add_filter('wp_mail_from_name','bcl_from_name');

function bcl_from_email(string $from_email){
    return 'hello@bizinkonline.com';
}
add_filter('wp_mail_from','bcl_from_email');


// Theme Updater
require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$myUpdateChecker = PucFactory::buildUpdateChecker('https://github.com/BizInk/bizink-content-libary',__FILE__,'bizink-content-libary');
$myUpdateChecker->setBranch('master');