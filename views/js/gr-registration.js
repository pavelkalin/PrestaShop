(function ($) {
    cycles = {
        available_cycles: {},
        cycle_list:null,
        campaign_list:null,
        cycle_checkbox:null,
        cycle_label:null,
        selected_cycle_day:null,
        init:function (available_cycles, campaign_list, cycle_list, cycle_checkbox, selected_cycle_day) {
            this.available_cycles = available_cycles;
            this.cycle_list = cycle_list;
            this.campaign_list = campaign_list;
            this.cycle_checkbox = cycle_checkbox;
            this.set_initial_cycle_day(selected_cycle_day);
            this.campaign_list.on('change', function () {
                campaign_id = campaign_list.val();
                campaign_cycles = this.available_cycles[campaign_id];
                if (!campaign_cycles) {
                    this.turn_off_cycles();
                    return;
                }
                this.turn_on_cycles(campaign_cycles);
            }.bind(this));
            this.campaign_list.trigger('change');
        },
        set_initial_cycle_day: function (day) {
            if (day !== parseInt(day) || day === 0) {
                return;
            }
            this.selected_cycle_day = day;
            this.cycle_checkbox.attr('checked', 'checked');
        },
        turn_on_cycles: function (campaign_cycles) {
            this.cycle_list.html('');
            $.each(campaign_cycles, function (index, cycle) {
                selected = this.selected_cycle_day == cycle.day ? 'selected' : '';
                this.cycle_list.append(
                    '<option value="' + cycle.day + '" ' + selected + ' >' + cycle.full_name + '</option>'
                );
            }.bind(this));
            this.cycle_list.removeClass('inactive');
            this.cycle_list.removeAttr('disabled');
            this.cycle_checkbox.removeAttr('disabled');
        },
        turn_off_cycles: function () {
            this.cycle_list.html('<option value="">no autoresponders</option>');
            this.cycle_list.addClass('inactive');
            this.cycle_list.attr('disabled', 'disabled');
            this.cycle_checkbox.attr('disabled', 'disabled');
        }
    };
})(jQuery);

$(function () {
    //subscribe via registration form
    $('input[name="subscriptionSwitch"]').on('change', function () {
        if ($('input[name="subscriptionSwitch"]:checked').val() == 1) {
            $('#newsletter_on').parent().parent().parent().show();
            $('#campaign').parent().parent().show();
            $('#contactInfo_on').parent().parent().parent().show();
            $('#cycledays').parent().parent().show();
        } else {
            $('#newsletter_on').parent().parent().parent().hide();
            $('#campaign').parent().parent().hide();
            $('#contactInfo_on').parent().parent().parent().hide();
            $('#cycledays').parent().parent().hide();
            $('#contactInfo_off').trigger('click');
        }
    }).trigger('change');

    if ($('input[name="subscriptionSwitch"]:checked').val() == 1) {
        $('#newsletter_on').parent().parent().parent().show();
        $('#campaign').parent().parent().show();
    } else {
        $('#newsletter_on').parent().parent().parent().hide();
        $('#campaign').parent().parent().hide();
    }

    $('input[name="contactInfo"]').on('change', function () {
        if ($('input[name="contactInfo"]:checked').val() == 1) {
            $('#form-GRSubscribeRegistration').show();
        } else {
            $('#form-GRSubscribeRegistration').hide();
        }
    });

    if ($('input[name="contactInfo"]:checked').val() == 1) {
        $('#form-GRSubscribeRegistration').show();
    } else {
        $('#form-GRSubscribeRegistration').hide();
    }

    $('table.GRSubscribeRegistration tr').each(function () {
        if (['firstname', 'lastname', 'email'].indexOf($(this).find('td').first().text().trim()) >= 0) {
            var td = $(this).find('td');
            td.attr('onclick', '').removeClass('pointer');
            td.last().html('<span class="btn btn-default disabled">Default</span>');
        }
    });
});