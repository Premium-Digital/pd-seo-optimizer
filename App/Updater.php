<?php

namespace PdExtraWidgets;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Updater
{
    public static function init()
    {
        if (!defined('PD_EXTRA_WIDGETS_REPO_URL')) {
            error_log('[PD Extra Widgets] Constant PD_EXTRA_WIDGETS_REPO_URL is not defined.');
            return;
        }

        if (!class_exists(PucFactory::class)) {
            error_log('[PD Extra Widgets] Plugin Update Checker is not available.');
            return;
        }

        $pluginFile = PD_EXTRA_WIDGETS_PLUGIN_DIR_PATH . '/pd-seo-optimizer.php';

        $updateChecker = PucFactory::buildUpdateChecker(
            PD_EXTRA_WIDGETS_REPO_URL,
            $pluginFile,
            'pd-seo-optimizer'
        );

        $updateChecker->getVcsApi()->enableReleaseAssets();
    }
}