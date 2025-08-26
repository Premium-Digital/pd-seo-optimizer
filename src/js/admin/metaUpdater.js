import $ from 'jquery';

export function initMetaGeneration() {

    $('#doaction, #doaction2').on('click', function(e) {
        const $form = $(this).closest('form');
        const action = $form.find('select[name="action"]').val();

        if (action === 'generate_meta') {
            e.preventDefault();

            let postIds = [];
            $('tbody th.check-column input[type="checkbox"]:checked').each(function() {
                postIds.push(parseInt($(this).val()));
            });

            if (postIds.length === 0) {
                alert('Proszę zaznacz przynajmniej jeden post.');
                return;
            }

            runMetaGenerationBatch(postIds, pdSeoMetaData, "post");
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

            runMetaGenerationBatch(termIds, pdSeoMetaData, "term");
        }
    });

    function runMetaGenerationBatch(ids, pdSeoMetaData, context) {
        const $popup = $('#pd-seo-meta-generator-popup-overlay');
        $popup.css('display', 'flex');
        const batchSize = pdSeoMetaData.batchSize;
        let currentIndex = 0;
        const total = ids.length;

        function processBatch() {
            const batch = ids.slice(currentIndex, currentIndex + batchSize);
            if (!batch.length) {
                $('#pd-seo-meta-generator-popup-progress').text('Zakończono!');
                $('.pd-seo-meta-popup-close-button').show();
                $('.pd-seo-meta-popup-close-button').on('click', function() {
                    $popup.remove();
                    location.reload();
                });
                return;
            }

            $.post(pdSeoMetaData.ajaxurl, {
                action: context === 'post' ? 'pd_generate_meta_batch' : 'pd_generate_meta_terms_batch',
                nonce: pdSeoMetaData.nonce,
                ids: JSON.stringify(batch)
            }, function (res) {
                currentIndex += batchSize;
                const percent = Math.min(100, Math.round((currentIndex / total) * 100));
                $('#pd-seo-meta-generator-popup-progress').text(`${percent}%`);
                processBatch();
            }).fail(() => {
                $('#pd-seo-meta-generator-popup-progress').text('Wystąpił błąd podczas generowania.');
            });
        }

        processBatch();
    }
}
