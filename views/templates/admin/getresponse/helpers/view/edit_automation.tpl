{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<link rel="stylesheet" type="text/css" href="/modules/getresponse/css/edit_automation.css" />

<div id="edit_automation" class="gr-wrapper _hide">
	<h3 class="cnt">{l s='Edit automation' mod='getresponse'}</h3>
	<form class="form-horizontal" target="_top" action="{$action_url|escape:'htmlall':'UTF-8'}&action=automation&update_id={$selected_id|escape:'htmlall':'UTF-8'}" method="post">
		<div class="control-group">
			<label for="filterCategory" class="control-label">{l s='If purchased in the category' mod='getresponse'}</label>
			<div class="controls">
				<select name="category" id="filterCategory" class="gr_select hiddenselect" disabled>
					{if $categories}
						{foreach $categories as $category}
							<option value="{$category['id_category']|escape:'htmlall':'UTF-8'}" {if $selected_category == $category['id_category']}selected{/if}>{$category['name']|escape:'htmlall':'UTF-8'}</option>
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
			</div>
		</div>

		<div class="control-group">
			<label for="popAction" class="control-label">{l s='contact will be' mod='getresponse'}:</label>
			<div class="controls">
				<select name="a_action" id="filterAction" class="hiddenselect">
					<option value="move" {if $selected_action == 'move'}selected{/if}>{l s='Move' mod='getresponse'}</option>
					<option value="copy" {if $selected_action == 'copy'}selected{/if}>{l s='Copy' mod='getresponse'}</option>
				</select>
			</div>
		</div>

		<div class="control-group">
			<label for="destinationCampaign" class="control-label">{l s='into the camapaign' mod='getresponse'}</label>
			<div class="controls">
				{if $campaigns}
					<select name="campaign" id="targetCampaign" class="gr_select campaignSelect">
						{foreach $campaigns as $campaign}
							<option value="{$campaign['id']|escape:'htmlall':'UTF-8'}" {if $selected_campaign == $campaign['id']}selected{/if}>{$campaign['name']|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				{else}
					<h5>{l s='No campaigns' mod='getresponse'}</h5>
				{/if}
			</div>
		</div>

		{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/add_cycle.tpl"}

		<div class="control-group">
			<div class="controls">
				<input type="submit" value="{l s='Save' mod='getresponse'}" name="EditAutomationConfiguration" class="button">
			</div>
		</div>
	</form>
</div>
