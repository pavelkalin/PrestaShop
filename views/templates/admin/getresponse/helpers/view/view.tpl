{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<link href='http://fonts.googleapis.com/css?family=Ubuntu+Condensed|Open+Sans:400,300,600' rel='stylesheet' type='text/css'>

<script type="text/javascript">
	APP.files.js.templateBuilder = '{$base_url|escape:'htmlall':'UTF-8'}modules/getresponse/views/js/templateBuilder.src-verified.async.js';
	APP.files.js.lightbox = '{$base_url|escape:'htmlall':'UTF-8'}modules/getresponse/views/js/lightbox.src-verified.async.js';
	$.register = {
		form_status: "{$form_status|escape:'htmlall':'UTF-8'}",
		status_text: "{$status_text|escape:'htmlall':'UTF-8'}"
	}
</script>

<div id="getresponse" class="gr-wrapper">
	{if isset($message)}
		<div class="bootstrap">
			<div class="module_confirmation conf confirm alert alert-success">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				{$message|escape:'htmlall':'UTF-8'}
			</div>
		</div>
	{/if}
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
					{if $selected_tab == 'exportcustomers'}
						{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/exportcustomers.tpl"}
					{/if}
					{if $selected_tab == 'viapage'}
						{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/viapage.tpl"}
					{/if}
					{if $selected_tab == 'viawebform'}
						{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/viawebform.tpl"}
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
