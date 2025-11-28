<?php

/**
 * Plugin Name: PD Seo Optimizer
 * Description: Seo Optimizer for WordPress.
 * Version: 1.0.21
 * Author: kkarasiewicz
 */

namespace PdSeoOptimizer;
use PdSeoOptimizer\PluginManager;

if (!defined('WPINC')) {
    die;
}

if (!defined('PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH')) {
    define('PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
}

if (!defined('PD_SEO_OPTIMIZER_PLUGIN_DIR_URL')) {
    define('PD_SEO_OPTIMIZER_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
}

if (!defined('PD_SEO_OPTIMIZER_REPO_URL')) {
    define('PD_SEO_OPTIMIZER_REPO_URL', 'https://github.com/Premium-Digital/pd-seo-optimizer');
}

require PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . '/vendor/autoload.php';

// Load environment variables from .env file
if (file_exists(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . '.env')) {
    $env_file = PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . '.env';
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!getenv($key)) {
            putenv("{$key}={$value}");
        }
    }
}

class PdSeoOptimizer
{
    public function __construct()
    {
      load_plugin_textdomain('pd-seo-optimizer', false, dirname(plugin_basename(__FILE__)) . '/languages');
      new PluginManager();
    }
}

new PdSeoOptimizer();

register_activation_hook(__FILE__, ['PdSeoOptimizer\PluginManager', 'activate']);
register_deactivation_hook(__FILE__, ['PdSeoOptimizer\PluginManager', 'deactivate']);