/*
 * This module hooks into the newOrder to add the customers
 * @author Getresponse <grintegrations@getresponse.com>
 * @copyright  GetResponse
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

 /*global window, document, jQuery, imgurlcore, APP, $ */
(function (name, buider) {
    if (APP && APP.core) {
        APP.core.define(name, buider);
        return;
    }

    throw new Error('Nie ma APP.core.define');
}('GetResponse', function (box) {
    var $ = box.dom || jQuery,
        win = window,
        doc = win.document,
        autoresponder,
        templateBuilder = box.templateBuilder.getInstance({
            block: '<div class="triggerAddToCycleContainer">'
                    + '<select name="{{dayName}}" data-define="day" disabled="disabled"></select>'
                + '</div>',
            option: '<span><strong>{{translateDay}} {{number}}:</strong> {{count}} {{translateMessages}}</span>',
            message: '<em title="{{message}}">{{message}}</em>'
        }), actions;

        function parseConfig(string) {
            if (string[0] !== '{') {
                string = '{' + string + '}';
            }
            return eval('(' + string + ')');
        }
        function bindAction(ob) {
            var that = this,
                $ob = $(ob);

            $ob.find('[data-params]').bind({
                click : function (e) {
                    var data = this.getAttribute('data-params') || '{}',
                        hash = this.tagName.toUpperCase() === 'A' ? this.getAttribute('href').split('#') : false,
                        url,
                        action;
                    if (this.tagName.toUpperCase() === 'A') {
                        e.preventDefault();
                    }
                    data = parseConfig(data);
                    url = hash ? hash.shift() : false;
                    action = hash ? hash.shift() : false;
                    data.url = data.url || url;
                    data.action = data.action || action;
                    data.params = data.params || hash;
                    data.event = e;
                    data.activator = this;

                    if (data.action && 'function' === typeof actions[data.action]) {
                        return actions[data.action].call(that, data);
                    } else {
                        console.log(data.action);
                    }
                }
            });
            APP('lightbox.attachToObject', $ob.find('[data-lightbox]').toArray(), 'lightbox');
        }

        actions = {
            add_campaign: function(data) {
                var that = data.activator;

                APP('lightbox.open', {selector: '#add_campaign'});
            },
            switch_viapage: function(data, status) {
                var formBoxEl = $('#form-box');
                if (status) {
                    formBoxEl.slideDown(300);
                } else {
                    formBoxEl.slideUp(300);
                }

                $.post( data.activator.href, { subscription: (status) ? 'yes' : 'no' }, function(){

                });

            },
            switch_webformpage: function(data, status) {
                var formBoxEl = $('#form-box');
                if (status) {
                    formBoxEl.slideDown(300);
                } else {
                    formBoxEl.slideUp(300);
                }

                $.post( data.activator.href, { subscription: (status) ? 'yes' : 'no' }, function(){

                });
            },
            switch_viapage_customs: function(data, status) {
                var customNameFields = $('#customNameFields');
                if (status) {
                    customNameFields.slideDown(300);
                } else {
                    customNameFields.slideUp(300);
                }
            },
            iswitch: function(data) {
                var el = $(data.activator), switchEl = el.find('[data-iswitch]'), checkboxEl = el.find('input[type="checkbox"]'), status;

                status = checkboxEl[0] ? checkboxEl[0].checked : ( switchEl.hasClass('enabled') ? true : false );

                if (!checkboxEl[0]) {
                    checkboxEl = $('<input type="checkbox" name="" />').attr('checked', status).appendTo(el);
                }

                if (status) {
                    el.removeClass('enabled').addClass('disabled');
                    switchEl.removeClass('enabled').addClass('disabled');
                    checkboxEl.attr('checked', false);
                } else {
                    el.removeClass('disabled').addClass('enabled');
                    switchEl.removeClass('disabled').addClass('enabled');
                    checkboxEl.attr('checked', true);
                }
                if (data.run && typeof actions[data.run] === 'function') {
                    actions[data.run](data, !status);
                }

            },
            checkbox: function(data) {
                var el = $(data.activator), checkboxEl = el.find('input[type="checkbox"]'), status = checkboxEl[0].checked;

                if (data.run && typeof actions[data.run] === 'function') {
                    actions[data.run](data, status);
                }

            }
        }

        return {
            init: function() {
                if (typeof $.growler !== 'undefined') {
                    $.MyG = $.growler({
                        position: 'bottom right',
                        offset: '0 10 10 0',
                        width: 350,
                        appendTo: document.body
                    });
                }

                /* ONLOAD GROWLERS */
                    if ($.register && $.MyG) {
                        if ($.register.form_status && $.register.status_text) {
                            if ($.register.form_status === 'success') {
                                $.MyG.show($.register.status_text);
                            } else  if ($.register.form_status === 'error') {
                                $.MyG.show($.register.status_text);
                            } else {
                                $.MyG.show($.register.status_text);
                            }
                        }
                    }
                /* */

                /* Narrow select */
                $('.jsNarrowSelect').fullSelectNarrowDown();

                /* FULLSELLECTS */
                    var fulleselectEls = $('select.fullselect'),
                        hiddenselectEls = $('select.hiddenselect');

                    fulleselectEls.fullSelectLc();
                    hiddenselectEls.fullSelectLc();

                /* campaignSelectEls */
                    $(function(){
                        var campaignSelectEls = $('select.campaignSelect');

                        campaignSelectEls.fullSelectLc({
                            afterClickItem: function (e, select) {
                                APP.publish('triggerAddToCycle.setCampaign', select.elements.selected[0].input.value);
                            }
                        });
                    });
                /* */

                $('#account_type').bind({
                    change: function() {
                        if (this.value === 'gr') {
                            $('#cryptoView').hide();
                        } else {
                            $('#cryptoView').show();
                        }
                    }
                })

                bindAction(doc.body);
            }
        }


}));

