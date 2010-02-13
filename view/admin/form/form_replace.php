<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<form method="post" action="<?php echo $action.$top ?>" <?php echo $params ?><?php echo $upload ?>>
	<input type="hidden" name="filled_in_form" value="<?php echo $formid ?>"/>
	<input type="hidden" name="filled_in_start" value="<?php echo $time ?>"/>

	<?php echo $inside; ?>
</form>

<?php if (isset($ajax)) echo $ajax ?>
