{*
* @author Getresponse <grintegrations@getresponse.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div id="add_campaign" style="display:none;" class="gr-wrapper">
	<div class="highslide-body">
		<div class="form-horizontal" action="">

			<fieldset class="control-group" id="campaignDiv">
				<label for="webformId" class="control-label">{l s='Enter the campaign name' mod='getresponse'}</label>
				<div class="controls">
					<div class="input-tip">
						<input name="campaign_name" type="text" id="campaignName" placeholder="Campaign Name">
						<span>
							<abbr title='{l s='Campaign name' mod='getresponse'}|{l s='Campaign name must be between 3-64 characters. Use only a-z (lower case), numbers, and "_".' mod='getresponse'}' rel="tooltip"></abbr>
						</span>
					</div>
				</div>
			</fieldset>

			<fieldset class="control-group" id="fromFieldDiv">
				<label for="from" class="control-label">{l s='Select the From field' mod='getresponse'}</label>
				<div class="controls select-wide">
					<select name="from_field" id="fromField" class="gr_select hiddenselect">
						{if isset($fromfields)}
							{foreach $fromfields as $fromfield}
								<option value="{$fromfield['id']|escape:'htmlall':'UTF-8'}">{$fromfield['name']|escape:'htmlall':'UTF-8'} ({$fromfield['email']|escape:'htmlall':'UTF-8'})</option>
							{/foreach}
						{/if}
					</select>
				</div>
			</fieldset>

			<fieldset class="control-group" id="replyToDiv">
				<label for="replyTo" class="control-label">{l s='Select reply to address' mod='getresponse'}</label>
				<div class="controls select-wide">
					<select name="reply_to_field" id="replyTo" class="gr_select hiddenselect">
						{if isset($fromfields)}
							{foreach $fromfields as $fromfield}
								<option value="{$fromfield['id']|escape:'htmlall':'UTF-8'}">{$fromfield['name']|escape:'htmlall':'UTF-8'} ({$fromfield['email']|escape:'htmlall':'UTF-8'})</option>
							{/foreach}
						{/if}
					</select>
				</div>
			</fieldset>

			<fieldset class="control-group" id="confirmationSubjectDiv">
				<label for="confirmationSubject" class="control-label">{l s='Select subject line for confirmation message' mod='getresponse'}</label>
				<div class="controls select-wide">
					<select name="confirmation_subject" id="confirmationSubject" class="gr_select hiddenselect">
						{if isset($subscriptionConfirmationsSubject)}
							{foreach $subscriptionConfirmationsSubject as $subject}
								<option value="{$subject['id']|escape:'htmlall':'UTF-8'}">{$subject['name']|escape:'htmlall':'UTF-8'}</option>
							{/foreach}
						{/if}
					</select>
				</div>
			</fieldset>

			<fieldset class="control-group" id="confirmationBodyDiv">
				<label for="confirmationSubject" class="control-label">{l s='Select confirmation message body' mod='getresponse'}</label>
				<div class="controls select-wide">
					<select name="confirmation_body" id="confirmationBody" class="gr_select hiddenselect">
						{if isset($subscriptionConfirmationsBody)}
							{foreach $subscriptionConfirmationsBody as $body}
								<option value="{$body['id']|escape:'htmlall':'UTF-8'}">({$body['name']|escape:'htmlall':'UTF-8'}) {$body['contentPlain']|escape:'htmlall':'UTF-8'}</option>
							{/foreach}
						{/if}
					</select>
				</div>
			</fieldset>

			<fieldset class="control-group" id="saveDiv">
				<div class="controls">
					<div class="btns">
						<a class="button" type="button" onClick="addCampaign();false;">{l s='Create' mod='getresponse'}</a>
					</div>
				</div>
			</fieldset>

			<div id="MessageAjax"></div>

			<div class="clearer"/>
		</div>
		{literal}
		<script>
			function addCampaign()
			{
				$.post('{/literal}{$action_url|escape:'htmlall':'UTF-8'}{literal}+&ajax&action=addcampaign', {
					add_contact: 1,
					from_field: $('#fromField').val(),
					campaign_name: $('#campaignName').val(),
					reply_to_field: $('#replyTo').val(),
					confirmation_subject : $('#confirmationSubject').val(),
					confirmation_body: $('#confirmationBody').val(),
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
