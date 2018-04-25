{*
* @author Getresponse <grintegrations@getresponse.com>
* @copyright  GetResponse
* @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if isset($subscribe_via_registration_form)}
    {$subscribe_via_registration_form}
{/if}
{if isset($subscribe_via_registration_list)}
    {$subscribe_via_registration_list}
{/if}

{if isset($campaign_days)}
    <script>
        $(function () {
            var available_cycles = $.parseJSON('{$campaign_days}');

            var cycles1 = cycles.init(
                available_cycles,
                $('#campaign'),
                $('#cycledays'),
                $('#addToCycle'),
                {$cycle_day}
            );
        });
    </script>
{/if}