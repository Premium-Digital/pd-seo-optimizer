<?php

namespace PdSeoOptimizer;

use PdSeoOptimizer\Actions;

use PdSeoOptimizer\Settings;
use PdSeoOptimizer\Logger;
use PdSeoOptimizer\Filters;
use PdSeoOptimizer\Notifier;
use PdSeoOptimizer\Updater;

class PluginManager
{
    private $logger;
    private $actions;
    private $settings;
    private $filters;
    private $notifier;

    public function __construct()
    {
        Updater::init();
        Logger::getInstance();
        new Actions();
        new Settings();
        new Filters();
        new Notifier();
    }

    public static function activate()
    {
        \flush_rewrite_rules();
    }

    public static function deactivate()
    {
        \delete_site_option('pd_seo_optimizer_post_api_key');
        \flush_rewrite_rules();
    }

}