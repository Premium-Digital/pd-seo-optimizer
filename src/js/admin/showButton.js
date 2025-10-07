import $ from 'jquery';

export function showButton(){
    $('.pd-seo-meta-popup-close-button').show();
    $('.pd-seo-meta-popup-close-button').on('click', function() {
        $popup.remove();
        location.reload();
    });
}