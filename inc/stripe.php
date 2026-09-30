<?php

defined('ABSPATH') || exit;

/**
 * Stripe integration: settings, a lightweight REST API client (no SDK/composer
 * dependency), per-user subscription tracking, and the webhook handler.
 * Replaces the Paid Memberships Pro Stripe gateway.
 */

define('BCL_STRIPE_CURRENCIES', array(
    'USD' => array('label' => 'USD ($)', 'symbol' => '$'),
    'AUD' => array('label' => 'AUD ($)', 'symbol' => '$'),
    'NZD' => array('label' => 'NZD ($)', 'symbol' => '$'),
    'EUR' => array('label' => 'EUR (€)', 'symbol' => '€'),
    'GBP' => array('label' => 'GBP (£)', 'symbol' => '£'),
));

define('BCL_STRIPE_API_BASE', 'https://api.stripe.com/v1/');

// ---------------------------------------------------------------------------
// Settings
// ---------------------------------------------------------------------------

function bcl_stripe_options()
{
    $defaults = array(
        'secret_key'      => '',
        'publishable_key' => '',
        'webhook_secret'  => '',
        'currencies'      => array('NZD'),
    );
    $options = get_option('bcl_stripe_settings', array());
    return array_merge($defaults, is_array($options) ? $options : array());
}

function bcl_stripe_option($key, $default = '')
{
    $options = bcl_stripe_options();
    return $options[$key] ?? $default;
}

function bcl_stripe_enabled_currencies()
{
    $currencies = bcl_stripe_option('currencies', array('NZD'));
    $currencies = array_values(array_intersect((array) $currencies, array_keys(BCL_STRIPE_CURRENCIES)));
    return empty($currencies) ? array('NZD') : $currencies;
}

// A masked value resubmitted unchanged tells us to keep the existing secret,
// so saving the form again doesn't require re-pasting live keys every time.
define('BCL_STRIPE_MASKED_VALUE', '••••••••');

function bcl_stripe_sanitize_settings($input)
{
    $existing = bcl_stripe_options();
    $output   = array();

    $output['secret_key'] = isset($input['secret_key']) ? sanitize_text_field(trim($input['secret_key'])) : $existing['secret_key'];
    if ($output['secret_key'] === BCL_STRIPE_MASKED_VALUE) {
        $output['secret_key'] = $existing['secret_key'];
    }

    $output['webhook_secret'] = isset($input['webhook_secret']) ? sanitize_text_field(trim($input['webhook_secret'])) : $existing['webhook_secret'];
    if ($output['webhook_secret'] === BCL_STRIPE_MASKED_VALUE) {
        $output['webhook_secret'] = $existing['webhook_secret'];
    }

    $output['publishable_key'] = sanitize_text_field(trim($input['publishable_key'] ?? ''));

    $selected = array_map('strtoupper', (array) ($input['currencies'] ?? array()));
    $output['currencies'] = array_values(array_intersect($selected, array_keys(BCL_STRIPE_CURRENCIES)));

    return $output;
}

add_action('admin_init', function () {
    register_setting('bcl_stripe_settings_group', 'bcl_stripe_settings', 'bcl_stripe_sanitize_settings');
});

add_action('admin_menu', function () {
    add_options_page('Stripe Settings', 'Stripe Settings', 'manage_options', 'bcl-stripe-settings', 'bcl_stripe_settings_page');
    add_submenu_page('options-general.php', 'Stripe Members', 'Stripe Members', 'manage_options', 'bcl-stripe-members', 'bcl_stripe_members_page');
});

