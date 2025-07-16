<tr class="plugin-settings__row">
                <th scope="row" class="plugin-settings__label">
                    <label for="pd_seo_optimizer_enable_feature" class="plugin-settings__label-text">
                        <?php _e('Enable autogenerate post feature', 'pd-seo-optimizer'); ?>
                    </label>
                </th>
                <td class="plugin-settings__input">
                    <input type="checkbox" name="pd_seo_optimizer_enable_feature" value="1" id="pd_seo_optimizer_enable_feature"
                           class="plugin-settings__checkbox" <?php checked(1, get_option('pd_seo_optimizer_enable_feature'), true); ?> />
                </td>
            </tr>