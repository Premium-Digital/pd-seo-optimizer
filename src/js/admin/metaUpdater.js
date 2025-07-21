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

            runMetaGenerationBatch(postIds, pdSeoMetaData);
        }
    });

    // Funkcja obsługująca batch ajax + popup
    function runMetaGenerationBatch(postIds, pdSeoMetaData) {
        const $wrap = $('.wrap');
        const $popup = $(`
            <div class="overlay" style="
                position: fixed;
                width: 100%;
                height: 100vh;
                z-index: 9999999;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(0,0,0,0.3);
                margin: auto;
                display: flex;
            ">
                <div style="padding: 20px;background: #fff;border: 1px solid #ccc;width: 50%;margin: auto;margin-top: auto;margin-bottom: auto;">
                    <h2>Generowanie Meta za pomocą OpenAI...</h2>
                    <p id="pd-seo-progress">0%</p>
                     <button style="display:none" class="close-button">Zamknij</button>
                </div>
            </div>

        `);

        $wrap.prepend($popup);

        const batchSize = 2;
        let currentIndex = 0;
        const total = postIds.length;

        function processBatch() {
            const batch = postIds.slice(currentIndex, currentIndex + batchSize);
            if (!batch.length) {
                $('#pd-seo-progress').text('Zakończono!');
                $('.close-button').show();
                $('.close-button').on('click', function() {
                    $popup.remove();
                    location.reload();
                });
                return;
            }

            $.post(pdSeoMetaData.ajaxurl, {
                action: 'pd_generate_meta_batch',
                nonce: pdSeoMetaData.nonce,
                post_ids: JSON.stringify(batch)
            }, function (res) {
                currentIndex += batchSize;
                const percent = Math.min(100, Math.round((currentIndex / total) * 100));
                $('#pd-seo-progress').text(`${percent}%`);
                processBatch();
            }).fail(() => {
                $('#pd-seo-progress').text('Wystąpił błąd podczas generowania.');
            });
        }

        processBatch();
    }
}
