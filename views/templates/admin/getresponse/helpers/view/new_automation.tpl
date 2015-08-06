{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<link rel="stylesheet" type="text/css" href="/modules/getresponse/css/new_automation.css" />

<div id="add_automation" class="gr-wrapper">
	<h3 class="cnt">{l s='Create new automation' mod='getresponse'}</h3>
	<form class="form-horizontal" target="_top" action="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=automation" method="post" OnSubmit="return CheckForm()">

		{if $d_categories}
			<div class="control-group">
				<label for="filterCategory" class="control-label">{l s='If purchased in the category' mod='getresponse'}</label>
				<div class="controls">
					<select name="category" id="filterCategory" class="gr_select hiddenselect">
						{if $d_categories}
							{foreach $d_categories as $category}
								<option value="{$category['id_category']|escape:'htmlall':'UTF-8'}">{$category['name']|escape:'htmlall':'UTF-8'}</option>
							{/foreach}
						{/if}
					</select>
					<a class="gr-tooltip">
						<span class="gr-tip" style="width:150px">
							<h5>{l s='Filter category' mod='getresponse'}</h5>
							<p>
								{l s='Choose one of your Prestashop product categories to copy or move the contact after a successful purchase in the selected category.' mod='getresponse'}
							</p>
						</span>
					</a>
					<div id="ErrorCategory"></div>
				</div>
			</div>

			<div class="control-group">
				<label for="popAction" class="control-label">{l s='contact will be' mod='getresponse'}:</label>
				<div class="controls">
					<select name="a_action" id="filterAction" class="gr_select hiddenselect">
						<option value="move">{l s='Move' mod='getresponse'}</option>
						<option value="copy">{l s='Copy' mod='getresponse'}</option>
					</select>
					<div id="ErrorAction"></div>
				</div>
			</div>

			<div class="control-group sel-h126">
				<label for="destinationCampaign" class="control-label">{l s='into the camapaign' mod='getresponse'}</label>
				<div class="controls">
					{if $campaigns}
						<select name="campaign" id="targetCampaign" class="gr_select campaignSelect">
							{if $campaigns}
								{foreach $campaigns as $campaign}
									<option value="{$campaign['id']|escape:'htmlall':'UTF-8'}">{$campaign['name']|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							{/if}
						</select>
					{else}
						<h5>{l s='No campaigns' mod='getresponse'}</h5>
					{/if}
					<div id="ErrorCampaign"></div>
				</div>
			</div>

			{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/add_cycle.tpl"}

			<div class="control-group">
				<div class="controls">
					<input type="submit" value="{l s='Save' mod='getresponse'}" name="NewAutomationConfiguration" class="button">
				</div>
			</div>
		{else}
			<div style="text-align:center; font-size: 15px;">
				<p style="color: #ff0000;">{l s='Can not add new automation' mod='getresponse'}.</p>
				<p style="color: #ff0000;">{l s='Maximum number of options has been used' mod='getresponse'}.</p>
			</div>
		{/if}
		<div class="clearer" ></div>
	</form>
</div>