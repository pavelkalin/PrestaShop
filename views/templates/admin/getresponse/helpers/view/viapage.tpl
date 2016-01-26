{*
* @author	 Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="content">
	<h3>{l s='Subscribe via registration page' mod='getresponse'}</h3>
	<p>
		{l s='You can add subscribers to a selected GetResponse campaign when they register to your online shop (via the registration page). Select an existing campaign or create a new campaign for your PrestaShop visitors. If the campaign includes an autoresponder, choose the sequence day you want to add the contacts to. In case customers change their contact data in the last stage of order placement, select the ' mod='getresponse'} <strong>Checkout Update</strong> {l s=' box and we’ll update their details automatically.' mod='getresponse'}
	</p>
	<form class="form-horizontal" action="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=viapage" method="post">
		<div id="switch-box">
			<div class="control-group">
				<label for="targetCampaign" class="control-label">{l s='Subscription' mod='getresponse'}</label>
				<div class="controls">

					<a href="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=viapage#iswitch" id="chckSubscriptionInput" class="switch {if $status == 'yes'}enabled{else}disabled{/if}" data-params="{ run: 'switch_viapage' }">

						<span data-iswitch="" class="s-css3 {if $status == 'yes'}enabled{else}disabled{/if}">
							<span class="s-css3-c">
								<span class="s-css3-c-1">
									<span class="s-css3-in">{l s='ON' mod='getresponse'}</span>
								</span>
								<span class="s-css3-c-2">
									<span class="s-css3-in">{l s='OFF' mod='getresponse'}</span>
								</span>
								<span class="s-css3-c-gala">
									<span class="s-css3-in"></span>
								</span>
							</span>
						</span>
						<input type="checkbox" value="yes" id="chckSubscriptionInput" name="status" {if $status == 'yes'}checked{/if}/>
					</a>
					<abbr title='{l s='Subscription' mod='getresponse'}|{l s='If you set this to ON, subscribers are immediately added to your GetResponse account. You can stop this anytime by switching the slider to OFF.' mod='getresponse'}' rel="tooltip"></abbr>
				</div>
			</div>
		</div>

		<div id="form-box" {if $status == 'yes'} style="display:block"{else} style="display:none"{/if}>
			<div class="highslide-body">

				<fieldset class="control-group" id="campaignDiv">
					<label for="targetCampaign" class="control-label">{l s='Select your campaign' mod='getresponse'}</label>
					<div class="controls">
						{if $campaigns}
							<div class="input-more">
								<select name="campaign" id="targetCampaign" class="gr_select campaignSelect">
									{foreach $campaigns as $campaign}
										<option value="{$campaign['id']|escape:'htmlall':'UTF-8'}" {if isset($c) && $c == $campaign['name'] || $selected_campaign == $campaign['id']}selected{/if}>{$campaign['name']|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
								<a href="#add_campaign" data-label="Create new campaign|Cancel" class="button default show-more" style="width: 140px;">{l s='Create new campaign' mod='getresponse'}</a>
							</div>
							{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/new_campaign.tpl"}
						{else}
							<div class="warning">
								<h5>
									{l s='No campaigns' mod='getresponse'}
								</h5>
							</div>
						{/if}
					</div>
				</fieldset>

				{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/add_cycle.tpl"}

				<fieldset class="control-group" id="updateDiv">
					<label for="crypto" class="control-label"></label>
					<div class="controls">
						<label class="checkbox"  data-params="{ action: 'checkbox', run: 'switch_viapage_customs' }">
							<input value="yes" type="checkbox" name="update_address" id="chckUpdateContactDataOnPage" {if $update_address == 'yes'}checked{/if}>
							<span class="tooltip-label">{l s='Checkout update' mod='getresponse'}</span>
							<abbr title='{l s='Checkout update' mod='getresponse'}|{l s='Select if you want to update billing data. A custom name must be composed using up to 32: lowercase a-z letters, digits, or underscores.' mod='getresponse'}' rel="tooltip"></abbr>
						</label>
					</div>
				</fieldset>
				<fieldset id="customNameFields" {if $update_address == 'yes'} style="display:block"{else} style="display:none"{/if}>
					<label>
						<span class="tooltip-label">Copy PrestaShop customer details to custom fields</span>
					</label>
					<div class="gr-custom-field">
						<select class="jsNarrowSelect" name="custom_field" multiple="multiple">
							{foreach $custom_fields as $custom_field}
								{if $custom_field['custom_name'] != ''}
									<option data-inputvalue="{$custom_field['custom_name']|escape:'htmlall':'UTF-8'}" {if $custom_field['default'] == 'yes' or $custom_field['active_custom'] == 'yes'}selected="selected" {/if}{if $custom_field['default'] == 'yes'}disabled="disabled" {/if}value="{$custom_field['custom_value']|escape:'htmlall':'UTF-8'}">{$custom_field['custom_field']|escape:'htmlall':'UTF-8'}</option>
								{/if}
							{/foreach}
						</select>
					</div>
				</fieldset>

				<fieldset class="control-group" id="saveDiv">
					<div class="controls">
						<input type="submit" value="{l s='Save' mod='getresponse'}" name="ViapageConfiguration" class="button">
					</div>
				</fieldset>
			</div>
		</div>
	</form>
	{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/new_campaign.tpl"}
</div>