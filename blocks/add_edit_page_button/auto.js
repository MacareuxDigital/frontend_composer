$('input[name="type"]').on('change', function () {
    if ($(this).val() === 'edit') {
        $('#ccm_pageTypeSelector').hide();
    } else {
        $('#ccm_pageTypeSelector').show();
    }
});