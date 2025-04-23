<?php
add_action('admin_menu', 'ai_captcha_add_admin_menu');
function ai_captcha_add_admin_menu()
{
    add_options_page(
        'AI Captcha Settings',
        'AI Captcha',
        'manage_options',
        'ai-captcha',
        'ai_captcha_settings_page'
    );
}

function ai_captcha_settings_page()
{
?>
    <div class="wrap">
        <h1><?php _e('AI Captcha Settings',AI_CAPTCHA_LANG);?></h1>
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

add_action('admin_init', 'ai_captcha_register_settings');
function ai_captcha_register_settings()
{
    register_setting('ai_captcha_settings', 'ai_captcha_site_key');
    register_setting('ai_captcha_settings', 'ai_captcha_secret_key');
    register_setting('ai_captcha_settings', 'ai_captcha_forms');

    add_settings_section(
        'ai_captcha_main',
        __('API Keys',AI_CAPTCHA_LANG),
        null,
        'ai-captcha'
    );

    add_settings_field(
        'ai_captcha_site_key',
        __('Site Key',AI_CAPTCHA_LANG),
        'ai_captcha_site_key_callback',
        'ai-captcha',
        'ai_captcha_main'
    );

    add_settings_field(
        'ai_captcha_secret_key',
        __('Secret Key',AI_CAPTCHA_LANG),
        'ai_captcha_secret_key_callback',
        'ai-captcha',
        'ai_captcha_main'
    );

    add_settings_field(
        'ai_captcha_forms',
        __('Enable on Forms' , AI_CAPTCHA_LANG),
        'ai_captcha_forms_callback',
        'ai-captcha',
        'ai_captcha_main'
    );
}

function ai_captcha_site_key_callback()
{
    $site_key = get_option('ai_captcha_site_key');
    echo '<input type="text" name="ai_captcha_site_key" value="' . esc_attr($site_key) . '" class="regular-text">';
}

function ai_captcha_secret_key_callback()
{
    $secret_key = get_option('ai_captcha_secret_key');
    echo '<input type="text" name="ai_captcha_secret_key" value="' . esc_attr($secret_key) . '" class="regular-text">';
}

function ai_captcha_forms_callback()
{
    $forms = get_option('ai_captcha_forms', ['login', 'register', 'comment', 'contact', 'woocommerce']);
    $available_forms = [
        'login'         => __('Login Form', AI_CAPTCHA_LANG),
        'register'      => __('Registration Form', AI_CAPTCHA_LANG),
        'lostpassword'  => __('Forget Form', AI_CAPTCHA_LANG),
        'comment'       => __('Comment Form', AI_CAPTCHA_LANG),
        'contact'       => __('Contact Form 7', AI_CAPTCHA_LANG),
        'woocommerce'   => __('WooCommerce Checkout', AI_CAPTCHA_LANG)
    ];

    foreach ($available_forms as $key => $label) {
        $checked = in_array($key, $forms) ? 'checked' : '';
        echo '<label><input type="checkbox" name="ai_captcha_forms[]" value="' . esc_attr($key) . '" ' . $checked . '> ' . esc_html($label) . '</label><br>';
    }
}
