{*
* @author Getresponse <grintegrations@getresponse.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<link href='http://fonts.googleapis.com/css?family=Ubuntu+Condensed|Open+Sans:400,300,600' rel='stylesheet' type='text/css'>

<script type="text/javascript">
	APP.files.js.templateBuilder = '{$base_url|escape:'htmlall':'UTF-8'}modules/getresponse/views/js/templateBuilder.src-verified.async.js';
	APP.files.js.lightbox = '{$base_url|escape:'htmlall':'UTF-8'}modules/getresponse/views/js/lightbox.src-verified.async.js';
</script>

{if !empty($flash_message)}
	<div class="bootstrap">
		<div class="module_confirmation conf confirm alert alert-{$flash_message['status']|escape:'htmlall':'UTF-8'}">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
            {$flash_message['message']|escape:'htmlall':'UTF-8'}
		</div>
	</div>
{/if}

<div id="getresponse" class="gr-wrapper">
	<div id="module">
		<div class="row">
			<div class="col-md-3">
				{if isset($selected_tab) && $selected_tab == 'automation' && isset($edit_automation) && ($edit_automation == 'new' || $edit_automation > '0')}
				{else}
					{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/menu.tpl"}
				{/if}
			</div>
			<div class="col-md-9">
				{if $selected_tab}
					{if $selected_tab == 'api'}
						{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/api.tpl"}
					{/if}
					{if $selected_tab == 'export_customers'}
						{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/exportcustomers.tpl"}
					{/if}
					{if $selected_tab == 'subscribe_via_registration'}
						{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/subscribe_via_registration.tpl"}
					{/if}
					{if $selected_tab == 'subscribe_via_form'}
						{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/subscribe_via_form.tpl"}
					{/if}
					{if $selected_tab == 'automation'}
						{if $edit_automation}
							{if $edit_automation == 'new'}
								{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/new_automation.tpl"}
							{else}
								{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/edit_automation.tpl"}
							{/if}
						{else}
							{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/automation.tpl"}
						{/if}
					{/if}
				{else}
					{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/api.tpl"}
				{/if}
			</div>
		</div>
	</div>
</div>
