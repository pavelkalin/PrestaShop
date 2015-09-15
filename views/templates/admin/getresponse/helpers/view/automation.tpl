{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="content">
	<h3>{l s='Automation' mod='getresponse'}</h3>

	{if !empty($no_automation) && $no_automation == 'yes'}
		<div>
			<p>
				{l s='GetResponse enables you to automatically move or copy your customers between GetResponse campaigns once they purchase in a particular Prestashop product category. To do this, click “Create new” button and choose automation parameters. When you select "move" option, the rule will move contacts from ALL existing campaigns to the destination campaign. To add contacts to another campaign simply choose "copy" option. ' mod='getresponse'}

				<div class="btns">
					<a href="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=automation&amp;edit_id=new#add_automation" data-label="Create new automation|Close new automation" class="button default show-more">{l s='Create new automation' mod='getresponse'}</a>
				</div>
				{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/new_automation.tpl"}
			</p>
		</div>
	{else}
		<p>
			{l s='GetResponse enables you to automatically move or copy your customers between GetResponse campaigns once they purchase in a particular Prestashop product category. To do this, click “Create new” button and choose automation parameters. When you select "move" option, the rule will move contacts from ALL existing campaigns to the destination campaign. To add contacts to another campaign simply choose "copy" option. ' mod='getresponse'}

			<div class="btns">
				<a href="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=automation&amp;edit_id=new#add_automation" data-label="Create new automation|Close new automation" class="button default show-more">{l s='Create new automation' mod='getresponse'}</a>
			</div>
			{include file="{$gr_tpl_path|escape:'htmlall':'UTF-8'}getresponse/helpers/view/new_automation.tpl"}
		</p>

		<form class="" action="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=automation" method="post">
			{if !empty($automation_settings)}
				<div class="rwd-table">
					<h3>{l s='Automation List' mod='getresponse'}</h3>
					<table>
					    <tr>
					    	<th>{l s='ID' mod='getresponse'}</th>
					        <th>{l s='Category' mod='getresponse'}</th>
					        <th>{l s='Destination Campaign' mod='getresponse'}</th>
					        <th>{l s='Action' mod='getresponse'}</th>
					        <th>{l s='Status' mod='getresponse'}</th>
					    </tr>
					    {foreach $automation_settings as $automation name=id}
					    <tr>
					        <td data-th="{l s='ID' mod='getresponse'}"> <span>{$smarty.foreach.id.iteration|escape:'htmlall':'UTF-8'}</span></td>
					        <td data-th="{l s='Category' mod='getresponse'}">
								{if $categories}
									{foreach $categories as $category}
										{if $category['id_category'] == $automation['category_id']}
											<span id="CategorySpan_{$automation['id']|escape:'htmlall':'UTF-8'}" class="{if $automation['active'] == 'no'}disabled-automation{/if}">{$category['name']|escape:'htmlall':'UTF-8'}</span>
										{/if}
									{/foreach}
								{/if}
								<div class="item-dropdown-menu">
									<div class="menu">
										<a href="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=automation&amp;edit_id={$automation['id']|escape:'htmlall':'UTF-8'}" class="edit">
											<i></i><span>{l s='Edit' mod='getresponse'}</span>
										</a>
										<button class="trigger"></button>
										<ul class="dropdown">
											<li>
												<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=automation&delete_id={$automation['id']|escape:'htmlall':'UTF-8'}" class="del">
													<i></i><span>{l s='Delete' mod='getresponse'}</span>
												</a>
											</li>
										</ul>
									</div>
								</div>
					        </td>
					        <td data-th="{l s='Destination Campaign' mod='getresponse'}">
								{if $campaigns}
									{foreach $campaigns as $campaign}
										{if $campaign['id'] == $automation['campaign_id']}
											<span id="CampaignSpan_{$automation['id']|escape:'htmlall':'UTF-8'}" class="{if $automation['active'] == 'no'}disabled-automation{/if}">{$campaign['name']|escape:'htmlall':'UTF-8'}</span>
										{/if}
									{/foreach}
								{/if}
					        </td>
					        <td data-th="{l s='Action' mod='getresponse'}">
								<span id="ActionSpan_{$automation['id']|escape:'htmlall':'UTF-8'}" class="{if $automation['active'] == 'no'}disabled-automation{/if}">
									{if $automation['action'] == 'move'}{l s='Move' mod='getresponse'}{/if}
									{if $automation['action'] == 'copy'}{l s='Copy' mod='getresponse'}{/if}
								</span>
					        </td>
					        <td data-th="{l s='Status' mod='getresponse'}">
								<a title="Status" href="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=automation&amp;update_id={$automation['id']|escape:'htmlall':'UTF-8'}&amp;update_status={if $automation['active'] =='yes'}no{else}yes{/if}" class="switch {if $automation['active'] =='yes'}enabled{else}disabled{/if}">
									<span data-iswitch="" class="s-css3 {if $automation['active'] =='yes'}enabled{else}disabled{/if}">
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
								</a>
					        </td>
					    </tr>
					    {/foreach}
					</table>
				</div>
				<div id="data-target"></div>
				<div id="automation_form_edit"></div>
			{/if}
			<input type="hidden" name="show_box" id="show_box" value="{$show_box|escape:'htmlall':'UTF-8'}"/>
		</form>
	{/if}
</div>