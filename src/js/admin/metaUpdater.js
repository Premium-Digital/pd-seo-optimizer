import $ from 'jquery';

import { showButton } from './showButton';

export function initMetaGeneration() {

    $('#doaction, #doaction2').on('click', function(e) {
        const $form = $(this).closest('form');
        const action = $form.find('select[name="action"]').val();

        if (action === 'generate_meta' || action === 'generate_meta_title' || action === 'generate_meta_description') {
            e.preventDefault();

            let postIds = [];
            $('tbody th.check-column input[type="checkbox"]:checked').each(function() {
                postIds.push(parseInt($(this).val()));
            });

            if (postIds.length === 0) {
                alert('Proszę zaznacz przynajmniej jeden post.');
                return;
            }

            // determine which server action to call
            let serverAction = 'pd_generate_meta_batch';
            if (action === 'generate_meta_title') serverAction = 'pd_generate_meta_title_batch';
            if (action === 'generate_meta_description') serverAction = 'pd_generate_meta_description_batch';

            runMetaGenerationBatch(postIds, pdSeoMetaData, serverAction, 'post');
        }

        if (action === 'generate_meta_terms') {
            e.preventDefault();

            let termIds = [];
            $('tbody th.check-column input[type="checkbox"]:checked').each(function() {
                termIds.push(parseInt($(this).val()));
            });

            if (termIds.length === 0) {
                alert('Proszę zaznacz przynajmniej jedną kategorię/termin.');
                return;
            }

            runMetaGenerationBatch(termIds, pdSeoMetaData, null, "term");
        }
    });

    function runMetaGenerationBatch(ids, pdSeoMetaData, serverAction = null, context = 'post') {
        let textHeading = "";
        switch (serverAction) {
            case 'pd_generate_meta_title_batch':
                textHeading = "Generowanie meta tytułów";
                break;
            case 'pd_generate_meta_description_batch':
                textHeading = "Generowanie meta opisów";
                break;
            default:
                textHeading = "Generowanie meta tytułów i opisów";
        }
        const $popup = $('#pd-seo-meta-generator-popup-overlay');
        $(".pd-seo-meta-popup-heading").text(textHeading);
        $popup.css('display', 'flex');
        const batchSize = pdSeoMetaData.batchSize;
        let currentIndex = 0;
        const total = ids.length;

        function processBatch() {
            const batch = ids.slice(currentIndex, currentIndex + batchSize);
            if (!batch.length) {
                $('#pd-seo-meta-generator-popup-progress').text('Zakończono!');
                showButton();
                return;
            }

            $.post(pdSeoMetaData.ajaxurl, {
                action: serverAction || (context === 'post' ? 'pd_generate_meta_batch' : 'pd_generate_meta_terms_batch'),
                nonce: pdSeoMetaData.nonce,
                ids: JSON.stringify(batch)
            }, function (res) {
                currentIndex += batchSize;
                const percent = Math.min(100, Math.round((currentIndex / total) * 100));
                $('#pd-seo-meta-generator-popup-progress').text(`${percent}%`);
                processBatch();
            }).fail(() => {
                $('#pd-seo-meta-generator-popup-progress').text('Wystąpił błąd podczas generowania.');
                showButton();
            });
        }

        processBatch();
    }
}