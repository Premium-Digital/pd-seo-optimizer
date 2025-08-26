<?php

namespace PdSeoOptimizer;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Updater
{
    public static function init()
    {
        if (!defined('PD_SEO_OPTIMIZER_REPO_URL')) {
            error_log('[PD Seo Optimizer] Constant PD_SEO_OPTIMIZER_REPO_URL is not defined.');
            return;
        }

        if (!class_exists(PucFactory::class)) {
            error_log('[PD Seo Optimizer] Plugin Update Checker is not available.');
            return;
        }

        $pluginFile = PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . '/pd-seo-optimizer.php';

        $updateChecker = PucFactory::buildUpdateChecker(
            PD_SEO_OPTIMIZER_REPO_URL,
            $pluginFile,
            'pd-seo-optimizer'
        );

        $updateChecker->getVcsApi()->enableReleaseAssets();
    }
}