import $ from 'jquery';

export function initAltGeneration() {

    $('#doaction, #doaction2').on('click', function(e) {
        const $form = $(this).closest('form');
        const action = $form.find('select[name="action"]').val();

        if (action === 'generate_image_alts') {
            e.preventDefault();

            let postIds = [];
            $('tbody th.check-column input[type="checkbox"]:checked').each(function() {
                postIds.push(parseInt($(this).val()));
            });

            if (postIds.length === 0) {
                alert('Proszę zaznacz przynajmniej jeden post.');
                return;
            }

            runAltGenerationBatch(postIds, pdSeoMetaData);
        }
    });

    function runAltGenerationBatch(ids, pdSeoMetaData) {
        const $popup = $('#pd-seo-meta-generator-popup-overlay');
        $(".pd-seo-meta-popup-heading").text("Generowanie altów obrazków");
        $popup.css('display', 'flex');
        const batchSize = parseInt(pdSeoMetaData.batchSize, 10);
        let currentIndex = 0;
        const total = ids.length;
        let totalImages = 0;

        function processBatch() {
            const batch = ids.slice(currentIndex, currentIndex + batchSize);
            if (!batch.length) {
                $('#pd-seo-meta-generator-popup-progress').text(`Zakończono! Wygenerowano alt dla ${totalImages} obrazków.`);
                $('.pd-seo-meta-popup-close-button').show();
                $('.pd-seo-meta-popup-close-button').on('click', function() {
                    $popup.remove();
                    location.reload();
                });
                return;
            }

            $.post(pdSeoMetaData.ajaxurl, {
                action: 'pd_generate_image_alts_batch',
                nonce: pdSeoMetaData.nonce,
                ids: JSON.stringify(batch)
            }, function (res) {
                currentIndex += batchSize;
                totalImages += res.data.processed_images || 0;
                const percent = Math.min(100, Math.round((currentIndex / total) * 100));
                $('#pd-seo-meta-generator-popup-progress').text(`${percent}%`);
                processBatch();
            }).fail(() => {
                $('#pd-seo-meta-generator-popup-progress').text('Wystąpił błąd podczas generowania altów.');
            });
        }

        processBatch();
    }
}
