$(function() {

    //webform selector
    $('input[name="subscription"]').on('change', function(e) {
        if ($('input[name="subscription"]:checked').val() == 1) {
            $('#style').parent().parent().show();
            $('#position').parent().parent().show();
            $('#form').parent().parent().show();
        } else {
            $('#style').parent().parent().hide();
            $('#position').parent().parent().hide();
            $('#form').parent().parent().hide();
        }
    });

    if ($('input[name="subscription"]:checked').val() == 1) {
        $('#style').parent().parent().show();
        $('#position').parent().parent().show();
        $('#form').parent().parent().show();
    } else {
        $('#style').parent().parent().hide();
        $('#position').parent().parent().hide();
        $('#form').parent().parent().hide();
    }

});