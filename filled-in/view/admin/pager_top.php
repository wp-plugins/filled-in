<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="pager">
	<form method="get" action="<?php echo $pager->url ?>">
		<input type="hidden" name="page" value="filled_in.php"/>
		<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>
		<?php if (isset ($_GET['total'])) : ?>
		<input type="hidden" name="total" value="<?php echo esc_attr( $_GET['total'] ) ?>"/>
		<?php else : ?>
		<input type="hidden" name="errors" value="<?php echo esc_attr( $_GET['errors'] ) ?>"/>
		<?php endif; ?>


		<?php _e ('Search', 'filled-in'); ?>: 
		<input type="text" name="search" value="<?php echo htmlspecialchars ($_GET['search']) ?>"/>
		
		<?php _e ('Results per page', 'filled-in') ?>: 
		<select name="perpage">
			<?php foreach ($pager->steps AS $step) : ?>
		  	<option value="<?php echo $step ?>"<?php if ($pager->per_page == $step) echo ' selected="selected"' ?>><?php echo $step ?></option>
			<?php endforeach; ?>
		</select>
		
		<input type="submit" name="go" value="<?php _e ('go', 'filled-in') ?>"/>
	</form>
</div>
