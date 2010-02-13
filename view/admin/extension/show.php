<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="options">
<?php if ($extension->status == 'off') : ?>
	<a href="#" title="<?php _e ('Enable', 'filled-in') ?>" class="nol" onclick="return enable_extension(<?php echo $extension->id ?>,'<?php echo $extension->what_group () ?>','<?php echo wp_create_nonce ('filledin-extension_enable_'.$extension->id); ?>');">
		<img src="<?php echo $this->url () ?>/images/enable.png" alt="edit" width="16" height="16"/>
	</a>
<?php else : ?>
	<a href="#" title="<?php _e ('Disable', 'filled-in') ?>" class="nol" onclick="return disable_extension(<?php echo $extension->id ?>,'<?php echo $extension->what_group () ?>','<?php echo wp_create_nonce ('filledin-extension_disable_'.$extension->id); ?>');">
		<img src="<?php echo $this->url () ?>/images/disable.png" alt="edit" width="16" height="16"/>
	</a>
<?php endif; ?>
<a href="#" class="nol" onclick="return delete_extension(<?php echo $extension->id ?>,'<?php echo $extension->what_group () ?>','<?php echo wp_create_nonce ('filledin-extension_delete_'.$extension->id); ?>');"><img src="<?php echo $this->url () ?>/images/delete.png" alt="edit" width="16" height="16"/></a>
</div>

<?php if ($extension->status == 'on' && $extension->is_editable ()) : ?>
<a href="#" onclick="return edit_extension(<?php echo $extension->id ?>,'<?php echo $extension->what_group () ?>')">
<?php endif; ?>

	<?php echo $extension->show () ?>
	
<?php if ($extension->status == 'on') : ?>
</a>
<?php endif; ?>
