<tr class="plugin-settings__row">
    <th scope="row" class="plugin-settings__label">
        <label for="pd_seo_optimizer_font_size" class="plugin-settings__label-text">
            <?php _e('Font Size', 'pd-seo-optimizer'); ?>
        </label>
    </th>
    <td class="plugin-settings__input">
        <input type="number" name="pd_seo_optimizer_font_size" id="pd_seo_optimizer_font_size" value="<?php echo esc_attr(get_option('pd_seo_optimizer_font_size', 40)); ?>" min="10" max="100">
    </td>
</tr>