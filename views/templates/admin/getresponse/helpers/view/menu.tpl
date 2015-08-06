{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<ul class="gr_menu">
	<li class="{if $selected_tab == 'api'}current-menu-item{/if}">
		<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=api" class="gr-api" title="api">{l s='API key settings' mod='getresponse'}</a>
	</li>
	<li class="{if $selected_tab == 'exportcustomers'}current-menu-item{/if}">
		{if $api_key}
			<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=exportcustomers" class="gr-export" title="export">{l s='Export customers' mod='getresponse'}</a>
		{else}
			<a class="inactive gr-export" title="export">{l s='Export customers' mod='getresponse'}</a>
		{/if}
	</li>
	<li class="{if $selected_tab == 'viapage'}current-menu-item{/if}">
		{if $api_key}
			<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=viapage" class="gr-viapage" title="viapage">{l s='Subscription via %s registration page' mod='getresponse'}</a>
		{else}
			<a class="inactive gr-viapage" title="viapage">{l s='Subscription via %s registration page' mod='getresponse'}</a>
		{/if}
	</li>
	<li class="{if $selected_tab == 'viawebform'}current-menu-item{/if}">
		{if $api_key}
			<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=viawebform" class="gr-viawebform" title="viawebform">{l s='Subscription via Web Form' mod='getresponse'}</a>
		{else}
			<a class="inactive gr-viawebform" title="viawebform">{l s='Subscription via Web Form' mod='getresponse'}</a>
		{/if}
	</li>
	<li class="{if $selected_tab == 'automation'}current-menu-item{/if}">
		{if $api_key}
			<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=automation" class="gr-automation" title="automation">{l s='Automation' mod='getresponse'}</a>
		{else}
			<a class="inactive gr-automation" title="automation">{l s='Automation' mod='getresponse'}</a>
		{/if}
	</li>
</ul>
