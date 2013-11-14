<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
	
<?php if (count ($reports) > 0 && count ($forms) > 0) : ?>
<div class="wrap">
	<?php $this->render_admin ('annoy'); ?>
	<?php screen_icon(); ?>
	
	<h2><?php _e ('Batch Processing', 'filled-in'); ?></h2>
	<?php $this->submenu (true); ?>

	<p style="clear: both"><?php _e ('While your original form data will remain untouched, please be aware that replaying data could cause unwanted side effects', 'filled-in'); ?></p>
	
	<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" accept-charset="utf-8">
	<table width="100%" class="filled_in">
		<tr>
			<th><?php _e ('Run form', 'filled-in'); ?>:</th>
			<td>
<?php if (count ($forms) > 0) : ?>
				<select name="form">
					<?php foreach ($forms AS $form) : ?>
					<option value="<?php echo $form->id ?>"><?php echo htmlspecialchars ($form->name) ?></option>
					<?php endforeach; ?>
				</select>
<?php endif; ?>
				
<?php if (count ($reports) > 0) : ?>
				<?php _e ('through', 'filled-in'); ?>: 
				<select name="report">
				<?php foreach ($reports AS $report) : ?>
				<option value="<?php echo $report->id ?>"><?php echo htmlspecialchars ($report->name) ?></option>
				<?php endforeach; ?>
				</select>
<?php endif; ?>
			</td>
		</tr>
		
		<tr>
			<th valign="top"><?php _e ('Start date', 'filled-in'); ?>:<br/>
				<span class="sub"><?php _e ('Start date for data', 'filled-in'); ?></span>
			</th>
			<td>
				<select name="start_type" onchange="if (this.options[this.selectedIndex].value == 'custom') Element.show ('start_time'); else Element.hide ('start_time');">
					<option value="first"><?php _e ('First entry', 'filled-in'); ?></option>
					<option value="custom"><?php _e ('Custom date', 'filled-in'); ?></option>
				</select>
				
				<div id="start_time" style="display: none">
					<?php $this->render_admin ('date_and_time', array ('name' => 'start', 'current_month' => substr ($start_date, 5, 2), 'current_year' => substr ($start_date, 0, 4), 'current_day' => substr ($start_date, 8, 2), 'hour' => '00', 'minute' => '00', 'months' => $months)); ?>
				</div>
			</td>
		</tr>

		<tr>
			<th valign="top"><?php _e ('End date', 'filled-in'); ?>:<br/>
				<span class="sub"><?php _e ('End date for data', 'filled-in'); ?></span>
				</th>
			<td>
				<select name="end_type" onchange="if (this.options[this.selectedIndex].value == 'custom') Element.show ('end_time'); else Element.hide ('end_time');">
					<option value="first"><?php _e ('Last entry', 'filled-in'); ?></option>
					<option value="custom"><?php _e ('Custom date', 'filled-in'); ?></option>
				</select>
				
				<div id="end_time" style="display: none">
					<?php $this->render_admin ('date_and_time', array ('name' => 'end', 'current_month' => substr ($end_date, 5, 2), 'current_year' => substr ($end_date, 0, 4), 'current_day' => substr ($end_date, 8, 2), 'hour' => 23, 'minute' => 59, 'months' => $months)); ?>
				</div>
			</td>
		</tr>
		
		<tr>
			<th></th>
			<td>
				<input class="button-secondary" type="submit" name="run" value="<?php _e ('Run', 'filled-in'); ?>" id="run"/>
			</td>
		</tr>
		
	</table>
	</form>

</div>
<?php endif; ?>

<div class="wrap">
	<?php if (count ($reports) == 0 || count ($forms) == 0 ) : ?>
	<?php $this->render_admin ('annoy'); ?>
	<?php screen_icon(); ?>
	<?php endif;?>
	
	<h2><?php _e ('Batches', 'filled-in'); ?></h2>
	<?php if (count ($reports) == 0 || count ($forms) == 0 ) : ?>
	<?php $this->submenu (true); ?>
	<?php endif;?>
	
	<?php if (count ($reports) > 0) : ?>
	<ul class="emails" style="clear: both">
		<?php foreach ($reports AS $report) : ?>
		<li id="form_<?php echo $report->id ?>">
			<?php $this->render_admin ('report/item', array ('base' => $base, 'report' => $report)); ?>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php else : ?>
	<p style="clear: both"><?php _e ('You have no reports', 'filled-in'); ?></p>
	<?php endif; ?>
	
	<h3><?php _e ('Create new batch', 'filled-in') ?></h3>
	
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" class="form-table">
		<?php _e ('Name', 'filled-in') ?>: <input type="text" name="form_name"/>
		<input class="button-primary" type="submit" name="create" value="<?php _e ('Create', 'filled-in'); ?>"/>
	</form>
</div>

