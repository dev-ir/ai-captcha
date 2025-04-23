<?php
add_filter('wp_authenticate_user', 'ai_captcha_verify_login', 10, 2);
function ai_captcha_verify_login($user, $password) {
    if (in_array('login', (array) get_option('ai_captcha_forms', []))) {
        // Verify nonce with proper sanitization
        if (!isset($_POST['ai_captcha_nonce']) || 
            !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['ai_captcha_nonce'])),
                'ai_captcha_login_action'
            )) {
            wp_die(
                esc_html__('Security check failed. Please try again.', 'ai-captcha')
            );
        }

        $secret_key = sanitize_text_field(get_option('ai_captcha_secret_key'));
        $response = isset($_POST['ai-captcha-response']) 
            ? sanitize_text_field(wp_unslash($_POST['ai-captcha-response'])) 
            : '';
        
        if (empty($response)) {
            wp_die(
                esc_html__('Please complete the captcha verification.', 'ai-captcha')
            );
        }

        $url = esc_url_raw('https://aicaptcha.site/verify-token?secret=' . $secret_key . '&response=' . $response);
        $verify = wp_safe_remote_get($url);
        
        if (is_wp_error($verify)) {
            wp_die(
                esc_html__('captcha_server_error: Error connecting to the CAPTCHA server.', 'ai-captcha')
            );
        }

        $result = json_decode(wp_remote_retrieve_body($verify));
        
        if (empty($result) || !isset($result->success)) {
            wp_die(
                esc_html__('Error: Invalid CAPTCHA response.', 'ai-captcha')
            );
        }
        
        if (!$result->success) {
            wp_die(
                wp_kses_post('<strong>Error</strong>: AI Captcha verification failed!')
            );
        }
    }
    return $user;
}