function bcl_stripe_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $options = bcl_stripe_options();
?>
    <div class="wrap">
        <h1>Stripe Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('bcl_stripe_settings_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="bcl_stripe_secret_key">Secret key</label></th>
                    <td>
                        <input type="password" id="bcl_stripe_secret_key" name="bcl_stripe_settings[secret_key]"
                            value="<?php echo esc_attr($options['secret_key'] ? BCL_STRIPE_MASKED_VALUE : ''); ?>"
                            class="regular-text" autocomplete="off">
                        <p class="description">Your Stripe secret key (sk_live_... or sk_test_...). Leave the dots in place to keep the current key.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bcl_stripe_publishable_key">Publishable key</label></th>
                    <td>
                        <input type="text" id="bcl_stripe_publishable_key" name="bcl_stripe_settings[publishable_key]"
                            value="<?php echo esc_attr($options['publishable_key']); ?>" class="regular-text">
                        <p class="description">Safe to expose publicly. Served to the frontend via <code>GET <?php echo esc_url(rest_url('bcl/v1/stripe-config')); ?></code>.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bcl_stripe_webhook_secret">Webhook signing secret</label></th>
                    <td>
                        <input type="password" id="bcl_stripe_webhook_secret" name="bcl_stripe_settings[webhook_secret]"
                            value="<?php echo esc_attr($options['webhook_secret'] ? BCL_STRIPE_MASKED_VALUE : ''); ?>"
                            class="regular-text" autocomplete="off">
                        <p class="description">
                            Create a webhook endpoint in the Stripe dashboard pointing to
                            <code><?php echo esc_url(rest_url('bcl/v1/stripe/webhook')); ?></code>
                            and paste its signing secret here.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Enabled currencies</th>
                    <td>
                        <?php foreach (BCL_STRIPE_CURRENCIES as $code => $meta) : ?>
                            <label style="display:inline-block;margin-right:1.5em;">
                                <input type="checkbox" name="bcl_stripe_settings[currencies][]" value="<?php echo esc_attr($code); ?>"
                                    <?php checked(in_array($code, $options['currencies'], true)); ?>>
                                <?php echo esc_html($meta['label']); ?>
                            </label>
                        <?php endforeach; ?>
                        <p class="description">Members can subscribe to a Member Level in any of these currencies.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

function bcl_stripe_members_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $query = new WP_User_Query(array(
        'meta_key'     => 'bcl_stripe_subscription_status',
        'meta_value'   => '',
        'meta_compare' => '!=',
        'number'       => 200,
        'orderby'      => 'registered',
        'order'        => 'DESC',
    ));
    $users = $query->get_results();
?>
    <div class="wrap">
        <h1>Stripe Members</h1>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Member level</th>
                    <th>Status</th>
                    <th>Interval</th>
                    <th>Currency</th>
                    <th>Renews / ends</th>
                    <th>Stripe customer</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)) : ?>
                    <tr>
                        <td colspan="8">No Stripe subscriptions recorded yet.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($users as $user) :
                    $level_id    = (int) get_user_meta($user->ID, 'bcl_stripe_member_level_id', true);
                    $level_name  = $level_id ? get_the_title($level_id) : '—';
                    $status      = get_user_meta($user->ID, 'bcl_stripe_subscription_status', true);
                    $interval    = get_user_meta($user->ID, 'bcl_stripe_billing_interval', true);
                    $currency    = get_user_meta($user->ID, 'bcl_stripe_currency', true);
                    $period_end  = (int) get_user_meta($user->ID, 'bcl_stripe_current_period_end', true);
                    $customer_id = get_user_meta($user->ID, 'bcl_stripe_customer_id', true);
                ?>
                    <tr>
                        <td><a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>"><?php echo esc_html($user->display_name); ?></a></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td><?php echo esc_html($level_name); ?></td>
                        <td><?php echo esc_html($status ?: '—'); ?></td>
                        <td><?php echo esc_html($interval ?: '—'); ?></td>
                        <td><?php echo esc_html($currency ?: '—'); ?></td>
                        <td><?php echo $period_end ? esc_html(date_i18n(get_option('date_format'), $period_end)) : '—'; ?></td>
                        <td><?php echo $customer_id ? esc_html($customer_id) : '—'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
}

// ---------------------------------------------------------------------------
// Stripe REST API client (plain HTTP; no composer/SDK dependency)
// ---------------------------------------------------------------------------

function bcl_stripe_request($method, $endpoint, array $params = array())
{
    $secret_key = bcl_stripe_option('secret_key');
    if (empty($secret_key)) {
        return new WP_Error('bcl_stripe_not_configured', 'Stripe secret key is not configured');
    }

    $method = strtoupper($method);
    $args = array(
        'method'  => $method,
        'timeout' => 30,
        'headers' => array(
            'Authorization' => 'Bearer ' . $secret_key,
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ),
    );

    $url = BCL_STRIPE_API_BASE . ltrim($endpoint, '/');

    if ($method === 'GET') {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
    } else {
        $args['body'] = http_build_query($params);
    }

    $response = wp_remote_request($url, $args);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($code >= 400) {
        $message = $body['error']['message'] ?? 'Stripe API error';
        return new WP_Error('bcl_stripe_api_error', $message, array('status' => $code, 'body' => $body));
    }

    return is_array($body) ? $body : array();
}

