<?php
/**
 * Plugin Name: AI Captcha
 * Plugin URI: https://github.com/dev-ir/aiCaptcha
 * Description: AI-powered invisible captcha for WordPress and WooCommerce forms. Seamless protection without disrupting users.
 * Version: 1.0.1
 * Requires at least: 5.8
 * Requires PHP: 7.2
 * Author: DVHOST.IR
 * Author URI: https://dvhost.ir
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-captcha
 * Domain Path: /languages
 * @package ai-captcha
*/

defined('ABSPATH') or die('No script kiddies please!');

define('AI_CAPTCHA_VERSION', '1.0.1');
define('AI_CAPTCHA_PLUGIN_DIR', plugin_dir_path(__FILE__));

if (! defined('AI_CAPTCHA_PLUGIN_URL')) {
    define('AI_CAPTCHA_PLUGIN_URL', plugin_dir_url(AI_CAPTCHA_PLUGIN_DIR));
}

require_once AI_CAPTCHA_PLUGIN_DIR . 'includes/admin-settings.php';
require_once AI_CAPTCHA_PLUGIN_DIR . 'includes/frontend-handler.php';
require_once AI_CAPTCHA_PLUGIN_DIR . 'includes/verification.php';
