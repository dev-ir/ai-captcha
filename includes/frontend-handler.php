<?php

/**
 * Frontend Handler for AI Captcha Plugin
 * Includes support for: Login, Register, Lost Password, Comments, WooCommerce, Contact Form 7
 */

add_action('lostpassword_form', 'ai_captcha_add_to_lostpassword');
add_action('retrieve_password', 'ai_captcha_verify_on_lostpassword');
add_action('wp_enqueue_scripts', 'ai_captcha_enqueue_scripts');
add_action('login_enqueue_scripts', 'ai_captcha_enqueue_scripts');

function ai_captcha_is_login_page()
{
    return in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php']);
}

function ai_captcha_is_lost_password_page()
{
    return isset($_GET['action']) && $_GET['action'] === 'lostpassword';
}

function ai_captcha_is_registration_page()
{
    return isset($_GET['action']) && $_GET['action'] === 'register';
}

function ai_captcha_is_woocommerce_active()
{
    return class_exists('WooCommerce');
}

function ai_captcha_is_checkout()
{
    return ai_captcha_is_woocommerce_active() && function_exists('is_checkout') && is_checkout();
}

function ai_captcha_is_account_page()
{
    return ai_captcha_is_woocommerce_active() && function_exists('is_account_page') && is_account_page();
}

function ai_captcha_is_needed()
{
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

    if (in_array('lostpassword', $forms) && ai_captcha_is_lost_password_page()) {
        return true;
    }

    if (in_array('comment', $forms) && is_singular() && comments_open()) {
        return true;
    }
    if (ai_captcha_is_woocommerce_active()) {
        if (in_array('woocommerce', $forms)) {
            if (ai_captcha_is_checkout() || ai_captcha_is_account_page()) {
                return true;
            }
        }
    }

    return false;
}
function ai_captcha_verify_on_lostpassword($user_login)
{
    if (in_array('lostpassword', (array) get_option('ai_captcha_forms', []))) {
        $secret_key = get_option('ai_captcha_secret_key');
        $response = isset($_POST['ai-captcha-response']) ? sanitize_text_field($_POST['ai-captcha-response']) : '';

        if (empty($response)) {
            wp_die(__('Please complete the captcha verification.', AI_CAPTCHA_LANG));
        }

        $url = 'https://aicaptcha.site/verify-token?secret=' . $secret_key . '&response=' . $response;
        $verify = wp_remote_get($url);

        if (is_wp_error($verify)) {
            error_log('AI Captcha Error: ' . $verify->get_error_message());
            return $user_login;
        }

        $result = json_decode(wp_remote_retrieve_body($verify));

        if (empty($result) || !isset($result->success) || !$result->success) {
            wp_die(__('Captcha verification failed. Please try again.', AI_CAPTCHA_LANG));
        }
    }
    return $user_login;
}

function ai_captcha_enqueue_scripts()
{
    if (ai_captcha_is_needed()) {
        wp_enqueue_script(
            'ai-captcha-api',
            'https://aicaptcha.site/api.js',
            [],
            null,
            ['in_footer' => true, 'strategy' => 'async']
        );

        wp_enqueue_style(
            'ai-captcha-style',
            esc_url(plugins_url('assets/css/ai-captcha.css', __DIR__)),
            [],
            AI_CAPTCHA_VERSION
        );
    }
}
function ai_captcha_add_to_form()
{
    $site_key = get_option('ai_captcha_site_key');
    if (empty($site_key)) {
        error_log('AI Captcha Error: Site Key is empty!');
        return;
    }

    echo '<div class="ai-captcha-container" style="margin: 1em 0;">';
    echo '<div data-sitekey="' . esc_attr($site_key) . '" class="ai-captcha"></div>';
    echo '</div>';
}


add_action('login_form', 'ai_captcha_add_to_login');
function ai_captcha_add_to_login()
{
    if (in_array('login', (array) get_option('ai_captcha_forms', [])) && !ai_captcha_is_lost_password_page() && !ai_captcha_is_registration_page()) {
        ai_captcha_add_to_form();
    }
}

add_action('lostpassword_form', 'ai_captcha_add_to_lostpassword');
function ai_captcha_add_to_lostpassword()
{
    if (in_array('lostpassword', (array) get_option('ai_captcha_forms', []))) {
        ai_captcha_add_to_form();
    }
}

add_action('register_form', 'ai_captcha_add_to_register');
function ai_captcha_add_to_register()
{
    if (in_array('register', (array) get_option('ai_captcha_forms', []))) {
        ai_captcha_add_to_form();
    }
}

add_action('comment_form_after_fields', 'ai_captcha_add_to_comments');
function ai_captcha_add_to_comments()
{
    if (in_array('comment', (array) get_option('ai_captcha_forms', []))) {
        ai_captcha_add_to_form();
    }
}

if (ai_captcha_is_woocommerce_active()) {
    add_action('woocommerce_after_checkout_billing_form', 'ai_captcha_add_to_woocommerce');
    add_action('woocommerce_login_form', 'ai_captcha_add_to_woocommerce');
    add_action('woocommerce_register_form', 'ai_captcha_add_to_woocommerce');

    function ai_captcha_add_to_woocommerce()
    {
        if (in_array('woocommerce', (array) get_option('ai_captcha_forms', []))) {
            ai_captcha_add_to_form();
        }
    }
}

if (defined('WPCF7_VERSION')) {
    add_filter('wpcf7_form_elements', 'ai_captcha_add_to_cf7');

    function ai_captcha_add_to_cf7($elements)
    {
        if (in_array('contact', (array) get_option('ai_captcha_forms', []))) {
            $elements .= ai_captcha_add_to_form();
        }
        return $elements;
    }
}
