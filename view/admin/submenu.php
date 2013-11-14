<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<ul <?php echo $class ?>>
  <li><a <?php if ($sub == '') echo 'class="current"'; ?>href="<?php echo $url ?>"><?php _e ('Forms', 'filled-in') ?></a><?php echo $trail; ?></li>
  <li><a <?php if ($sub == 'templates') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=templates"><?php _e ('Email Templates', 'filled-in') ?></a></li>
	
	<?php if (current_user_can ('activate_plugins')) : ?>
  <li><?php echo $trail; ?><a <?php if ($sub == 'reports') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=reports"><?php _e ('Batch Processing', 'filled-in') ?></a><?php echo $trail; ?></li>
  <li><a <?php if ($sub == 'options') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=options"><?php _e ('Options', 'filled-in') ?></a></li>
	<?php endif; ?>
</ul>