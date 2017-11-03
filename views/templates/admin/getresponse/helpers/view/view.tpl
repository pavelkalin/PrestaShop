{*
* @author Getresponse <grintegrations@getresponse.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div id="getresponse" class="gr-wrapper">
	<div id="module">
		<div class="row">
			<div class="col-lg-12">
					{if $selected_tab == 'api'}
						{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}common/api.tpl"}
					{/if}
					{if $selected_tab == 'export_customers'}
						{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}common/exportcustomers.tpl"}
					{/if}
					{if $selected_tab == 'subscribe_via_registration'}
						{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}common/subscribe_via_registration.tpl"}
					{/if}
			</div>
		</div>
	</div>
</div>
