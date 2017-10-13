{*
* @author Getresponse <grintegrations@getresponse.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<ul class="gr_menu">
{if $is_connected}
	<li class="{if $selected_tab == 'api'}current-menu-item{/if}">
		<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=api" class="gr-api" title="api">{l s='Connection settings' mod='getresponse'}</a>
	</li>
	<li class="{if $selected_tab == 'export_customers'}current-menu-item{/if}">
		<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=export_customers_show" class="gr-export" title="export">{l s='Export customers' mod='getresponse'}</a>
	</li>
	<li class="{if $selected_tab == 'subscribe_via_registration'}current-menu-item{/if}">
		<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=subscribe_via_registration_show" class="gr-subscribe_via_registration_show" title="Subscribe via registration">{l s='Subscription via registration page' mod='getresponse'}</a>
	</li>
	<li class="{if $selected_tab == 'subscribe_via_form'}current-menu-item{/if}">
		<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=subscribe_via_form" class="gr-subscribe_via_form" title="subscribe_via_form">{l s='Subscription via a form' mod='getresponse'}</a>
	</li>
	<li class="{if $selected_tab == 'automation'}current-menu-item{/if}">
		<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=automation" class="gr-automation" title="automation">{l s='Automatic segmentation' mod='getresponse'}</a>
	</li>
	{if $active_tracking != 'disabled'}
	<li class="{if $selected_tab == 'tracking'}current-menu-item{/if}">
		<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=tracking" class="gr-tracking" title="tracking">{l s='Web Traffic Tracking' mod='getresponse'}</a>
	</li>
	{/if}
	<li class="{if $selected_tab == 'ecommerce'}current-menu-item{/if}">
		<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=ecommerce" class="gr-ecommerce" title="ecommerce">{l s='Ecommerce' mod='getresponse'}</a>
	</li>
{else}
	<li class="{if $selected_tab == 'api'}current-menu-item{/if}">
		<a href="{$action_url|escape:'htmlall':'UTF-8'}&action=api" class="gr-api" title="api">{l s='Connection settings' mod='getresponse'}</a>
	</li>
	<li class="{if $selected_tab == 'export_customers'}current-menu-item{/if}">
		<a class="inactive gr-export" title="export">{l s='Export customers' mod='getresponse'}</a>
	</li>
	<li class="{if $selected_tab == 'subscribe_via_registration'}current-menu-item{/if}">
		<a class="inactive gr-subscribe_via_registration_show" title="Subscribe via registration">{l s='Subscription via registration page' mod='getresponse'}</a>
	</li>
	<li class="{if $selected_tab == 'subscribe_via_form'}current-menu-item{/if}">
		<a class="inactive gr-subscribe_via_form" title="subscribe_via_form">{l s='Subscription via a form' mod='getresponse'}</a>
	</li>
	<li class="{if $selected_tab == 'automation'}current-menu-item{/if}">
		<a class="inactive gr-automation" title="automation">{l s='Automatic segmentation' mod='getresponse'}</a>
	</li>
{/if}
</ul>
