/*
 * This module hooks into the newOrder to add the customers
 * @author   Grzegorz Struczynski <gstruczynski@implix.com>
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
                    formBoxEl.show();
                } else {
                    formBoxEl.hide();
                }

                $.post( data.activator.href, { subscription: (status) ? 'yes' : 'no' }, function(){

                });

            },
            switch_webformpage: function(data, status) {
                var formBoxEl = $('#form-box');
                if (status) {
                    formBoxEl.show();
                } else {
                    formBoxEl.hide();
                }

                $.post( data.activator.href, { subscription: (status) ? 'yes' : 'no' }, function(){

                });
            },
            switch_viapage_customs: function(data, status) {
                var customNameFields = $('#customNameFields');
                if (status) {
                    customNameFields.show();
                } else {
                    customNameFields.hide();
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

