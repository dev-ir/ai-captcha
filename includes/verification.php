<?php
add_filter('wp_authenticate_user', 'ai_captcha_verify_login', 10, 2);
function ai_captcha_verify_login($user, $password)
{
    if (in_array('login', get_option('ai_captcha_forms', []))) {
        $secret_key = get_option('ai_captcha_secret_key');
        $response = isset($_POST['ai-captcha-response']) ? sanitize_text_field($_POST['ai-captcha-response']) : '';
        if (empty($response)) {
            return new WP_Error('captcha_empty', __('Please complete the captcha verification.', AI_CAPTCHA_LANG));
        }

        $url = 'https://aicaptcha.site/verify-token?secret=' . $secret_key . '&response=' . $response;
        $verify = wp_remote_get($url);
        if (is_wp_error($verify)) {
            return new WP_Error('captcha_server_error', __('Error connecting to the CAPTCHA server.', AI_CAPTCHA_LANG));
        }
        $result = json_decode(wp_remote_retrieve_body($verify));
        if (!$result->success) {
            return new WP_Error('captcha_failed', __('<strong>Error</strong>: AI Captcha verification failed!', AI_CAPTCHA_LANG));
        }
    }
    return $user;
}