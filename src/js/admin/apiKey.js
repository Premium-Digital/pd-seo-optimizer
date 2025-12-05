import $ from 'jquery';

export function initApiKeyUI() {
  $(document).ready(function() {
    const $changeBtn = $('#pd-seo-api-change-button');
    const $inputWrap = $('#pd-seo-api-key-input');

    if ($changeBtn.length && $inputWrap.length) {
      $changeBtn.on('click', function() {
        $inputWrap.toggle();
      });
    }

    $(document).on('change', 'input[name="pd_seo_optimizer_openai_api_key_clear"]', function() {
      if (this.checked) {
        if (!confirm('Are you sure you want to remove the existing OpenAI API key?')) {
          this.checked = false;
        }
      }
    });
  });
}
