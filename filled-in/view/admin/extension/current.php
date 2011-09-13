<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div id="extension_<?php echo $group ?>">
	
	<?php if (isset($form->extensions[$group]) && count ($form->extensions[$group]) > 0) : ?>
	<ol id="<?php echo $group ?>_list">
	<?php foreach ($form->extensions[$group] AS $extension) : ?>
		<li id="ext_<?php echo $extension->id ?>"<?php if ($extension->status == 'off') echo 'class="disabled"' ?>>
			<?php $this->render_admin ('extension/show', array ('extension' => $extension)) ?>
		</li>
	<?php endforeach; ?>
	</ol>
	<?php endif; ?>

	<form method="post" action="<?php echo str_replace ('&', '&amp;', $_SERVER['REQUEST_URI']) ?>" onsubmit="add_extension(<?php echo $form->id ?>,'<?php echo $group ?>', this,'<?php echo wp_create_nonce ('filledin-add_extension'); ?>'); return false">
		<?php _e ('Add new', 'filled-in') ?>
		<?php $factory = FI_Extension_Factory::get (); $this->render_admin ('extension/available', array ('extensions' => $factory->group ($group), 'group' => $group)) ?>
		<input class="button-secondary" type="submit" name="add" value="Add"/>
	</form>

<div id="ext_loading_<?php echo $group ?>" style="display: none">
  <img src="<?php echo $this->url () ?>/images/loading.gif" width="32" height="32" alt="loading"/>
</div>

<?php if (isset($form->extensions[$group]) && count ($form->extensions[$group]) > 1) : ?>
<script type="text/javascript" charset="utf-8">
	jQuery('#<?php echo $group ?>_list').sortable ({ cursor: 'move',  update: function() { save_extension_order ('<?php echo $group ?>'); }});
</script>
<?php endif; ?>

<?php if (isset($error) && !$error && $error != '') $this->render_error ($error);?>
</div>