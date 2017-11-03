{if isset($tracking_email)}
<script type='text/javascript'>
    window.onload = function() {
        gaSetUserId('{$tracking_email}');
    };
</script>
{/if}