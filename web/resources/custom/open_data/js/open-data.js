
var loadingSelectOption = '<option value="">Loading...</option>';

function formatMda (item) {
    /*if (!item.id) {
        return item.text;
    }*/

    var $mda = item.text;

    if($(item.element).data('total_submissions_processed') > 0){
        var $mda = $(
            '<span class="mda-has-data">' + item.text + '</span>'
        );
    }else{
        var $mda = $(
            '<span class="mda-has-no-data">' + item.text + '</span>'
        );
    }
    return $mda;
}

$(document).ready(function() {

    $('.select-mda').select2({
        allowClear: true,
        templateResult: formatMda
    });

    $('.select-2').select2({
        allowClear: true
    });
});

function showLoading() {
    $('#loading-overlay').show();
}

function hideLoading() {
    $('#loading-overlay').hide();
}