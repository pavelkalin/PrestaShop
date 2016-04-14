{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="content">
	<h3>{l s='GetResponse forms' mod='getresponse'}</h3>
	<p>
		{l s='Add your GetResponse forms to your PrestaShop store. Simply pick one of the forms you’ve created in GetResponse and choose the location to insert the form. You can display the form in its original GetResponse style or the plain PrestaShop style.' mod='getresponse'}
	</p>
	<form class="form-horizontal" action="{$action_url|escape:'htmlall':'UTF-8'}&action=viawebform" method="post">
		<div class="control-group" id="chckSubscriptoinDiv">
			<label for="targetCampaign" class="control-label">{l s='Publish' mod='getresponse'}</label>
			<div class="controls">
				<a title="Status" data-params="{ run: 'switch_webformpage' }" href="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=viawebform#iswitch" id="chckSubscriptionInput" class="switch {if $webform_status == 'yes'}enabled{else}disabled{/if}">
					<span data-iswitch="" class="s-css3 {if $webform_status == 'yes'}enabled{else}disabled{/if}">
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
					<input type="checkbox" value="yes" id="chckSubscriptoinInput" name="webform_status" {if $webform_status == 'yes'}checked{/if}/>
				</a>
			</div>
		</div>
		<div id="form-box" {if $webform_status == 'yes'} style="display:block"{else} style="display:none"{/if}>
			<div class="highslide-body">
				<fieldset class="control-group" id="webformIdDiv">
					<label for="webformId" class="control-label">{l s='Select a form' mod='getresponse'}</label>
					<div class="controls">
						<select name="webform_id" id="webformSidebar" class="gr_select fullselect">
							{if $webforms}
								<option value="" disabled>Old Webforms:</option>
								{foreach $webforms as $wid => $webform}
									<option value="{$webform->webformId|escape:'htmlall':'UTF-8'}" {if $webform_id == $webform->webformId}selected{/if}>{$webform->name|escape:'htmlall':'UTF-8'} ({$webform->campaign->name|escape:'htmlall':'UTF-8'}) {if $webform->status != 'enabled'}(DISABLED IN GR){/if}</option>
								{/foreach}
							{else}
								<option value="" disabled>{l s='No old webforms' mod='getresponse'}</option>
							{/if}
							{if $forms}
								<option value="" disabled>New Forms:</option>
								{foreach $forms as $wid => $form}
									<option value="{$form->webformId|escape:'htmlall':'UTF-8'}" {if $webform_id == $form->webformId}selected{/if}>{$form->name|escape:'htmlall':'UTF-8'} ({$form->campaign->name|escape:'htmlall':'UTF-8'}) {if $form->status != 'published'}(DISABLED IN GR){/if}</option>
								{/foreach}
							{else}
								<option value="" disabled>{l s='No new forms' mod='getresponse'}</option>
							{/if}
						</select>
					</div>
				</fieldset>

				<fieldset class="control-group" id="webformSidebarDiv">
					<label for="webformSidebar" class="control-label">{l s='Select where to place the form' mod='getresponse'}</label>
					<div class="controls">
						<select name="webform_sidebar" id="webformSidebar" class="gr_select fullselect">
							<option value="home" {if $webform_sidebar == 'home'}selected{/if}>{l s='Homepage' mod='getresponse'}</option>
							<option value="left" {if $webform_sidebar == 'left'}selected{/if}>{l s='Left sidebar' mod='getresponse'}</option>
							<option value="right" {if $webform_sidebar == 'right'}selected{/if}>{l s='Right sidebar' mod='getresponse'}</option>
							<option value="header" {if $webform_sidebar == 'header'}selected{/if}>{l s='Header' mod='getresponse'}</option>
							<option value="top" {if $webform_sidebar == 'top'}selected{/if}>{l s='Top' mod='getresponse'}</option>
							<option value="footer" {if $webform_sidebar == 'footer'}selected{/if}>{l s='Footer' mod='getresponse'}</option>
						</select>
					</div>
				</fieldset>

				<fieldset class="control-group" id="webformStyleDiv">
					<label for="webformStyle" class="control-label">{l s='Select the style' mod='getresponse'}</label>
					<div class="controls input-tip">
						<select name="webform_style" id="webformStyle" class="gr_select fullselect">
							<option value="webform" {if $webform_style == 'webform'}selected{/if}>Web Form</option>
							<option value="prestashop" {if $webform_style == 'prestashop'}selected{/if}>Prestashop</option>
						</select>
						<span>
							<abbr title='{l s='Web form style' mod='getresponse'}|{l s='Selecting style works only if you still have legacy web forms enabled. In this case you can make your web form match the PrestaShop design or use the style defined in GetResponse.<br /><br />This feature doesn’t apply to regular GetResponse forms.' mod='getresponse'}' mod='getresponse' rel="tooltip"></abbr>
						</span>
					</div>
				</fieldset>
				<fieldset class="control-group" id="saveDiv">
					<div class="controls">
						<div class="btns">
							<input type="submit" value="{l s='Save' mod='getresponse'}" name="ViawebformConfiguration" class="button">
						</div>
					</div>
				</fieldset>
			</div>
		</div>
	</form>
	{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/new_campaign.tpl"}
</div>