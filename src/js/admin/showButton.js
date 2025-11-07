import $ from 'jquery';

export function showButton(){
    $('.pd-seo-meta-popup-close-button').show();
    $('.pd-seo-meta-popup-close-button').on('click', function() {
        $('.pd-seo-meta-popup-close-button').remove();
        location.reload();
    });
}