<?php 
    $selected_position = get_option('pd_seo_optimizer_logo_position', 'bottom-right');
?>
<tr class="plugin-settings__row">
    <th scope="row" class="plugin-settings__label">
        <label for="pd_seo_optimizer_logo_position" class="plugin-settings__label-text">
            <?php _e('Logo Position', 'pd-seo-optimizer'); ?>
        </label>
    </th>
    <td class="plugin-settings__input">
        <select name="pd_seo_optimizer_logo_position" id="pd_seo_optimizer_logo_position">
            <option value="top-left" <?php selected($selected_position, 'top-left'); ?>><?php _e('Top Left', 'pd-seo-optimizer'); ?></option>
            <option value="top-right" <?php selected($selected_position, 'top-right'); ?>><?php _e('Top Right', 'pd-seo-optimizer'); ?></option>
            <option value="bottom-left" <?php selected($selected_position, 'bottom-left'); ?>><?php _e('Bottom Left', 'pd-seo-optimizer'); ?></option>
            <option value="bottom-right" <?php selected($selected_position, 'bottom-right'); ?>><?php _e('Bottom Right', 'pd-seo-optimizer'); ?></option>
        </select>
    </td>
</tr>