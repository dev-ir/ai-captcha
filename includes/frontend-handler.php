<?php
/**
 * AI Captcha Plugin - Secure Form Protection
 * 
 * @package AI_Captcha
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

// Actions
add_action('lostpassword_form', 'ai_captcha_add_to_lostpassword');
add_action('retrieve_password', 'ai_captcha_verify_on_lostpassword');
add_action('wp_enqueue_scripts', 'ai_captcha_enqueue_scripts');
add_action('login_enqueue_scripts', 'ai_captcha_enqueue_scripts');

/**
 * Check if current page is login page
 */
function ai_captcha_is_login_page() {
    return in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php']);
}

/**
 * Check if current page is lost password page
 */
function ai_captcha_is_lost_password_page() {
    return isset($_GET['action']) && 'lostpassword' === $_GET['action'];
}

/**
 * Check if current page is registration page
 */
function ai_captcha_is_registration_page() {
    return isset($_GET['action']) && 'register' === $_GET['action'];
}

/**
 * Check if WooCommerce is active
 */
function ai_captcha_is_woocommerce_active() {
    return class_exists('WooCommerce');
}

/**
 * Check if current page is WooCommerce checkout
 */
function ai_captcha_is_checkout() {
    return ai_captcha_is_woocommerce_active() && function_exists('is_checkout') && is_checkout();
}

/**
 * Check if current page is WooCommerce account page
 */
function ai_captcha_is_account_page() {
    return ai_captcha_is_woocommerce_active() && function_exists('is_account_page') && is_account_page();
}

/**
 * Determine if CAPTCHA is needed on current page
 */
function ai_captcha_is_needed() {
    $forms = (array) get_option('ai_captcha_forms', []);

    if (empty($forms)) {
        return false;
    }

    if (in_array('login', $forms) && ai_captcha_is_login_page() && !ai_captcha_is_lost_password_page() && !ai_captcha_is_registration_page()) {
        return true;
    }

    if (in_array('lostpassword', $forms) && ai_captcha_is_lost_password_page()) {
        return true;
    }

    if (in_array('register', $forms) && ai_captcha_is_registration_page()) {
        return true;
    }

    if (in_array('comment', $forms) && is_singular() && comments_open()) {
        return true;
    }

    if (ai_captcha_is_woocommerce_active() && in_array('woocommerce', $forms)) {
        if (ai_captcha_is_checkout() || ai_captcha_is_account_page()) {
            return true;
        }
    }

    return false;
}

/**
 * Verify CAPTCHA on lost password form
 */
function ai_captcha_verify_on_lostpassword($user_login) {
    if (!in_array('lostpassword', (array) get_option('ai_captcha_forms', []))) {
        return $user_login;
    }

    // Verify nonce
    if (!isset($_POST['ai_captcha_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ai_captcha_nonce'])), 'ai_captcha_lostpassword_action')) {
        wp_die(
            esc_html__('Security verification failed. Please try again.', 'ai-captcha')
        );
    }

    $secret_key = sanitize_text_field(get_option('ai_captcha_secret_key'));
    $response = isset($_POST['ai-captcha-response']) ? sanitize_text_field(wp_unslash($_POST['ai-captcha-response'])) : '';

    if (empty($response)) {
        wp_die(
            esc_html__('Please complete the CAPTCHA verification.', 'ai-captcha')
        );
    }

    $url = esc_url_raw('https://aicaptcha.site/verify-token?secret=' . $secret_key . '&response=' . $response);
    $verify = wp_safe_remote_get($url);

    if (is_wp_error($verify)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // error_log('AI Captcha Error: ' . $verify->get_error_message());
        }
        return new WP_Error('captcha_error', esc_html__('CAPTCHA verification failed. Please try again.', 'ai-captcha'));
    }

    $result = json_decode(wp_remote_retrieve_body($verify));

    if (empty($result) || !isset($result->success) || !$result->success) {
        wp_die(
            esc_html__('CAPTCHA verification failed. Please try again.', 'ai-captcha')
        );
    }

    return $user_login;
}

/**
 * Enqueue required scripts and styles
 */
