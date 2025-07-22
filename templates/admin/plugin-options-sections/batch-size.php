<tr class="plugin-settings__row">
    <th scope="row" class="plugin-settings__label">
        <label for="pd_seo_optimizer_batch_size" class="plugin-settings__label-text">
            <?php _e('Batch size', 'pd-seo-optimizer'); ?>
        </label>
    </th>
    <td class="plugin-settings__input">
        <input type="number" name="pd_seo_optimizer_batch_size" id="pd_seo_optimizer_batch_size" value="<?php echo esc_attr(get_option('pd_seo_optimizer_batch_size', 10)); ?>" min="5" max="50">
    </td>
</tr>