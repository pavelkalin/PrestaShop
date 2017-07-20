{*
* @author Getresponse <grintegrations@getresponse.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{if isset($webform_url)}
<div id="getresponse_webform" class="block" data-position="{$position}">
    {if $webform_url}
    <script type="text/javascript" src="{$webform_url|escape:'htmlall':'UTF-8'}{$style|escape:'htmlall':'UTF-8'}"></script>
    {/if}
</div>
{/if}