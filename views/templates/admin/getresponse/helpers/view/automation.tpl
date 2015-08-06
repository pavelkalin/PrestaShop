{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<h3>{l s='Automation' mod='getresponse'}</h3>

{if !empty($no_automation) && $no_automation == 'yes'}
	<div>
		<p>
			<a href="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=automation&amp;edit_id=new#add_automation" data-lightbox="{ iframe: { src: activator.href }, preloading: true, hideOuterScroll: true, defaultHeight: 300, width: 700 }" class="button fright">{l s='Create new automation' mod='getresponse'}</a>
			{l s='GetResponse enables you to automatically move or copy your customers between GetResponse campaigns once they purchase in a particular Prestashop product category. To do this, click “Create new” button and choose automation parameters. When you select "move" option, the rule will move contacts from ALL existing campaigns to the destination campaign. To add contacts to another campaign simply choose "copy" option. ' mod='getresponse'}
		</p>
	</div>
{else}
	<p>
		<a href="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=automation&amp;edit_id=new#add_automation" data-lightbox="{ iframe: { src: activator.href }, preloading: true, hideOuterScroll: true, defaultHeight: 300, width: 700 }" class="button fright">{l s='Create new automation' mod='getresponse'}</a>
		{l s='GetResponse enables you to automatically move or copy your customers between GetResponse campaigns once they purchase in a particular Prestashop product category. To do this, click “Create new” button and choose automation parameters. When you select "move" option, the rule will move contacts from ALL existing campaigns to the destination campaign. To add contacts to another campaign simply choose "copy" option. ' mod='getresponse'}
	</p>

	<form class="" action="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=automation" method="post">
		{if !empty($automation_settings)}
			<table class="primasort">
				<thead>
				<tr>
					<th scope="col" class="first" style="width:390px;">
						<a href="#" class=" current">{l s='Category' mod='getresponse'}</a>
					</th>
					<th scope="col">
						<a href="#" class=" up ">{l s='Destination Campaign' mod='getresponse'}</a>
					</th>
					<th scope="col" style="width:70px;">
						<a href="#" class=" up">{l s='Action' mod='getresponse'}</a>
					</th>
					<th scope="col" class="last rt" style="width:65px;">
						<a href="#" class=" up">{l s='Status' mod='getresponse'}</a>
					</th>
				</tr>
				</thead>
				<tbody>
					{foreach $automation_settings as $automation}
						<tr>
							<th scope="row">
								<h6>
									{if $categories}
										{foreach $categories as $category}
											{if $category['id_category'] == $automation['category_id']}
												<span id="CategorySpan_{$automation['id']|escape:'htmlall':'UTF-8'}" class="{if $automation['active'] == 'no'}disabled-automation{/if}">{$category['name']|escape:'htmlall':'UTF-8'}</span>
											{/if}
										{/foreach}
									{/if}
								</h6>
								<ul class="item-dropdown-menu">
									<li><a>{l s='Actions' mod='getresponse'}</a>
										<ul>
											<li>
												<a href="{$action_url|escape:'htmlall':'UTF-8'}&amp;action=automation&amp;edit_id={$automation['id']|escape:'htmlall':'UTF-8'}" data-lightbox="{ iframe: { src: activator.href }, preloading: true, hideOuterScroll: true, defaultHeight: 675, width: 700 }">{l s='Edit' mod='getresponse'}</a>
											</li>
											<li><a href="{$action_url|escape:'htmlall':'UTF-8'}&action=automation&delete_id={$automation['id']|escape:'htmlall':'UTF-8'}" onclick="return confirm('Are you sure?')">{l s='Delete' mod='getresponse'}</a></li>
										</ul>
									</li>
								</ul>
							</th>
							<td>
								{if $campaigns}
									{foreach $campaigns as $campaign}
										{if $campaign['id'] == $automation['campaign_id']}
											<span id="CampaignSpan_{$automation['id']|escape:'htmlall':'UTF-8'}" class="{if $automation['active'] == 'no'}disabled-automation{/if}">{$campaign['name']|escape:'htmlall':'UTF-8'}</span>
										{/if}
									{/foreach}
								{/if}
							</td>
							<td>
								<span id="ActionSpan_{$automation['id']|escape:'htmlall':'UTF-8'}" class="{if $automation['active'] == 'no'}disabled-automation{/if}">
									{if $automation['action'] == 'move'}{l s='Move' mod='getresponse'}{/if}
									{if $automation['action'] == 'copy'}{l s='Copy' mod='getresponse'}{/if}
								</span>
							</td>
							<td class="last rt">
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
				</tbody>
			</table>
		{/if}
		<input type="hidden" name="show_box" id="show_box" value="{$show_box|escape:'htmlall':'UTF-8'}"/>
	</form>
{/if}