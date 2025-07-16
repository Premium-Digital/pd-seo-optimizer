<?php

namespace PdSeoOptimizer;

class Notifier
{
    public function __construct()
    {
        add_action('admin_notices', [$this, 'showRankMathMissingNotice']);
    }

    public static function showRankMathMissingNotice()
    {
        if (!is_plugin_active('seo-by-rank-math/rank-math.php')) {
            include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . "templates/admin/notifications/rank-math-missing-notification.php");
        }
    }
}