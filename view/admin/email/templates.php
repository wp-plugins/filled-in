<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php $this->render_admin ('annoy'); ?>
	<?php screen_icon(); ?>

  <h2><?php _e ('Email templates', 'filled-in') ?></h2>
	<?php $this->submenu (true); ?>
  <p style="clear: both"><?php _e ('Filled In can email the contents of a form to you when it is successfully completed.  You can edit that email here.', 'filled-in'); ?></p>

  <?php if (is_array ($templates) && count ($templates) > 0) : ?>
		<?php if (current_user_can ('administrator')) : ?>
		<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">	
			<div class="tablenav">
				<div class="alignleft actions">
					<select name="action2" id="action2_select">
						<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
						<option value="delete"><?php _e('Delete'); ?></option>
					</select>

					<input type="submit" value="<?php _e('Apply'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
					<br class="clear" />
				</div>
			</div>
		</form>
                <?php endif; ?>
	
  <table class="widefat post fixed">
		<thead>
			<tr>
				<?php if (current_user_can ('administrator')) : ?><th width="16" class="check-column">
					<input type="checkbox" name="select_all" class="select-all"/>
				</th><?php endif; ?>
				<th><?php _e ('Name', 'filled-in')?></th>
				<th><?php _e ('From', 'filled-in')?></th>
				<th><?php _e ('To', 'filled-in')?></th>
			</tr>
		</thead>
		
    <?php foreach ($templates AS $template) : ?>
      <tr id="temp_<?php echo $template->name ?>">
				<?php Email_Template::show ($template); ?>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
  
  <div id="loading" style="display: none">
    <img src="<?php echo $this->url () ?>/images/loading.gif" width="32" height="32" alt="loading"/>
  </div>
  
	<br/>
  <?php if (current_user_can ('administrator')) : ?><form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" class="form-table">
		<?php wp_nonce_field ('filledin-add_template'); ?>
   	<input type="text" name="name" class="regular-text" size="20"/>
		<input class="button-primary" type="submit" name="create" value="<?php _e ('Create Template', 'filled-in') ?>"/>
  </form>
	<br/><?php endif; ?>
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
				deleteItems ('delete_templates','<?php echo wp_create_nonce ('filledin-delete_items'); ?>');
			return false;
		});
		
		setupTemplates ();
	});
</script>