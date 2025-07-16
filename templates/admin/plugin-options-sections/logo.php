<tr class="plugin-settings__row">
                <th scope="row" class="plugin-settings__label">
                    <label for="pd_seo_optimizer_post_logo" class="plugin-settings__label-text">
                        <?php _e('Logo', 'pd-seo-optimizer'); ?>
                    </label>
                </th>
                <td class="plugin-settings__input">
                    <input type="hidden" name="pd_seo_optimizer_post_logo" id="pd_seo_optimizer_post_logo" value="<?php echo esc_attr(get_option('pd_seo_optimizer_post_logo')); ?>">
                    <img id="preview_image" src="<?php echo esc_url(get_option('pd_seo_optimizer_post_logo')); ?>" style="max-width: 100px; display: <?php echo get_option('pd_seo_optimizer_post_logo') ? 'block' : 'none'; ?>;">
                    <button type="button" id="upload_image_button" class="button">Change Image</button>
                    <button type="button" id="remove_image_button" class="button" style="display: none;">Remove Image</button>
                </td>
            </tr>