<?php
    $textColor = get_option('pd_seo_optimizer_text_color');
?>
<tr class="plugin-settings__row">
    <th scope="row" class="plugin-settings__label">
        <label for="pd_seo_optimizer_text_color" class="plugin-settings__label-text">
            <?php _e('Text Color', 'pd-seo-optimizer'); ?>
        </label>
    </th>
    <td class="plugin-settings__input">
        <input type="color" name="pd_seo_optimizer_text_color" id="pd_seo_optimizer_text_color" value="<?php echo esc_attr($textColor); ?>">
    </td>
</tr>