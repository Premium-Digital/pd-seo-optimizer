<?php
    $bgColor = get_option('pd_seo_optimizer_bg_color', 'rgb(0,0,0)');
?>

<tr class="plugin-settings__row">
    <th scope="row" class="plugin-settings__label">
        <label for="pd_seo_optimizer_bg_color" class="plugin-settings__label-text">
            <?php _e('Text Background Color', 'pd-seo-optimizer'); ?>
        </label>
    </th>
    <td class="plugin-settings__input">
        <input type="color" name="pd_seo_optimizer_bg_color" id="pd_seo_optimizer_bg_color" value="<?php echo esc_attr($bgColor); ?>">
    </td>
</tr>