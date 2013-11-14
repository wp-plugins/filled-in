<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<select name="type" id="add_type_<?php echo $group ?>">
<?php foreach ($extensions AS $type => $name) : ?>
  <option value="<?php echo $type ?>"><?php echo $name ?></option>
<?php endforeach; ?>
</select>