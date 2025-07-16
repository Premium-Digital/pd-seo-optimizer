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
        <input type="text" name="pd_seo_optimizer_openai_api_key" id="pd_seo_optimizer_openai_api_key" value="<?php echo esc_attr($openAiApiKey); ?>">
    </td>
</tr>