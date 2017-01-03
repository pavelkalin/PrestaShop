{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div id="edit_automation" class="gr-wrapper _hide">
	<div class="highslide-body">
		<form class="form-horizontal" target="_top" action="{$action_url|escape:'htmlall':'UTF-8'}&action=automation&update_id={$selected_id|escape:'htmlall':'UTF-8'}" method="post">
			<fieldset class="control-group">
				<label for="filterCategory" class="control-label">{l s='If customers buy  in the category' mod='getresponse'}</label>
				<div class="controls input-tip">
					<select name="category" id="filterCategory" class="gr_select hiddenselect">
						{if $categories}
							{foreach $categories as $category}
								{if $category['level_depth'] != 0}
									<option value="{$category['id_category']|escape:'htmlall':'UTF-8'}" {if $selected_category == $category['id_category']}selected{/if}>{$category['name']|escape:'htmlall':'UTF-8'}</option>
								{/if}
							{/foreach}
						{/if}
					</select>
					<span>
						<abbr title='{l s='Category' mod='getresponse'}|{l s='Select a PrestaShop category that will be used to categorize customers. When someone makes a purchase in this category they can be automatically moved or copied to a specific campaign.' mod='getresponse'}' rel="tooltip"></abbr>
					</span>
				</div>
			</fieldset>

			<fieldset class="control-group">
				<label for="popAction" class="control-label">{l s='they are' mod='getresponse'}:</label>
				<div class="controls">
					<select name="a_action" id="filterAction" class="hiddenselect">
						<option value="move" {if $selected_action == 'move'}selected{/if}>{l s='Moved' mod='getresponse'}</option>
						<option value="copy" {if $selected_action == 'copy'}selected{/if}>{l s='Copied' mod='getresponse'}</option>
					</select>
				</div>
			</fieldset>

			<fieldset class="control-group">
				<label for="destinationCampaign" class="control-label">{l s='into the camapaign' mod='getresponse'}</label>
				<div class="controls">
					{if $campaigns}
						<select name="campaign" id="targetCampaign" class="gr_select campaignSelect">
							{foreach $campaigns as $campaign}
								<option value="{$campaign['id']|escape:'htmlall':'UTF-8'}" {if $selected_campaign == $campaign['id']}selected{/if}>{$campaign['name']|escape:'htmlall':'UTF-8'}</option>
							{/foreach}
						</select>
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

			<fieldset class="control-group">
				<div class="controls">
					<input type="submit" value="{l s='Save' mod='getresponse'}" name="EditAutomationConfiguration" class="button">
					<div class="button edit_automation_cancel" onclick="cancel_automation_edit()">{l s='Cancel' mod='getresponse'}</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>
