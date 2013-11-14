<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="pager"><?php _e ('Results per page', 'filled-in') ?>: 
	<form method="get" action="<?php echo $pager->url ?>">
		<input type="hidden" name="page" value="filled_in.php"/>
		<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>

		<select name="perpage">
			<?php foreach ($pager->steps AS $step) : ?>
		  	<option value="<?php echo $step ?>"<?php if ($pager->per_page == $step) echo ' selected="selected"' ?>><?php echo $step ?></option>
			<?php endforeach; ?>
		</select>
		
		<input type="submit" name="go" value="<?php _e ('go', 'filled-in') ?>"/>
	</form>
</div>
