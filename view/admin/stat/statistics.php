<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<h2>
<?php if (true || current_user_can ('administrator')) : ?>
		<?php echo $title ?> for '<a href="<?php bloginfo ('wpurl') ?>/wp-admin/edit.php?page=filled_in.php&amp;edit=<?php echo $form->id ?>"><?php echo htmlspecialchars ($form->name) ?></a>'
<?php else : ?>
	<?php echo $title ?> for '<?php echo htmlspecialchars ($form->name) ?>'
<?php endif; ?>
	</h2>
	
		<?php $this->submenu (true); ?>
		
		<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">	
			<input type="hidden" name="page" value="filled_in.php"/>
			<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>
			<p class="search-box">
				<label for="post-search-input" class="hidden"><?php _e ('Search') ?>:</label>

				<input type="text" class="search-input" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars ($_GET['search']) : ''?>"/>
				<?php if (isset ($_GET['search']) && $_GET['search'] != '') : ?>
					<input type="hidden" name="ss" value="<?php echo htmlspecialchars ($_GET['search']) ?>"/>
				<?php endif;?>

				<input type="submit" class="button" value="Search"/>
			</p>

			<div class="tablenav">
                            <?php if (current_user_can ('administrator')): ?>
				<div class="alignleft actions">
					<select name="action2" id="action2_select">
						<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
						<option value="delete"><?php _e('Delete'); ?></option>
					</select>

					<input type="submit" value="<?php _e('Apply'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />

					<?php $pager->per_page ('filled-in'); ?>

					<input type="submit" value="<?php _e('Filter'); ?>" class="button-secondary" />

					<br class="clear" />
				</div>
                            <?php endif; ?>
				<div class="tablenav-pages">
					<?php echo $pager->page_links (); ?>
				</div>
			</div>
		</form>
		
<?php if (count ($stats) > 0) : ?>

	<table class="widefat post fixed">
		<thead>
		<?php $this->render_admin ('stat/head', array ('columns' => $columns)); ?>
		</thead>

		<tbody>
		<?php $alt = 0; foreach ($stats AS $statistic) : ?>
		<tr <?php if ($alt++ % 2 == 1) echo ' class="alt"' ?> id="s_<?php echo $statistic->id ?>">
			<?php if (current_user_can ('administrator')): ?><td width="16" class="item center">
				<input type="checkbox" class="check" name="checkall[]" value="<?php echo $statistic->id ?>"/>
			</td><?php endif; ?>
			<td class="date">
				<a href="<?php echo $this->url () ?>/controller/admin_ajax.php?cmd=show_stat&amp;id=<?php echo $statistic->id ?>" class="filledin-show-stat"><?php echo date ('d M y', $statistic->created) ?></a>
			</td>

			<?php
				foreach ($columns AS $column)
					$this->render_admin ('stat/stat_columns', array ('data' => $statistic->get_source ('post'), 'cookies' => $statistic->get_source ('cookies'), 'column' => $column, 'errors' => isset ($statistic->errors) ? $statistic->errors : false));
			?>
		</tr>

		<tr id="d_<?php echo $statistic->id ?>" style="display: none"><td/></tr>

		<?php endforeach; ?>
		</tbody>
	</table>

	<div id="loading" style="display: none">
		<img src="<?php echo $this->url () ?>/images/loading.gif" alt="loading" width="32" height="32"/>
	</div>

	<?php else : ?>
		<p><?php _e ('There are no entries', 'filled-in') ?></p>
	<?php endif; ?>
</div>

<?php if (current_user_can ('administrator')) : ?><div class="wrap">
	<h2><?php _e ('Delete all entries', 'filled-in'); ?></h2>
	<p><?php _e ('Once deleted, your data will be permanently gone.  Please be sure this is what you really want to do.', 'filled-in'); ?></p>
	
	<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" accept-charset="utf-8">
		<input class="button-primary" type="submit" name="delete" value="<?php _e ('Delete all entries', 'filled-in') ?>" id="delete" onclick="if (confirm (wp_confirm_data)) return true; return false;"/>
	</form>
</div><?php endif; ?>

<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function()
	{
		jQuery('.select-all').click (function (item)
		{
			var check = jQuery('.select-all').attr ('checked');
		  jQuery('.item :checkbox').each (function ()
		  {
		    this.checked = check;
		  });
	
			return true;
		});
		
		jQuery('#doaction2').click (function ()
		{
			if (jQuery('#action2_select').attr ('value') == 'delete')
			  /* ///  Modification  1.7.6 - bugfix */
				/* //deleteItems ('delete_stats','<?php echo wp_create_nonce ('filledin-delete_items'); ?>'); */
				deleteItems ('delete_stats','<?php echo wp_create_nonce ('filledin-delete_stats'); ?>');
				/* /// End of modification */
			return false;
		});
		
		jQuery('.filledin-show-stat').click (function ()
		{
			var item = jQuery(this).parent ().parent ().attr ('id').substring (2);
			if (jQuery('#d_' + item).is (':visible'))
				jQuery('#d_' + item).hide ();
			else
			{
				jQuery('#d_' + item).show ();
				jQuery('#d_' + item).load (this.href);
			}
			return false;
		});
	});
</script>