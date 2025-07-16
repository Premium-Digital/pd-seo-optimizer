<tr class="plugin-settings__row">
                <th scope="row" class="plugin-settings__label">
                    <label for="pd_seo_optimizer_author" class="plugin-settings__label-text">
                        <?php _e('Default Post Author', 'pd-seo-optimizer'); ?>
                    </label>
                </th>
                <td class="plugin-settings__input">
                    <select name="pd_seo_optimizer_author" id="pd_seo_optimizer_author" class="plugin-settings__select">
                        <?php
                        $admins = get_users(['role' => 'administrator']);
                        $selected_author = get_option('pd_seo_optimizer_author', get_current_user_id());

                        foreach ($admins as $admin) {
                            echo '<option value="' . esc_attr($admin->ID) . '" ' . selected($selected_author, $admin->ID, false) . '>';
                            echo esc_html($admin->display_name);
                            echo '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>