import $ from 'jquery';

export function showButton(){
    $('.pd-seo-meta-popup-close-button').show();
    $('.pd-seo-meta-popup-close-button').on('click', function() {
        $('#pd-seo-meta-generator-popup-overlay').remove();
        location.reload();
    });
}