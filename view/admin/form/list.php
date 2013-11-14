<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php $this->render_admin ('annoy'); ?>
	<?php screen_icon(); ?>

	<h2><?php _e ('Form List', 'filled-in') ?></h2>
	<?php $this->submenu (true); ?>

	<?php if (count ($forms) > 0) : ?>

      <?php if( $bDisplayPostError ) : ?>

         <div id="post-error" style="clear: both;">
            <h3>Last error on post extension:</h3>
            <table cellspacing="3" class="widefat post fixed">
               <?php foreach( $aPostErrorData as $strKey => $mixData ) : ?>
                  <tr>
                     <td><?php echo $strKey; ?></td>
                     <td>
                        <?php if( is_array( $mixData ) || is_object( $mixData ) ) : ?>
                           <?php echo str_replace( "\n", '<br />', print_r( $mixData, true ) ); ?>
                        <?php else : ?>
                           <?php echo $mixData; ?>
                        <?php endif; ?>
                     </td>
                  </tr>
               <?php endforeach; ?>
            </table>
            <div>
               <input type="button" name="dismiss" value="Dismiss" id="post-error-dismiss" />
            </div>
         </div>

      <?php endif; ?>

	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">	
		<input type="hidden" name="page" value="filled_in.php"/>
		<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>

		<div id="pager" class="tablenav">
                    <?php if (current_user_can ('administrator')) : ?>
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
        

	<table cellspacing="3" class="widefat post fixed">
	   <thead>
			<tr valign="top">
				<?php if (current_user_can ('administrator')) : ?><th width="16" class="check-column">
					<input type="checkbox" name="select_all" class="select-all"/>
				</th><?php endif; ?>
				<th><?php echo $pager->sortable ('name', __ ('Form name', 'filled-in')); ?></th>
				<th class="center"><a href=""><?php echo $pager->sortable ('_success', __ ('Succeeded', 'filled-in')); ?></th>
				<th class="center"><a href=""><?php echo $pager->sortable ('_failed', __ ('Failed', 'filled-in')); ?></th>
				<th class="center"><a href=""><?php echo $pager->sortable ('_last', __ ('Last Completed', 'filled-in')); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php 
				$alt = 0;
				foreach ($forms AS $form)
					$this->render_admin ('form/list_entry', array ('form' => $form, 'base' => $base, 'admin' => $admin, 'alt' => ($alt++ % 2) == 1 ? 'alt' : ''));
			?>
		</tbody>
	</table>
	
		<div id="loading" style="display: none">
			<img src="<?php bloginfo ('wpurl') ?>/wp-content/plugins/filled-in/images/loading.gif" alt="loading" width="32" height="32"/>
		</div>

	<?php else : ?>
	  <p><?php _e ('You currently have no forms to display.', 'filled-in') ?></p>
	<?php endif; ?>

	<?php if ($admin) : ?>
		<h3><?php _e ('Create new form', 'filled-in') ?></h3>
	
		<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" class="form-table">
			<?php wp_nonce_field ('filledin-create_form'); ?>
			
			<?php _e ('Name', 'filled-in') ?>: <input type="text" name="form_name"/>
			<input class="button-primary" type="submit" name="create" value="Create"/>
		</form>
	<?php endif; ?>
</div>


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
				deleteItems ('delete_forms','<?php echo wp_create_nonce ('filledin-delete_items'); ?>');
			return false;
		});
	});
</script>