function ecommerceDisplay() {
    if ($('#ecommerce_on').is(':checked')) {
        $('#shop').parent().parent().show();
        $('#form-GREcommerce').show();
    } else {
        $('#shop').parent().parent().hide();
        $('#form-GREcommerce').hide();
    }
}

$(document).ready(function() {
    $('.prestashop-switch').click(function () {
        ecommerceDisplay();
    }).trigger('click');


    $('button[type="reset"]').click(function(e) {
        e.preventDefault();
        window.history.back();
    });
});