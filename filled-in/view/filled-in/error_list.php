<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="form_notice">
	<p style="float: left; width: 140px">
		<img src="<?php bloginfo ('wpurl') ?>/wp-content/plugins/filled-in/images/error_big.png" alt="error" width="128" height="128"/>
	</p>
	
	<p><?php echo $message ?></p>

	<div style="margin-left: 120px">
		<ul>
<?php if (is_array ($errors->message)) : ?>
			<?php foreach ($errors->message AS $error) : ?>
				<li><?php echo $error; ?></li>
			<?php endforeach; ?>
<?php else : ?>
			<li><?php echo $errors->message ?></li>
<?php endif; ?>
		</ul>
	</div>

	<div style="clear: both"></div>
</div>
