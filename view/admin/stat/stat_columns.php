<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<td <?php if ($errors != false && $errors->in_error ($column)) echo 'class="error"'?>>
<?php if (isset ($data->data[$column])) : ?>
	<?php echo htmlspecialchars (substr ($data->display ($column, false), 0, 40)).(strlen ($data->display($column, false)) > 40 ? "..." : ''); ?>
<?php elseif (isset ($cookies->data[$column])) : ?>
	<?php echo htmlspecialchars (substr ($cookies->data[$column], 0, 40)).(strlen ($cookies->data[$column]) > 40 ? "..." : ''); ?>
<?php endif; ?>
</td>