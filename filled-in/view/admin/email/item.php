<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<td width="16" class="item center">
	<input type="checkbox" class="check" name="checkall[]" value="<?php echo $template->name ?>"/>
</td>
<td>
	<a href="<?php echo $this->url () ?>/controller/admin_ajax.php?cmd=edit_template&amp;id=<?php echo $template->name ?>" class="filledin-template-edit"><?php echo $template->name ?></a>
</td>
<td><?php echo htmlspecialchars ($template->from); ?></td>
<td><?php echo htmlspecialchars ($template->to); ?></td>


