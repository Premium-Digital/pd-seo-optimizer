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

export function initMediaAltGeneration() {
    $('#doaction, #doaction2').on('click', function(e) {
        const $form = $(this).closest('form');
        const action = $form.find('select[name="action"]').val();

        if (action === 'generate_media_alts') {
            e.preventDefault();

            let attachmentIds = [];
            $('tbody th.check-column input[type="checkbox"]:checked').each(function() {
                attachmentIds.push(parseInt($(this).val()));
            });

            if (attachmentIds.length === 0) {
                alert('Proszę zaznacz przynajmniej jeden obrazek.');
                return;
            }

            runMediaAltGenerationBatch(attachmentIds, pdSeoMetaData);
        }
    });

    function runMediaAltGenerationBatch(ids, pdSeoMetaData) {
        const $popup = $('#pd-seo-meta-generator-popup-overlay');
        $(".pd-seo-meta-popup-heading").text("Generowanie alt tekstu dla obrazków");
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
                action: 'pd_generate_media_alts_batch',
                nonce: pdSeoMetaData.nonce,
                ids: JSON.stringify(batch)
            }, function (res) {
                currentIndex += batchSize;
                totalImages += res.data.processed_images || 0;
                const percent = Math.min(100, Math.round((currentIndex / total) * 100));
                $('#pd-seo-meta-generator-popup-progress').text(`${percent}%`);
                processBatch();
            }).fail(() => {
                $('#pd-seo-meta-generator-popup-progress').text('Wystąpił błąd podczas generowania alt tekstu.');
                showButton();
            });
        }

        processBatch();
    }
}

export function initSingleAttachmentAlt() {
    // Use MutationObserver to detect when attachment details panel is shown
    const observer = new MutationObserver(() => {
        const settingsContainer = document.querySelector('.attachment-info .settings');
        const altTextField = document.querySelector('#attachment-details-two-column-alt-text');
        
        // Check if container exists and button hasn't been added yet
        if (settingsContainer && altTextField && !settingsContainer.querySelector('.pd-generate-alt-button')) {
            addGenerateButtonToTop(settingsContainer, altTextField);
        }
    });

    // Start observing the document for changes
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Also add event delegation for clicks
    jQuery(document).on('click', '.pd-generate-alt-button', handleGenerateClick);
}

function addGenerateButtonToTop(settingsContainer, altTextField) {
    // Get attachment ID from the media frame view
    const attachmentId = jQuery('.attachment').data('id') || 
                        jQuery('li.attachment.selected').data('id');
    
    if (!attachmentId) return;

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'button pd-generate-alt-button';
    button.textContent = 'Generuj alt (AI)';
    button.setAttribute('data-attachment-id', attachmentId);
    // Insert at the very top of settings container
    settingsContainer.insertBefore(button, settingsContainer.firstChild);
}

function handleGenerateClick(e) {
    e.preventDefault();
    const $button = jQuery(this);
    const attachmentId = $button.data('attachment-id');

    if (!attachmentId) {
        alert('Błąd: Nie można określić ID obrazka');
        return;
    }

    $button.text('Generowanie...').prop('disabled', true);

    jQuery.post(pdSeoMetaData.ajaxurl, {
        action: 'pd_generate_single_attachment_alt',
        nonce: pdSeoMetaData.nonce,
        attachment_id: attachmentId
    }).done((res) => {
        if (res.success) {
            if (res.data.alt_text) {
                const altField = jQuery('#attachment-details-two-column-alt-text');
                if (altField && altField.length > 0) {
                    altField.val(res.data.alt_text);
                    altField.trigger('change');
                }
            } else {
                alert('Błąd: Alt tekst nie został wygenerowany.');
            }
        } else {
            alert('Błąd: ' + res.data.message);
        }
        $button.text('Generuj alt (AI)').prop('disabled', false);
    }).fail(() => {
        alert('Błąd podczas generowania ALT');
        $button.text('Generuj alt (AI)').prop('disabled', false);
    });
}

