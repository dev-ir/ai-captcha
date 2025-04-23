<?php
add_action('admin_menu', 'ai_captcha_add_admin_menu');
function ai_captcha_add_admin_menu() {
    add_options_page(
        __('AI Captcha Settings', 'ai-captcha'),
        __('AI Captcha', 'ai-captcha'),
        'manage_options',
        'ai-captcha',
        'ai_captcha_settings_page'
    );
}

function ai_captcha_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(__('AI Captcha Settings', 'ai-captcha')); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ai_captcha_settings');
            do_settings_sections('ai-captcha');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// توابع Sanitize اختصاصی
function ai_captcha_sanitize_site_key($input) {
    return sanitize_text_field($input);
}

function ai_captcha_sanitize_secret_key($input) {
    return sanitize_text_field($input);
}

function ai_captcha_sanitize_forms($input) {
    if (!is_array($input)) {
        return [];
    }
    
    $allowed_forms = ['login', 'register', 'lostpassword', 'comment', 'contact', 'woocommerce'];
    $sanitized = [];
    
    foreach ($input as $form) {
        $form = sanitize_text_field($form);
        if (in_array($form, $allowed_forms)) {
            $sanitized[] = $form;
        }
    }
    
    return $sanitized;
}

add_action('admin_init', 'ai_captcha_register_settings');
function ai_captcha_register_settings() {
    register_setting(
        'ai_captcha_settings',
        'ai_captcha_site_key',
        [
            'sanitize_callback' => 'ai_captcha_sanitize_site_key',
            'show_in_rest' => false
        ]
    );

    register_setting(
        'ai_captcha_settings',
        'ai_captcha_secret_key',
        [
            'sanitize_callback' => 'ai_captcha_sanitize_secret_key',
            'show_in_rest' => false
        ]
    );

    register_setting(
        'ai_captcha_settings',
        'ai_captcha_forms',
        [
            'sanitize_callback' => 'ai_captcha_sanitize_forms',
            'show_in_rest' => false
        ]
    );

    add_settings_section(
        'ai_captcha_main',
        __('API Keys', 'ai-captcha'),
        null,
        'ai-captcha'
    );

    add_settings_field(
        'ai_captcha_site_key',
        __('Site Key', 'ai-captcha'),
        'ai_captcha_site_key_callback',
        'ai-captcha',
        'ai_captcha_main'
    );

    add_settings_field(
        'ai_captcha_secret_key',
        __('Secret Key', 'ai-captcha'),
        'ai_captcha_secret_key_callback',
        'ai-captcha',
        'ai_captcha_main'
    );

    add_settings_field(
        'ai_captcha_forms',
        __('Enable on Forms', 'ai-captcha'),
        'ai_captcha_forms_callback',
        'ai-captcha',
        'ai_captcha_main'
    );
}

function ai_captcha_site_key_callback() {
    $site_key = get_option('ai_captcha_site_key');
    printf(
        '<input type="text" name="ai_captcha_site_key" value="%s" class="regular-text">',
        esc_attr($site_key)
    );
}

function ai_captcha_secret_key_callback() {
    $secret_key = get_option('ai_captcha_secret_key');
    printf(
        '<input type="text" name="ai_captcha_secret_key" value="%s" class="regular-text">',
        esc_attr($secret_key)
    );
}

function ai_captcha_forms_callback() {
    $forms = (array) get_option('ai_captcha_forms', ['login', 'register', 'comment', 'contact', 'woocommerce']);
    $available_forms = [
        'login'         => __('Login Form', 'ai-captcha'),
        'register'      => __('Registration Form', 'ai-captcha'),
        'lostpassword'  => __('Forget Form', 'ai-captcha'),
        'comment'       => __('Comment Form', 'ai-captcha'),
        'contact'       => __('Contact Form 7', 'ai-captcha'),
        'woocommerce'   => __('WooCommerce Checkout', 'ai-captcha')
    ];

    foreach ($available_forms as $key => $label) {
        $checked = in_array($key, $forms) ? 'checked' : '';
        printf(
            '<label><input type="checkbox" name="ai_captcha_forms[]" value="%s" %s> %s</label><br>',
            esc_attr($key),
            esc_attr($checked),
            esc_html($label)
        );
    }
}