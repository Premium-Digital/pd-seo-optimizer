<?php

namespace PdSeoOptimizer;

use PdSeoOptimizer\Actions;
use PdSeoOptimizer\RestRoutes;
use PdSeoOptimizer\Settings;
use PdSeoOptimizer\Logger;
use PdSeoOptimizer\Filters;
use PdSeoOptimizer\Notifier;

class PluginManager
{
    private $logger;
    private $actions;
    private $settings;
    private $filters;
    private $notifier;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
        $this->actions = new Actions();
        $this->settings = new Settings();
        $this->filters = new Filters();
        $this->notifier = new Notifier();
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