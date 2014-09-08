<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="wrap">
	<h2><?php _e ('Form Edit', 'filled-in') ?></h2>
	<?php $this->submenu (true); ?>
	<form style="clear: both" method="post" action="<?php echo str_replace ('&', '&amp;', $_SERVER['REQUEST_URI']) ?>">
		<table class="form-table">
		  <tr>
			<th width="120" valign="top"><?php _e ('Name', 'filled-in') ?>:<br/><span class="sub"><?php _e ('Identifies the form', 'filled-in') ?></span></th>
			<td>
				<input class="regular-text" size="40" type="text" name="new_name" value="<?php echo $form->name ?>"/>

			</td>
		  </tr>
		  <tr>
			<th width="120" valign="top">
				<?php _e ('Quick view', 'filled-in') ?>:<br/>
				<span class="sub"><?php _e ('Fields/cookies to display in the results list', 'filled-in') ?></span>
			</th>
			<td><input class="regular-text" type="text" name="quickview" size="40" value="<?php echo htmlspecialchars ($form->quickview) ?>"/> <span class="sub"><?php _e ('separate with comma', 'filled-in') ?></span></td>
		  </tr>
			<tr>
				<th width="120" valign="top"><?php _e ('Special Options','filled-in') ?>:<br/><span class="sub">Enable AJAX or file uploads</span></th>
				<td valign="top">
					<select name="special">
						<option value="none"><?php _e ('None', 'filled-in') ?></option>
						<option value="ajax"<?php if (isset($form->options['ajax']) && $form->options['ajax'] == 'true') echo ' selected="selected"'; ?>>AJAX</option>
						<option value="upload"<?php if (isset($form->options['upload']) && $form->options['upload'] == 'true') echo ' selected="selected"'; ?>>Allow uploads</option>
					</select>
			</tr>

		  <tr>
		    <td width="120" ></td>
		    <td>
					<input class="button-primary" type="submit" name="update" value="<?php _e ('Update', 'filled-in') ?>"/>
				</td>
		  </tr>

		</table>
	</form>
</div>

<div class="wrap">
  <h2><?php _e ('Extensions', 'filled-in') ?></h2>

	<h3><?php _e ('Pre Processors', 'filled-in') ?>: <span class="sub"><?php _e ('what we do before we begin') ?></span></h3>
	<?php $this->render_admin ('extension/current', array ('form' => $form, 'group' => 'pre'))?>
	
	<h3><?php _e ('Filters', 'filled-in') ?>: <span class="sub"><?php _e ('ensure the data is correct') ?></span></h3>
	<?php $this->render_admin ('extension/current', array ('form' => $form, 'group' => 'filter'))?>
	
	<h3><?php _e ('Post Processors', 'filled-in') ?>: <span class="sub"><?php _e ('what to do with the data when accepted') ?></span></h3>
	<?php $this->render_admin ('extension/current', array ('form' => $form, 'group' => 'post')) ?>
	
	<h3><?php _e ('Result Processor', 'filled-in') ?>: <span class="sub"><?php _e ('what to show the user after correct submission') ?></span></h3>
	<?php $this->render_admin ('extension/current', array ('form' => $form, 'group' => 'result'))?>
</div>

<div class="wrap">
	<h2><?php _e ('Custom Options', 'filled-in') ?></h2>

	<form method="post" action="<?php echo str_replace ('&', '&amp;', $_SERVER['REQUEST_URI']) ?>">
	<table class="form-table">
		<tr>
			<th width="180" valign="top"><?php _e( 'Submit anchor', 'filled-in' ); ?>:<br/><span class="sub"><?php _e ('When a form is submitted the user will be taken to specified anchor. Leave empty to submit to top of page', 'filled-in'); ?></span></th>
			<td valign="top">
				<input type="text" name="submit-anchor" value="<?php echo $form->options['submit-anchor']; ?>" />
			</td>
		</tr>
    <tr>
      <th width="180" valign="top"><?php _e ('Predecessor\'s form allowed ID', 'filled-in'); ?>:<br/><span class="sub"><?php _e ('Define allowed predecessor\'s form ID which has been submitted before this form has shown', 'filled-in'); ?></span></th>
      <td>
        <input type="text" name="custom_id" value="<?php echo $form->options['custom_id']; ?>" />
      </td>
    </tr>
		<tr>
			<th width="180" valign="top"><?php _e ('Custom submit code', 'filled-in'); ?>:<br/><span class="sub"><?php _e ('Override the default AJAX loading notice', 'filled-in'); ?></span></th>
			<td>
				<textarea class="large-text" name="custom_submit" rows="2"><?php echo htmlspecialchars (stripslashes (isset($form->options['custom_submit']) ? $form->options['custom_submit'] : '')) ?></textarea>
			</td>
		</tr>
		<tr>
			<th valign="top"><span class="sub"><?php _e ('Default submit code', 'filled-in'); ?>:</span></th>
			<td>
				<code style="font-size: 0.9em" class="sub">
				&lt;img src=&quot;<?php echo preg_replace ('@http://(.*?)/(.*)@', '/$2', get_bloginfo ('wpurl')) ?>/wp-content/plugins/filled-in/images/loading.gif&quot; alt=&quot;loading&quot; width=&quot;32&quot; height=&quot;32&quot;/&gt;
				<?php _e ('Please wait...', 'filled-in'); ?>
				</code>
			</td>
		</tr>
		
	  <tr>
	    <td width="180"></td>
	    <td>
				<input type="submit" class="button-primary" name="update_options" value="<?php _e ('Update', 'filled-in') ?>"/>
			</td>
	  </tr>
	</table>
	</form>
</div>
