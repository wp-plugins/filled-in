<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<form method="post" action="" onsubmit="update_extension (<?php echo $extension->id ?>,'<?php echo $extension->what_group () ?>',this); return false">
	<strong><?php echo $extension->name () ?></strong>
	<table class="edit_filter" width="100%">
		
		<?php echo $extension->edit (); ?>
		
  	<tr>
			<th></th>
			<td>
				<input class="button-primary" type="submit" name="save" value="Save"/>
				<input class="button-secondary" type="button" name="cancel" value="Cancel" onclick="show_extension (<?php echo $extension->id ?>,'<?php echo $extension->what_group () ?>'); return false"/>
			</td>
		</tr>
	</table>
</form>