$(function () {
    APP.require(APP.files.js.templateBuilder, function () {
        APP.core.start('GetResponse');
    });
});
/*------------------------------------*\
    SHOW MORE CONTENT
\*------------------------------------*/
$(function () {

    $.fn.hasData = function(key) {
        return (typeof $(this).data(key) != 'undefined');
    };

    $("#getresponse .show-more").each(function(){
        $(this).on('click', function(e){
            e.preventDefault();

            var rel = $(this).attr('href');
            hash = '#'+rel.split('#')[1];

            if($(this).hasData('label')) {
                var label = $(this).data('label').split("|");
            }
            if ($(hash).css('display') == 'none') {
                if($(this).hasData('label')) {
                    $(this).html(label[1]);
                }
                $(hash).slideDown(300);
            } else {
                if($(this).hasData('label')) {
                    $(this).html(label[0]);
                }
                $(hash).slideUp(300);
            }
        });
    });
});
/*------------------------------------*\
    TOGGLE MENU
\*------------------------------------*/
$(function () {
    $('#getresponse .item-dropdown-menu').each(function(){
        $(this).find('button').on('click', function(e){
            e.preventDefault();
            e.stopPropagation();

            if($(this).parent().hasClass('open')) {
                $(this).parent().removeClass('open');
            } else {
                $('#getresponse .item-dropdown-menu .menu').removeClass('open');
                $(this).parent().addClass('open');
            }
        });
    });
    $(document).on('click', function(){
        $('#getresponse .item-dropdown-menu .menu').removeClass('open');
    });

    var iframe = $('#automation_form_edit');
    // iframe.hide();
    edit_btns = $('#getresponse .item-dropdown-menu .edit');

    edit_btns.each(function(){
        $(this).on('click', function(e){
            e.preventDefault();
            e.stopPropagation();

            var href_edit = $(this).attr('href');
            var loader = $('<div>').addClass('loader').html('<span>loading data...</span>');

            if($(this).hasClass('active')) {
                $(this).removeClass('active');
                iframe.animate({
                    height: 0,
                }, 300).html('');
            }
            else {
                if(edit_btns.hasClass('active')) {
                    edit_btns.removeClass('active');
                    $(this).addClass('active');
                    iframe.children().animate({
                        'opacity': 0
                    }, 300);

                    loader.addClass('center').prependTo(iframe);

                    iframe.load(href_edit + ' #edit_automation', function(){
                        iframe.children().css({opacity: 0}).animate({
                            'opacity': 1
                        }, 300);
                        iframe.find('select').fullSelectLc({
                            mode: false
                        });
                    });

                } else {
                    iframe.animate({
                        height: 'auto',
                    }, 300);

                    loader.removeClass('center').prependTo(iframe);

                    $(this).addClass('active');
                    iframe.load(href_edit + ' #edit_automation', function(){
                        iframe.children().css({opacity: 0}).animate({
                            'opacity': 1
                        }, 300);

                        iframe.find('select').fullSelectLc({
                            mode: false
                        });
                    });
                }
            }
        });
    });
});

function cancel_automation_edit() {
    $('#getresponse .item-dropdown-menu .edit.active').removeClass('active');
    $('#edit_automation').hide('slow');
}
/*------------------------------------*\
    TOOLTIP
\*------------------------------------*/
$(function() {
    var targets = $('[rel~=tooltip]'),
        target = false,
        tooltip = false,
        title = false;

    $('#getresponse').on('mouseenter', '[rel~=tooltip]', function() {
        target = $(this);
        tip = target.attr('title').split("|");
        tooltip = $('<div id="gr-tooltip"></div>');

        if (!tip || tip == '')
            return false;

        target.removeAttr('title');
        tooltip.css('opacity', 0)
        .html('<h5>'+tip[0]+'</h5><p>'+tip[1]+'</p>')
            .appendTo('body');

        var init_tooltip = function() {
            if (($(window).width() < tooltip.outerWidth() * 1.5) && ($(window).width() <= 480)) {
                tooltip.css('max-width', $(window).width() / 2);
            }
            else {
                tooltip.css('max-width', 200);
            }
            var pos_left = target.offset().left + (target.outerWidth() / 2) - (tooltip.outerWidth() / 2),
                pos_top = target.offset().top - tooltip.outerHeight() - 20;

            if (pos_left < 0) {
                pos_left = target.offset().left + target.outerWidth() / 2 - 20;
                tooltip.addClass('left');
            } else
                tooltip.removeClass('left');

            if (pos_left + tooltip.outerWidth() > $(window).width()) {
                pos_left = target.offset().left - tooltip.outerWidth() + target.outerWidth() / 2 + 20;
                tooltip.addClass('right');
            } else
                tooltip.removeClass('right');

            if (pos_top < 0) {
                var pos_top = target.offset().top + target.outerHeight();
                tooltip.addClass('top');
            } else
                tooltip.removeClass('top');

            tooltip.css({
                    left: pos_left,
                    top: pos_top
                })
                .animate({
                    top: '+=10',
                    opacity: 1
                }, 50);
        };

        init_tooltip();
        $(window).resize(init_tooltip);

        var remove_tooltip = function() {
            tooltip.animate({
                top: '-=10',
                opacity: 0
            }, 50, function() {
                $(this).remove();
            });

            target.attr('title', tip[0]+'|'+tip[1]);
        };

        target.bind('mouseleave', remove_tooltip);
        tooltip.bind('click', remove_tooltip);
    });
});

