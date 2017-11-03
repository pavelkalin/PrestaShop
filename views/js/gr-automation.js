$(document).ready(function () {

    if ($('table.GRContactList').length > 0) {
        var text = 'Define rules that move or copy contacts between GetResponse lists when people make purchases in the selected product category.';
        $('table.GRContactList').parent().prepend('<div class="alert alert-info">' + text + '</div>');
    }

    if ($('#a_action').length > 0) {
        var autoresponders = $.parseJSON($('#autoresponders').val()),
            autorespondersList = $('#autoresponder_day'),
            autorespondersCheck = $('#options_1'),
            defaultNoAutoresponders = autorespondersList.find('option').text(),
            selectedCycleDay = $('#cycle_day_selected').val();

        $('#campaign').change(function () {
            var campaignId = $(this).val();
            autorespondersList.find('option').remove();
            autorespondersList.attr('disabled', 'disabled');
            autorespondersCheck.attr('checked', false);

            for (var i = 0; i < Object.keys(autoresponders).length; i++) {
                if (autoresponders[i].campaignId == campaignId) {
                    var text = (autoresponders[i].triggerSettings.dayOfCycle + ': ' + autoresponders[i].name + ' (' + autoresponders[i].subject + ')');
                    autorespondersList.append('<option value="' + autoresponders[i].triggerSettings.dayOfCycle + '">' + text + '</option>');
                }
            }

            if (autorespondersList.find('option').length > 0) {
                autorespondersCheck.attr('disabled', false);
                autorespondersCheck.parent().removeClass('text-muted');
            } else {
                autorespondersCheck.attr('disabled', 'disabled');
                autorespondersCheck.parent().addClass('text-muted');
                autorespondersList.append('<option value="">' + defaultNoAutoresponders + '</option>');
            }

            var autoresponder_start = $('#autoresponder_day_selected');

            if (autoresponder_start.val() != '') {
                setTimeout(function() {
                    $('#autoresponder_day').val(autoresponder_start.val());
                    autoresponder_start.val('');
                }, 500);

            }
        }).trigger('change');

        if (selectedCycleDay != '') {
            autorespondersCheck.attr('disabled', false).attr('checked', true);
            autorespondersList.attr('disabled', false).val(selectedCycleDay);
        }

        autorespondersCheck.change(function () {
            autorespondersList.attr('disabled', !autorespondersCheck.is(':checked'));
        });
    }

    $('button[type="reset"]').click(function (e) {
        e.preventDefault();
        window.history.back();
    });
});