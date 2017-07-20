$(function() {

    //save tracking button
    $('button#saveTracking').on('click', function () {
        var button = $('button#saveTracking').attr('data-url');
        var status = $('#checkTrackingInput').is(':checked');

        $.post(button, {tracking: (status) ? 'yes' : 'no'}, function (result) {
            var message = '';
            if (undefined !== result.error) {
                message = '<div class="bootstrap">' +
                    '<div class="module_confirmation conf confirm alert alert-danger">' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    result.error +
                    '</div>' +
                    '</div>';
            } else {
                message = '<div class="bootstrap">' +
                    '<div class="module_confirmation conf confirm alert alert-success">' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    result.success +
                    '</div>' +
                    '</div>';
            }

            $('#getresponse').parent().prepend(message);
        }, "json");
    })
});