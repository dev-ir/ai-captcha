<?php

/**
 * Plugin Name: AI Captcha Protection
 * Plugin URI: https://dvhost.ir
 * Description: AI-powered captcha for all WordPress forms.
 * Requires at least: 5.8
 * Requires PHP: 5.6
 * Version: 1.0.0
 * Author URI: DVHOST_CLOUD
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: ai-capcha
 * Domain Path: /languages
 *
 * @package ai-capcha
 */
defined('ABSPATH') or die('No script kiddies please!');

define('AI_CAPTCHA_VERSION', '1.0.0');
define('AI_CAPTCHA_PLUGIN_DIR', plugin_dir_path(__FILE__));

if (! defined('AI_CAPTCHA_PLUGIN_URL')) {
    define('AI_CAPTCHA_PLUGIN_URL', plugin_dir_url(AI_CAPTCHA_PLUGIN_DIR));
}

if (! defined('AI_CAPTCHA_LANG')) {
    define('AI_CAPTCHA_LANG', 'ai-captcha');
}

require_once AI_CAPTCHA_PLUGIN_DIR . 'includes/admin-settings.php';
require_once AI_CAPTCHA_PLUGIN_DIR . 'includes/frontend-handler.php';
require_once AI_CAPTCHA_PLUGIN_DIR . 'includes/verification.php';
