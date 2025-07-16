<?php 
    $selectedTextPosition = get_option('pd_seo_optimizer_text_position', 'top');
?>
<tr class="plugin-settings__row">
    <th scope="row" class="plugin-settings__label">
        <label for="pd_seo_optimizer_text_position" class="plugin-settings__label-text">
            <?php _e('Text Position', 'pd-seo-optimizer'); ?>
        </label>
    </th>
    <td class="plugin-settings__input">
        <select name="pd_seo_optimizer_text_position" id="pd_seo_optimizer_text_position">
            <option value="top" <?php selected($selectedTextPosition, 'top'); ?>><?php _e('Top', 'pd-seo-optimizer'); ?></option>
            <option value="middle" <?php selected($selectedTextPosition, 'middle'); ?>><?php _e('Middle', 'pd-seo-optimizer'); ?></option>
            <option value="bottom" <?php selected($selectedTextPosition, 'bottom'); ?>><?php _e('Bottom', 'pd-seo-optimizer'); ?></option>
        </select>
    </td>
</tr>