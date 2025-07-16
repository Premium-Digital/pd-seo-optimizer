<?php
    use PdSeoOptimizer\Helpers;
?>
<tr class="plugin-settings__row">
    <th scope="row" class="plugin-settings__label">
        <label for="pd_seo_optimizer_font" class="plugin-settings__label-text">
            <?php _e('Font', 'pd-seo-optimizer'); ?>
        </label>
    </th>
    <td class="plugin-settings__input">
        <select name="pd_seo_optimizer_font" id="pd_seo_optimizer_font">
            <?php 
                $fonts = Helpers::getAvailableFonts();
                $selectedFont = get_option('pd_seo_optimizer_font', 'Arial');
                foreach ($fonts as $fontFile => $fontName) {
                    $fontName = pathinfo($fontFile, PATHINFO_FILENAME);
                    echo '<option value="' . esc_attr($fontName) . '" ' . selected($selectedFont, $fontName, false) . '>';
                    echo esc_html($fontName);
                    echo '</option>';
                }
            ?>
        </select>
    </td>
</tr>