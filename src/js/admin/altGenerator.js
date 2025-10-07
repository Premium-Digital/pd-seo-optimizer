import $ from 'jquery';

import { showButton } from './showButton';

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
                showButton();
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
                showButton();
            });
        }

        processBatch();
    }
}

export function initSingleAttachmentAlt() {
    jQuery("body").on("click", ".thumbnail",  function() {
        if($('.pd-generate-alt-button').length) {
            return;
        }
        setTimeout(() => {
            let html = `
                <span class="setting" data-setting="pd-generate-alt">
                    <label class="name">Generuj alt (AI)</label>
                    <button type="button" class="button pd-generate-alt-button">Generuj alt (AI)</button>
                </span>
            `;
            const $container = $('.attachment-info > .settings');
            $container.prepend(html);
            let attachmentId = $(this).closest("li").data('id');
            $container.find('.pd-generate-alt-button').on('click',  function() {
                const $button = $(this);

                $button.text('Generowanie...');

                jQuery.post(pdSeoMetaData.ajaxurl, {
                    action: 'pd_generate_image_alts_batch',
                    nonce: pdSeoMetaData.nonce,
                    ids: JSON.stringify([attachmentId])
                }).done((res) => {
                    if (res.success) {
                        $('#attachment-details-two-column-alt-text').text(res.data.alt || '');
                        alert('ALT wygenerowany: ' + (res.data.processed_images || 0) + ' obrazek/ów');
                    } else {
                        alert('Błąd: ' + res.data.message);
                    }
                    $button.text('Generuj alt (AI)');
                }).fail(() => {
                    alert('Błąd podczas generowania ALT');
                    $button.text('Generuj alt (AI)');
                });
            });
        }, 500);
    });
}

