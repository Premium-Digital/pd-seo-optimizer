<div class="plugin-settings">
    <h1 class="plugin-settings__title"><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post" action="options.php" class="plugin-settings__form">
        <?php
        settings_fields('pd_seo_optimizer_options_group');
        do_settings_sections('pd-seo-optimizer');
        ?>

        <table class="plugin-settings__table">
            <?php
                include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . "templates/admin/plugin-options-sections/api-key.php");
                // include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . "templates/admin/plugin-options-sections/enable-plugin.php");
                // include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . "templates/admin/plugin-options-sections/post-status.php");
                // include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . "templates/admin/plugin-options-sections/post-author.php");
                // include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . "templates/admin/plugin-options-sections/logo.php");
                // include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . "templates/admin/plugin-options-sections/logo-position.php");
                // include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . "templates/admin/plugin-options-sections/font-family.php");
                // include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . "templates/admin/plugin-options-sections/font-size.php");
                // include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . "templates/admin/plugin-options-sections/text-color.php");
                // include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . "templates/admin/plugin-options-sections/background-color.php");
                // include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . "templates/admin/plugin-options-sections/text-position.php");
            ?>
        </table>

        <?php submit_button('Save Settings', 'primary', 'submit', true, ['class' => 'plugin-settings__submit']); ?>
    </form>
</div>