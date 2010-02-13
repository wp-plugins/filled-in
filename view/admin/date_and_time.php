<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><select id="<?php echo $name ?>_month" name="<?php echo $name ?>_month">
	<?php foreach ($months AS $pos => $month) : ?>
	<option value="<?php echo intval ($pos) ?>" <?php if ($pos == $current_month) echo 'selected="selected"' ?>><?php echo $month ?></option>
	<?php endforeach ?>
</select>

<select id="<?php echo $name ?>_day" name="<?php echo $name ?>_day">
	<?php for ($x = 1; $x < 32; $x++) : ?>
	<option value="<?php echo $x ?>" <?php if ($x == $current_day) echo 'selected="selected"' ?>><?php echo $x ?></option>
	<?php endfor; ?>
</select>

<select id="<?php echo $name ?>_year" name="<?php echo $name ?>_year">
	<?php for ($x = 2006; $x < 2020; $x++) : ?>
	<option value="<?php echo $x ?>" <?php if ($x == $current_year) echo 'selected="selected"' ?>><?php echo $x ?></option>
	<?php endfor; ?>
</select>

@

<input type="text" name="<?php echo $name ?>_hour" size="2" value="<?php echo $hour ?>" id="<?php echo $name ?>_hour"/>
:<input type="text" name="<?php echo $name ?>_minute" size="2" value="<?php echo $minute ?>" id="<?php echo $name ?>_minute"/>
