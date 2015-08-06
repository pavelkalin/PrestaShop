{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<h3>{l s='Export your Prestashop customers on demand' mod='getresponse'}</h3>
<p>
	{l s='This option enables one-time export of contacts from your Prestashop account to GetResponse. Choose a target campaign to which your contacts will be exported. If the campaign includes autoresponders select the sequence day to add your contacts to. You can also create a new campaign for your Prestashop contacts by clicking "Add new campaign". If you want to include visitors who subscribed via Prestashop web form module, check "Web form subscription" option.' mod='getresponse'}
</p>
<p>
	{l s='Please note: Each costumers export will include all of your entries from Prestashop database.' mod='getresponse'}
</p>
<form class="form-horizontal" action="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=exportcustomers" method="post">
	<div class="control-group">
		<label for="targetCampaign" class="control-label">{l s='Select target campaign' mod='getresponse'}:</label>
		<div class="controls">
			{if $campaigns}
				<select name="campaign" id="targetCampaign" class="gr_select campaignSelect">
					{foreach $campaigns as $campaign}
						<option value="{$campaign['id']|escape:'htmlall':'UTF-8'}" {if isset($c) && $c == $campaign['name']}selected="selected" {/if}>{$campaign['name']|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
				<a href="#add_campaign" data-lightbox="{ selector: '#add_campaign', width: 540 }" class="popup-link">{l s='Add new campaign' mod='getresponse'}</a>
			{else}
				<h5>{l s='No campaigns' mod='getresponse'}</h5>
			{/if}
		</div>
	</div>

	<div class="control-group">
		<label for="crypto" class="control-label"></label>
		<div class="controls">
			<label class="checkbox">
				<input name="newsletter_guests" type="checkbox" checked>
				{l s='Web Form subscription' mod='getresponse'}
			</label>
		</div>
	</div>
	<div class="control-group" id="updateDiv">
		<label for="crypto" class="control-label"></label>
		<div class="controls">
			<label class="checkbox"  data-params="{ action: 'checkbox', run: 'switch_viapage_customs' }">
				<input value="yes" type="checkbox" name="update_address" id="chckUpdateContactDataOnPage" {if isset($update_address) && $update_address == 'yes'}checked{/if}>
				{l s='Update details' mod='getresponse'}
			</label>
			<a class="gr-tooltip">
				<span class="gr-tip">
					<h5>{l s='Checkout update' mod='getresponse'}</h5>
					<p>
						{l s='Check if you want to update billing data. Custom name must be composed using up to 32: lowercase a-z letters, digits or underscores' mod='getresponse'}
					</p>
				</span>
			</a>
		</div>
	</div>
	<div id="customNameFields" {if isset($update_address) && $update_address == 'yes'} style="display:block"{else} style="display:none"{/if}>
		<div class="gr-custom-field">
			<select class="jsNarrowSelect" name="custom_field" multiple="multiple">
				{foreach $custom_fields as $custom_field}
					{if $custom_field['custom_name'] != ''}
						<option data-inputvalue="{$custom_field['custom_name']|escape:'htmlall':'UTF-8'}" {if $custom_field['default'] == 'yes' or $custom_field['active_custom'] == 'yes'}selected="selected" {/if}{if $custom_field['default'] == 'yes'}disabled="disabled" {/if}value="{$custom_field['custom_value']|escape:'htmlall':'UTF-8'}">{$custom_field['custom_field']|escape:'htmlall':'UTF-8'}</option>
					{/if}
				{/foreach}
			</select>
		</div>
	</div>

	{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/add_cycle.tpl"}

	<div class="control-group">
		<div class="controls">
			<input type="submit" value="{l s='Export' mod='getresponse'}" name="ExportConfiguration" class="button">
		</div>
	</div>
</form>
{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/new_campaign.tpl"}