import '../scss/admin.scss';
import { initMediaUploader } from './admin/mediaUploader.js';
jQuery.noConflict();

jQuery(document).ready(function() {
    initMediaUploader(
        'pd_seo_optimizer_post_logo',
        'preview_image',
        'upload_image_button',
        'remove_image_button'
    );
});