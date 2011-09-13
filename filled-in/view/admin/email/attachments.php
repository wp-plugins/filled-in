<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><ul>
	<?php foreach ($files AS $upload) :?>
	<li>
		<a style="float: right" class="nol" href="#" onclick="delete_file('<?php echo $name ?>','<?php echo urlencode ($upload->name) ?>'); return false"><img title="delete" src="<?php bloginfo ('wpurl') ?>/wp-content/plugins/filled-in/images/delete.png" alt="delete" width="16" height="16"/></a>
		<a title="<?php _e ('Download file', 'filled-in'); ?>" href="<?php bloginfo ('wpurl') ?>/wp-content/plugins/filled-in/controller/attachment.php?id=<?php echo $name ?>&amp;file=<?php echo urlencode ($upload->name) ?>"><?php echo $upload->name ?></a>
	</li>
	<?php endforeach; ?>
</ul>