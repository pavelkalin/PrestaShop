$(function() {
    //enterprise package selector
    if ($('input[name="is_enterprise"]:checked').val() == 1) {
        $('input[name="account_type"]').parent().parent().parent().parent().show();
        $('#domain').parent().parent().show();
    } else {
        $('input[name="account_type"]').parent().parent().parent().parent().hide();
        $('#domain').parent().parent().hide();
    }

    $('input[name="is_enterprise"]').on('change', function(e) {
        if ($('input[name="is_enterprise"]:checked').val() == 1) {
            $('input[name="account_type"]').parent().parent().parent().parent().show();
            $('#domain').parent().parent().show();
        } else {
            $('input[name="account_type"]').parent().parent().parent().parent().hide();
            $('#domain').parent().parent().hide();
        }
    });
});