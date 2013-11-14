<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
    <?php if (current_user_can ('administrator')) : ?><td class="nol"/><?php endif; ?>
<td colspan="<?php echo $columns + 1 ?>" class="detail nol">

<table class="widefat post fixed" style="width: 95%">
 	<tr>
		<th class="core"><?php _e ('Collected', 'filled-in') ?>:</th>
		<td class="core"><?php printf (__ ('%s from <a href="http://urbangiraffe.com/map/?ip=%s&amp;from=filledin">%s</a>', 'filled-in'), date (get_option ('date_format').' '.get_option ('time_format'), $stat->created), $server->remote_host, $server->remote_host); ?></td>
	</tr>
	<tr>
		<th class="core" valign="top"><?php _e ('Browser', 'filled-in') ?>:</th>
		<td class="core"><?php echo $server->user_agent ?></td>
	</tr>
 	<tr>
		<th class="core" valign="top"><?php _e ('Time', 'filled-in') ?>:</th>
		<td class="core"><?php if ($post->time_to_complete > 0) echo $post->time_to_complete.'s'; ?></td>
	</tr>
 
 <?php if ($errors && $errors->what_type () == 'pre') : ?>
 	<tr class="error">
		<th><?php _e ('Pre-Processor failure', 'filled-in') ?>:</th>
		<td><?php echo htmlspecialchars ($errors->message) ?></td>
	</tr>
 <?php elseif ($errors && $errors->what_type () == 'post') : ?>
 	<tr class="error">
		<th><?php _e ('Post-Processor failure', 'filled-in') ?>:</th>
		<td><?php echo htmlspecialchars ($errors->message) ?></td>
	</tr>
 <?php endif; ?>
 
 <?php if (!empty ($post->data)) { foreach ($post->data AS $key => $value) : ?>
   <?php if ($errors && $errors->in_error ($key)) : ?>
 	<tr class="error">
   <th valign="top" class="data"><?php echo $key ?>:</th>
   <td>
     <strong><?php echo $errors->show_error ($key) ?></strong>
     (<?php echo ($value == '' ? __('&lt;no data&gt;', 'filled-in') : htmlspecialchars ($value)) ?>)
   </td>
   
   <?php else : ?>
 	<tr>
   <th valign="top" class="data"><?php echo $key ?>:</th>
   <td>
     <?php echo htmlspecialchars ($post->display ($key)) ?>
   </td>
 	</tr>
   
   <?php endif; ?>
 <?php endforeach; } ?>

<?php if (!empty ($files) && !empty ($files->data)) :?>
 <?php foreach ($files->data AS $key => $value) : ?>

 <tr class="files">
   <th valign="top"><?php echo $key ?>:</th>
   
   <?php if ($errors && $errors->in_error ($key)) : ?>
   
   <td class="error">
     <strong><?php echo $errors->show_error ($key) ?></strong> - <?php _e ('deleted', 'filled-in'); ?>
   </td>
   
   <?php else : ?>
   
   <td>
		<ul>
		<?php foreach ($files->data[$key] AS $pos => $item) : ?>
			<li>
				<a href="<?php bloginfo ('wpurl') ?>/wp-content/plugins/filled-in/controller/download.php?id=<?php echo $stat->id ?>&amp;name=<?php echo urlencode ($key) ?>&amp;pos=<?php echo $pos ?>" title="download">
				<?php echo htmlspecialchars ($item->name) ?>
				</a>
			</li>
		<?php endforeach; ?>
		</ul>
   </td>
   
   <?php endif; ?>
 </tr>
 <?php endforeach; ?>
<?php endif; ?>

<?php if (count ($cookies->data) > 0 && is_array ($cookies->data)) : ?>
 <?php foreach ($cookies->data AS $key => $value) : ?>
 <tr class="cookie">
   <th valign="top"><?php echo $key ?>:</th>
   <td>
     <?php echo htmlspecialchars (stripslashes ($value)); ?>
   </td>
 </tr>
 <?php endforeach; ?>
<?php endif; ?>
</table>
</td>
