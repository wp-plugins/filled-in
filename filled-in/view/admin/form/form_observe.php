<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div id="form_status_<?php echo $formid ?>" style="display: none">
<?php if ($waiting == '') : ?>
	<img src="<?php echo $this->url () ?>/images/loading.gif" alt="loading" width="32" height="32"/>
	<?php _e ('Please wait...', 'filled-in'); ?>
<?php else : ?>
	<?php echo $waiting; ?>
<?php endif; ?>
</div>

<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function()
{
	jQuery('#<?php echo $name ?>').submit (function (item)
		{
			form_submit(this,'<?php echo $base ?>',<?php echo ($top == '' ? 'false' : 'true') ?>);
			return false;
		});
});
</script>