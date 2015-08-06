{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div id="add_campaign" style="display:none;" class="gr-wrapper lightboxHtmlContent">
	<div class="highslide-body">
		<h3 class="cnt">{l s='Create new campaign' mod='getresponse'}</h3>
		<div class="form-horizontal" action="">

			<div class="control-group" id="campaignDiv">
				<label for="webformId" class="control-label">{l s='Campaign name' mod='getresponse'}:</label>
				<div class="controls">
					<input name="campaign_name" type="text" id="campaignName" placeholder="Campaign Name">
					<a class="gr-tooltip">
						<span class="gr-tip">
							<h5>{l s='Campaign name' mod='getresponse'}</h5>
							<p>
								{l s='Campaign name must be between 3-64 characters, only a-z (lower case), numbers and "_"' mod='getresponse'}
							</p>
						</span>
					</a>
				</div>
			</div>

			<div class="control-group" id="fromFieldDiv">
				<label for="from" class="control-label">{l s='From' mod='getresponse'}</label>
				<div class="controls select-wide">
					<select name="from_field" id="fromField" class="gr_select hiddenselect">
						{if isset($fromfields)}
							{foreach $fromfields as $fromfield}
								<option value="{$fromfield['id']|escape:'htmlall':'UTF-8'}">{$fromfield['name']|escape:'htmlall':'UTF-8'} ({$fromfield['email']|escape:'htmlall':'UTF-8'})</option>
							{/foreach}
						{/if}
					</select>
				</div>
			</div>

			<div class="control-group" id="replyToDiv">
				<label for="replyTo" class="control-label">{l s='Reply to' mod='getresponse'}</label>
				<div class="controls select-wide">
					<select name="reply_to_field" id="replyTo" class="gr_select hiddenselect">
						{if isset($fromfields)}
							{foreach $fromfields as $fromfield}
								<option value="{$fromfield['id']|escape:'htmlall':'UTF-8'}">{$fromfield['name']|escape:'htmlall':'UTF-8'} ({$fromfield['email']|escape:'htmlall':'UTF-8'})</option>
							{/foreach}
						{/if}
					</select>
				</div>
			</div>

			<div class="control-group" id="saveDiv">
				<div class="controls">
					<a class="button" type="button" onClick="addCampaign();false;"><strong>{l s='Create' mod='getresponse'}</strong></a>
				</div>
			</div>

			<div id="MessageAjax"></div>

			<div class="clearer"/>
		</div>
		{literal}
		<script>
			function addCampaign()
			{
				$.post('{/literal}{$action_url}{literal}+&ajax&action=addcampaign', {
					add_contact: 1,
					from_field: $('#fromField').val(),
					campaign_name: $('#campaignName').val(),
					reply_to_field: $('#replyTo').val(),
					token: '{/literal}{$token|escape:'htmlall':'UTF-8'}{literal}'
				}, function(json) {
					if (json != null) {
						if (json.type == 'success'){
							$.MyG.show(json.msg);
							window.setTimeout(function() {
								window.location.href = $(location).attr('href')+'&c='+json.c;
							}, 1000);
						}
						else if (json.type == 'error') {
							$.MyG.show(json.msg);
						}
					}
					else {
						$.MyG.show(json.msg);
					}
				}, 'json');
			}
		</script>
		{/literal}
	</div>
</div>
