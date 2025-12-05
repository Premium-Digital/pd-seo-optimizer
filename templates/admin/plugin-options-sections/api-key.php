<?php
    $openAiApiKey = get_option('pd_seo_optimizer_openai_api_key', '');
?>

<tr class="plugin-settings__row">
    <th scope="row" class="plugin-settings__label">
        <label for="pd_seo_optimizer_openai_api_key" class="plugin-settings__label-text">
            <?php echo __('OpenAi key', 'pd-seo-optimizer');?>
        </label>
    </th>
    <td class="plugin-settings__input">
        <?php if (!empty($openAiApiKey)) : ?>
            <div style="display:flex;align-items:center;gap:12px;">
                <span><?php echo esc_html__('API key is set', 'pd-seo-optimizer'); ?></span>
                <button type="button" class="button" id="pd-seo-api-change-button"><?php echo esc_html__('Change key', 'pd-seo-optimizer'); ?></button>
            </div>
            <div id="pd-seo-api-key-input" style="margin-top:8px;display:none;">
                <input type="password" name="pd_seo_optimizer_openai_api_key" id="pd_seo_optimizer_openai_api_key" value="" placeholder="Enter new API key">
                <label style="margin-left:8px;font-weight:normal;">
                    <input type="checkbox" name="pd_seo_optimizer_openai_api_key_clear" value="1"> <?php echo esc_html__('Remove existing key', 'pd-seo-optimizer'); ?>
                </label>
            </div>
        <?php else: ?>
            <input type="password" name="pd_seo_optimizer_openai_api_key" id="pd_seo_optimizer_openai_api_key" value="" placeholder="Enter API key">
        <?php endif; ?>
    </td>
</tr>