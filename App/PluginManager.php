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
    //private $logger;
    private $actions;
    private $settings;
    private $filters;
    private $notifier;

    public function __construct()
    {
        global $wpdb;

        //$this->logger = Logger::getInstance($wpdb);
        $this->actions = new Actions();
        $this->settings = new Settings();
        $this->filters = new Filters();
        $this->notifier = new Notifier();
    }

    public static function activate()
    {
        \flush_rewrite_rules();
    }

}