function bcl_stripe_error_response($message, $status)
{
    $response = new WP_REST_Response(array('message' => $message), $status);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

// ---------------------------------------------------------------------------
// Customer + subscription tracking (cached on user meta, kept in sync by the
// webhook and refreshed opportunistically when the frontend asks for it)
// ---------------------------------------------------------------------------

function bcl_stripe_get_or_create_customer($user_id)
{
    $customer_id = get_user_meta($user_id, 'bcl_stripe_customer_id', true);
    if (!empty($customer_id)) {
        return $customer_id;
    }

    $user = get_userdata($user_id);
    if (!$user) {
        return new WP_Error('bcl_stripe_invalid_user', 'User not found');
    }

    $name = trim($user->first_name . ' ' . $user->last_name);

    $customer = bcl_stripe_request('POST', 'customers', array(
        'email'    => $user->user_email,
        'name'     => $name !== '' ? $name : $user->user_login,
        'metadata' => array('wp_user_id' => $user_id),
    ));

    if (is_wp_error($customer)) {
        return $customer;
    }

    update_user_meta($user_id, 'bcl_stripe_customer_id', $customer['id']);
    return $customer['id'];
}

function bcl_stripe_user_id_for_customer($customer_id)
{
    if (empty($customer_id)) {
        return 0;
    }
    $users = get_users(array(
        'meta_key'   => 'bcl_stripe_customer_id',
        'meta_value' => $customer_id,
        'number'     => 1,
        'fields'     => 'ID',
    ));
    return !empty($users) ? (int) $users[0] : 0;
}

function bcl_stripe_store_card($user_id, array $card)
{
    update_user_meta($user_id, 'bcl_stripe_card_brand', $card['brand'] ?? '');
    update_user_meta($user_id, 'bcl_stripe_card_last4', $card['last4'] ?? '');
    update_user_meta($user_id, 'bcl_stripe_card_exp_month', $card['exp_month'] ?? '');
    update_user_meta($user_id, 'bcl_stripe_card_exp_year', $card['exp_year'] ?? '');
}

function bcl_stripe_store_subscription($user_id, array $subscription)
{
    update_user_meta($user_id, 'bcl_stripe_subscription_id', $subscription['id']);
    update_user_meta($user_id, 'bcl_stripe_subscription_status', $subscription['status']);
    update_user_meta($user_id, 'bcl_stripe_cancel_at_period_end', !empty($subscription['cancel_at_period_end']));

    if (!empty($subscription['current_period_end'])) {
        update_user_meta($user_id, 'bcl_stripe_current_period_end', (int) $subscription['current_period_end']);
    }

    $item = $subscription['items']['data'][0] ?? null;
    if ($item) {
        $price = $item['price'] ?? array();
        if (!empty($price['currency'])) {
            update_user_meta($user_id, 'bcl_stripe_currency', strtoupper($price['currency']));
        }
        if (!empty($price['recurring']['interval'])) {
            update_user_meta($user_id, 'bcl_stripe_billing_interval', $price['recurring']['interval']);
        }
        update_user_meta($user_id, 'bcl_stripe_price_id', $price['id'] ?? '');
    }

    if (!empty($subscription['metadata']['member_level_id'])) {
        update_user_meta($user_id, 'bcl_stripe_member_level_id', (int) $subscription['metadata']['member_level_id']);
    }

    $payment_method = $subscription['default_payment_method'] ?? null;
    if (is_array($payment_method) && !empty($payment_method['card'])) {
        bcl_stripe_store_card($user_id, $payment_method['card']);
    }
}

function bcl_stripe_sync_subscription($user_id, $subscription_id = null)
{
    $subscription_id = $subscription_id ?: get_user_meta($user_id, 'bcl_stripe_subscription_id', true);
    if (empty($subscription_id)) {
        return null;
    }

    $subscription = bcl_stripe_request('GET', 'subscriptions/' . $subscription_id, array(
        'expand' => array('default_payment_method', 'items.data.price'),
    ));

    if (is_wp_error($subscription)) {
        return $subscription;
    }

    bcl_stripe_store_subscription($user_id, $subscription);
    return $subscription;
}

// ---------------------------------------------------------------------------
// Stripe product per Member Level (kept in sync whenever the level is saved
// in the admin; see bcl_sync_member_level_stripe_product() in member-levels.php)
// ---------------------------------------------------------------------------

function bcl_stripe_product_id_for_level($level_id)
{
    $product_id = get_post_meta($level_id, 'bcl_stripe_product_id', true);
    if (!empty($product_id)) {
        return $product_id;
    }
    return bcl_stripe_sync_product_for_level($level_id);
}

function bcl_stripe_sync_product_for_level($level_id)
{
    $level = get_post($level_id);
    if (!$level || $level->post_type !== 'bcl_member_level') {
        return new WP_Error('bcl_stripe_invalid_level', 'Invalid member level');
    }
    if (empty(bcl_stripe_option('secret_key'))) {
        return new WP_Error('bcl_stripe_not_configured', 'Stripe secret key is not configured');
    }

    $fields = function_exists('get_fields') ? (get_fields($level_id) ?: array()) : array();
    $params = array(
        'name'     => $level->post_title,
        'active'   => $level->post_status === 'publish' ? 'true' : 'false',
        'metadata' => array('wp_level_id' => $level_id),
    );
    if (!empty($fields['description'])) {
        $params['description'] = $fields['description'];
    }

    $product_id = get_post_meta($level_id, 'bcl_stripe_product_id', true);
    $product    = !empty($product_id)
        ? bcl_stripe_request('POST', 'products/' . $product_id, $params)
        : bcl_stripe_request('POST', 'products', $params);

    if (is_wp_error($product)) {
        return $product;
    }

    update_post_meta($level_id, 'bcl_stripe_product_id', $product['id']);
    return $product['id'];
}

// ---------------------------------------------------------------------------
// REST endpoints
// ---------------------------------------------------------------------------

function bcl_stripe_config(WP_REST_Request $request)
{
    $response = new WP_REST_Response(array(
        'publishable_key' => bcl_stripe_option('publishable_key'),
        'currencies'      => bcl_stripe_enabled_currencies(),
    ), 200);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

function bcl_checkout_session(WP_REST_Request $request)
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return bcl_stripe_error_response('User not found', 404);
    }

    if (empty(bcl_stripe_option('secret_key'))) {
        return bcl_stripe_error_response('Stripe is not configured', 500);
    }

    $data = json_decode($request->get_body(), true);
    if (!is_array($data)) {
        return bcl_stripe_error_response('Invalid or empty request body', 400);
    }

    $level_id = absint($data['member_level_id'] ?? 0);
    $interval = ($data['interval'] ?? 'month') === 'year' ? 'year' : 'month';
    $currency = strtoupper(sanitize_text_field($data['currency'] ?? ''));

    if (empty($level_id) || empty($currency)) {
        return bcl_stripe_error_response('Missing required fields: member_level_id, currency', 400);
    }

    if (!in_array($currency, bcl_stripe_enabled_currencies(), true)) {
        return bcl_stripe_error_response('Currency not enabled', 400);
    }

    $level = get_post($level_id);
    if (!$level || $level->post_type !== 'bcl_member_level' || $level->post_status !== 'publish') {
        return bcl_stripe_error_response('Invalid member level', 400);
    }

    $price = bcl_member_level_price($level_id, $interval, $currency);
    if ($price === null || $price <= 0) {
        return bcl_stripe_error_response('This member level has no price set for that currency', 400);
    }
    $upfront_price = bcl_member_level_price($level_id, $interval, $currency, true);

    $customer_id = bcl_stripe_get_or_create_customer($user_id);
    if (is_wp_error($customer_id)) {
        return bcl_stripe_error_response($customer_id->get_error_message(), 500);
    }

    $product_id = bcl_stripe_product_id_for_level($level_id);
    if (is_wp_error($product_id)) {
        return bcl_stripe_error_response($product_id->get_error_message(), 500);
    }

    $success_url = esc_url_raw($data['success_url'] ?? home_url('/'));
    $cancel_url  = esc_url_raw($data['cancel_url'] ?? home_url('/'));

    // A one-time upfront fee, when set, rides on the same invoice as the
    // subscription's first recurring payment rather than being its own charge.
    $line_items = array(
        array(
            'quantity'   => 1,
            'price_data' => array(
                'currency'    => strtolower($currency),
                'unit_amount' => (int) round($price * 100),
                'recurring'   => array('interval' => $interval),
                'product'     => $product_id,
            ),
        ),
    );

    if ($upfront_price !== null && $upfront_price > 0) {
        $line_items[] = array(
            'quantity'   => 1,
            'price_data' => array(
                'currency'    => strtolower($currency),
                'unit_amount' => (int) round($upfront_price * 100),
                'product'     => $product_id,
            ),
        );
    }

    $session = bcl_stripe_request('POST', 'checkout/sessions', array(
        'mode'        => 'subscription',
        'customer'    => $customer_id,
        'success_url' => $success_url,
        'cancel_url'  => $cancel_url,
        'line_items'  => $line_items,
        'subscription_data' => array(
            'metadata' => array(
                'wp_user_id'      => $user_id,
                'member_level_id' => $level_id,
            ),
        ),
        'metadata' => array(
            'wp_user_id'      => $user_id,
            'member_level_id' => $level_id,
        ),
    ));

    if (is_wp_error($session)) {
        return bcl_stripe_error_response($session->get_error_message(), 502);
    }

    update_user_meta($user_id, 'bcl_stripe_member_level_id', $level_id);
    update_user_meta($user_id, 'bcl_stripe_billing_interval', $interval);
    update_user_meta($user_id, 'bcl_stripe_currency', $currency);

    $response = new WP_REST_Response(array(
        'checkout_session_id' => $session['id'],
        'checkout_url'        => $session['url'],
    ), 201);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

function bcl_stripe_verify_webhook_signature($payload, $sig_header, $secret)
{
    $pairs = array();
    foreach (explode(',', $sig_header) as $pair) {
        $parts = explode('=', $pair, 2);
        if (count($parts) === 2) {
            $pairs[$parts[0]] = $parts[1];
        }
    }

    if (empty($pairs['t']) || empty($pairs['v1'])) {
        return false;
    }

    // Reject stale timestamps to mitigate replay attacks (5 minute tolerance,
    // matching Stripe's own webhook library default).
    if (abs(time() - (int) $pairs['t']) > 300) {
        return false;
    }

    $expected = hash_hmac('sha256', $pairs['t'] . '.' . $payload, $secret);
    return hash_equals($expected, $pairs['v1']);
}

function bcl_stripe_webhook(WP_REST_Request $request)
{
    $payload    = $request->get_body();
    $sig_header = $request->get_header('stripe-signature');
    $secret     = bcl_stripe_option('webhook_secret');

    if (empty($secret)) {
        return bcl_stripe_error_response('Webhook not configured', 500);
    }

    if (empty($sig_header) || !bcl_stripe_verify_webhook_signature($payload, $sig_header, $secret)) {
        return bcl_stripe_error_response('Invalid signature', 400);
    }

    $event = json_decode($payload, true);
    if (!is_array($event) || empty($event['type'])) {
        return bcl_stripe_error_response('Invalid payload', 400);
    }

    $object = $event['data']['object'] ?? array();

    switch ($event['type']) {
        case 'checkout.session.completed':
            $user_id         = absint($object['metadata']['wp_user_id'] ?? 0);
            $subscription_id = $object['subscription'] ?? null;
            if ($user_id && $subscription_id) {
                $subscription = bcl_stripe_request('GET', 'subscriptions/' . $subscription_id, array(
                    'expand' => array('default_payment_method'),
                ));
                if (!is_wp_error($subscription)) {
                    bcl_stripe_store_subscription($user_id, $subscription);
                }
            }
            break;

        case 'customer.subscription.created':
        case 'customer.subscription.updated':
            $user_id = absint($object['metadata']['wp_user_id'] ?? 0) ?: bcl_stripe_user_id_for_customer($object['customer'] ?? '');
            if ($user_id) {
                bcl_stripe_store_subscription($user_id, $object);
            }
            break;

        case 'customer.subscription.deleted':
            $user_id = absint($object['metadata']['wp_user_id'] ?? 0) ?: bcl_stripe_user_id_for_customer($object['customer'] ?? '');
            if ($user_id) {
                update_user_meta($user_id, 'bcl_stripe_subscription_status', 'canceled');
            }
            break;

        case 'invoice.payment_failed':
            $user_id = bcl_stripe_user_id_for_customer($object['customer'] ?? '');
            if ($user_id) {
                update_user_meta($user_id, 'bcl_stripe_subscription_status', 'past_due');
            }
            break;

        case 'payment_method.attached':
        case 'customer.updated':
            $customer_id = $object['customer'] ?? ($object['id'] ?? '');
            $user_id     = bcl_stripe_user_id_for_customer($customer_id);
            if ($user_id && !empty($object['card'])) {
                bcl_stripe_store_card($user_id, $object['card']);
            }
            break;
    }

    $response = new WP_REST_Response(array('received' => true), 200);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

add_action('rest_api_init', function () {
    register_rest_route('bcl/v1', '/stripe-config', array(
        'methods'             => 'GET',
        'callback'            => 'bcl_stripe_config',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('bcl/v1', '/checkout-session', array(
        'methods'             => 'POST',
        'callback'            => 'bcl_checkout_session',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));

    register_rest_route('bcl/v1', '/stripe/webhook', array(
        'methods'             => 'POST',
        'callback'            => 'bcl_stripe_webhook',
        'permission_callback' => '__return_true',
    ));
});
