{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<fieldset id="triggerAddToCycleContainer"></fieldset>
<script type="text/javascript" src="{$base_url|escape:'htmlall':'UTF-8'}modules/getresponse/views/js/AddToCycle.src-verified.async.js"></script>
{literal}
<script type="text/javascript">
    $(function () {
        APP.require(APP.files.js.templateBuilder, function () {
            APP.publish('triggerAddToCycle.destroy');
            APP.publish('triggerAddToCycle.setConfig', {
                container: '#triggerAddToCycleContainer',
                translations: {
                    translateDontAddToTheCycleOnDay: {/literal}" <span>{l s='Add to autoresponder sequence:' mod='getresponse'}</span>"{literal},
                    translateMore: {/literal}'more'{literal},
                    translateDay: {/literal}'{l s='Day' mod='getresponse'}'{literal},
                    translateMessages: '{{/literal}{l s='messages' mod='getresponse'}{literal}|{/literal}{l s='message' mod='getresponse'}{literal}|{/literal}{l s='messages' mod='getresponse'}{literal}}',
                    translateNoMessages: {/literal}'{l s='no messages' mod='getresponse'}'{literal}
                },
                names: {day: 'cycle_day'},
                storageName: 'manageContactsAutoresponderData',
                url: '{/literal}{$action_url|escape:'UTF-8'}{literal}&ajax&action=getmessages',
                api_key: "{/literal}{$api_key|escape:'htmlall':'UTF-8'}{literal}",
                api_url: "{/literal}{$api_url|escape:'htmlall':'UTF-8'}{literal}"
            });
            APP.publish('triggerAddToCycle.setCampaign', $('#targetCampaign').val());
            APP.publish('triggerAddToCycle.build');
        });
    });
</script>
{/literal}
