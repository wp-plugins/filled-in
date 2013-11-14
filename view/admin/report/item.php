<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="email_content">
	<div class="options">
		<a title="<?php _e ('delete', 'filled-in') ?>" class="nol" href="#" onclick="delete_form (<?php echo $report->id ?>); return false">
			<img src="<?php echo $this->url (); ?>/images/delete.png" alt="report" width="16" height="16"/>
		</a>
	</div>
	<a href="<?php echo $base ?>&amp;sub=reports&amp;edit=<?php echo $report->id ?>"><?php echo htmlspecialchars ($report->name) ?></a>
</div>