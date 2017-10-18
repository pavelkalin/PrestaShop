{*
* @author Getresponse <grintegrations@getresponse.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<form method="post" class="form-horizontal">
<div class="panel">
	<div class="panel-heading">
		<i class="icon-gears"></i> GetResponse Account Data
	</div>
	<div class="form-wrapper">
		<div class="form-group">
		<label class="col-lg-3" style="text-align: right"><strong>Status:</strong></label><p class="col-lg-9 text-success"> CONNECTED</p>
		<label class="col-lg-3" style="text-align: right"><strong>API Key:</strong></label><p class="col-lg-9"> {$api_key}</p>
		<label class="col-lg-3" style="text-align: right"><strong>Name:</strong></label><p class="col-lg-9"> {$gr_acc_name}</p>
		<label class="col-lg-3" style="text-align: right"><strong>Email:</strong></label><p class="col-lg-9"> {$gr_acc_email}</p>
		<label class="col-lg-3" style="text-align: right"><strong>Company:</strong></label><p class="col-lg-9"> {if empty($gr_acc_company)} - {else} {$gr_acc_company} {/if}</p>
		<label class="col-lg-3" style="text-align: right"><strong>Phone:</strong></label><p class="col-lg-9"> {if empty($gr_acc_phone)} - {else} {$gr_acc_phone} {/if}</p>
		<label class="col-lg-3" style="text-align: right"><strong>Address:</strong></label><p class="col-lg-9"> {if empty($gr_acc_address)} - {else} {$gr_acc_address} {/if}</p>
		</div>
	</div>
	<div class="panel-footer">
		{literal}
			<button id="disconnectFromGetResponse" type="submit" class="btn btn-default pull-right" name="disconnectFromGetResponse"><i class="icon-getresponse-connect icon-unlink"></i> Disconnect</button>
		{/literal}
	</div>
</div>
</form>
<script>
	$(function(){
		$('#disconnectFromGetResponse').on('click', function(e) {
			if (confirm('Disconnect from GetResponse?' + "\n\n" +
				'When you disconnect you won\'t be able to get new contacts via forms, comments, or during account registration.')){
				return true;
			} else {
				e.preventDefault();
			};
		});
	})
</script>