function ai_captcha_enqueue_scripts() {
    if (!ai_captcha_is_needed()) {
        return;
    }

    wp_enqueue_script(
        'ai-captcha-api',
        esc_url('https://aicaptcha.site/api.js'),
        [],
        null,
        [
            'in_footer' => true,
            'strategy' => 'async'
        ]
    );

    wp_enqueue_style(
        'ai-captcha-style',
        esc_url(plugins_url('assets/css/ai-captcha.css', __FILE__)),
        [],
        AI_CAPTCHA_VERSION
    );
}

/**
 * Add CAPTCHA field to forms
 */
function ai_captcha_add_to_form() {
    $site_key = sanitize_text_field(get_option('ai_captcha_site_key'));
    
    if (empty($site_key)) {
        if (current_user_can('manage_options')) {
            add_settings_error(
                'ai_captcha_messages',
                'ai_captcha_missing_key',
                esc_html__('AI Captcha Error: Site Key is empty! Please configure it in settings.', 'ai-captcha'),
                'error'
            );
        }
        return;
    }

    echo '<div class="ai-captcha-container" style="margin: 1em 0;">';
    echo '<div data-sitekey="' . esc_attr($site_key) . '" class="ai-captcha"></div>';
    echo '</div>';
}

/**
 * Add CAPTCHA to login form
 */
add_action('login_form', 'ai_captcha_add_to_login');
function ai_captcha_add_to_login() {
    if (in_array('login', (array) get_option('ai_captcha_forms', []))) {
        ai_captcha_add_to_form();
        wp_nonce_field('ai_captcha_login_action', 'ai_captcha_nonce', true, true);
    }
}

/**
 * Add CAPTCHA to lost password form
 */
function ai_captcha_add_to_lostpassword() {
    if (in_array('lostpassword', (array) get_option('ai_captcha_forms', []))) {
        ai_captcha_add_to_form();
        wp_nonce_field('ai_captcha_lostpassword_action', 'ai_captcha_nonce', true, true);
    }
}

/**
 * Add CAPTCHA to registration form
 */
add_action('register_form', 'ai_captcha_add_to_register');
function ai_captcha_add_to_register() {
    if (in_array('register', (array) get_option('ai_captcha_forms', []))) {
        ai_captcha_add_to_form();
        wp_nonce_field('ai_captcha_register_action', 'ai_captcha_nonce', true, true);
    }
}

/**
 * Add CAPTCHA to comment form
 */
add_action('comment_form_after_fields', 'ai_captcha_add_to_comments');
function ai_captcha_add_to_comments() {
    if (in_array('comment', (array) get_option('ai_captcha_forms', []))) {
        ai_captcha_add_to_form();
        wp_nonce_field('ai_captcha_comment_action', 'ai_captcha_nonce', true, true);
    }
}

/**
 * WooCommerce integration
 */
if (ai_captcha_is_woocommerce_active()) {
    add_action('woocommerce_after_checkout_billing_form', 'ai_captcha_add_to_woocommerce');
    add_action('woocommerce_login_form', 'ai_captcha_add_to_woocommerce');
    add_action('woocommerce_register_form', 'ai_captcha_add_to_woocommerce');

    function ai_captcha_add_to_woocommerce() {
        if (in_array('woocommerce', (array) get_option('ai_captcha_forms', []))) {
            ai_captcha_add_to_form();
            wp_nonce_field('ai_captcha_woocommerce_action', 'ai_captcha_nonce', true, true);
        }
    }
}

/**
 * Contact Form 7 integration
 */
if (defined('WPCF7_VERSION')) {
    add_filter('wpcf7_form_elements', 'ai_captcha_add_to_cf7');

    function ai_captcha_add_to_cf7($elements) {
        if (!in_array('contact', (array) get_option('ai_captcha_forms', []))) {
            return $elements;
        }

        ob_start();
        ai_captcha_add_to_form();
        wp_nonce_field('ai_captcha_cf7_action', 'ai_captcha_nonce', true, true);
        $captcha = ob_get_clean();

        return $elements . $captcha;
    }
}