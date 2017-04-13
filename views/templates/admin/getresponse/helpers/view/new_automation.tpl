{*
* @author Getresponse <grintegrations@getresponse.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div id="add_automation" style="display: none;" class="gr-wrapper">
	<div class="highslide-body">
		<form class="form-horizontal" target="_top" action="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=automation" method="post">

			{if $d_categories}
				<fieldset class="control-group">
					<label for="filterCategory" class="control-label">{l s='If customers buy in the category' mod='getresponse'}</label>
					<div class="controls">
						<!-- <div id="ErrorCategory"></div> -->
						<div class="input-tip">
							<select name="category" id="filterCategory" class="gr_select hiddenselect">
							{if $d_categories}
								{foreach $d_categories as $category}
									{if $category['level_depth'] != 0}
										<option value="{$category['id_category']|escape:'htmlall':'UTF-8'}">{$category['name']|escape:'htmlall':'UTF-8'}</option>
									{/if}
								{/foreach}
							{/if}
						</select>
							<span>
								<abbr title='{l s='Category' mod='getresponse'}|{l s='Select a PrestaShop category that will be used to categorize customers. When someone makes a purchase in this category they can be automatically moved or copied to a specific campaign.' mod='getresponse'}' mod='getresponse' rel="tooltip"></abbr>
							</span>
						</div>
					</div>
				</fieldset>

				<fieldset class="control-group">
					<label for="popAction" class="control-label">{l s='they are' mod='getresponse'}:</label>
					<div class="controls">
						<select name="a_action" id="filterAction" class="gr_select hiddenselect">
							<option value="move">{l s='Moved' mod='getresponse'}</option>
							<option value="copy">{l s='Copied' mod='getresponse'}</option>
						</select>
						<div id="ErrorAction"></div>
					</div>
				</fieldset>

				<fieldset class="control-group sel-h126">
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
							<div class="warning">
								<h5>
									{l s='No campaigns' mod='getresponse'}
								</h5>
							</div>
						{/if}
						<div id="ErrorCampaign"></div>
					</div>
				</fieldset>

				{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/add_cycle.tpl"}

				<fieldset class="control-group">
					<div class="controls">
						<div class="btns">
							<input type="submit" value="{l s='Save' mod='getresponse'}" name="NewAutomationConfiguration" class="button">
						</div>
					</div>
				</fieldset>
			{else}
				<div style="text-align:center; font-size: 15px;">
					<p style="color: #ff0000;">{l s='Can not add new automation' mod='getresponse'}.</p>
					<p style="color: #ff0000;">{l s='Maximum number of options has been used' mod='getresponse'}.</p>
				</div>
			{/if}
			<div class="clearer" ></div>
		</form>
	</div>
</div>
