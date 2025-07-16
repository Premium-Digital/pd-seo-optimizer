<tr class="plugin-settings__row">
                <th scope="row" class="plugin-settings__label">
                    <label for="pd_seo_optimizer_post_status" class="plugin-settings__label-text">
                        <?php _e('Default Post Status', 'pd-seo-optimizer'); ?>
                    </label>
                </th>
                <td class="plugin-settings__input">
                    <select name="pd_seo_optimizer_post_status" id="pd_seo_optimizer_post_status" class="plugin-settings__select">
                        <option value="publish" <?php selected(get_option('pd_seo_optimizer_post_status'), 'publish'); ?>>
                            <?php _e('Publish', 'pd-seo-optimizer'); ?>
                        </option>
                        <option value="draft" <?php selected(get_option('pd_seo_optimizer_post_status'), 'draft'); ?>>
                            <?php _e('Draft', 'pd-seo-optimizer'); ?>
                        </option>
                    </select>
                </td>
            </tr>