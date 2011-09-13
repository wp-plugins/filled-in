<?php

class Filter_Is_Greater extends FI_Filter
{
	function filter (&$value, $all_data)
	{
		$greater = $this->replace_fields ($all_data, $this->config['value']);

		if (($this->config['equal'] == 'true' && $value >= $greater) ||
			  ($this->config['equal'] == 'false' && $value > $greater))
			return true;
			
		if ($this->config['equal'] == 'true')
			return sprintf (__ ("value must be greater or equal to %d", 'filled-in'), $greater);
		else
			return sprintf (__ ("value must be greater than %d", 'filled-in'), $greater);
	}
	
	function name ()
	{
		return __ ("Is Greater", 'filled-in');
	}

	function save ($config)
	{
		if (isset ($config['equal']))
			$config['equal'] = 'true';
		else
			$config['equal'] = 'false';
		return array ('value' => $config['value'], 'equal' => $config['equal']);
	}
	
	function edit ()
	{
		parent::edit ();
	?>
	<tr>
		<th valign="top"><?php _e ('Greater than', 'filled-in') ?>:</th>
		<td>
			<input type="text" name="value" value="<?php echo $this->config['value'] ?>"/>
			<?php _e ('or equal:', 'filled-in')?>
			<input type="checkbox" name="equal" <?php if ($this->config['equal'] == 'true') echo ' checked="checked"' ?>/>
			<p><?php _e ('Remember that you can use other fields (i.e. this field is less than $otherfield$)', 'filled-in'); ?></p>
		</td>
	</tr>
	<?php
	}
	
	function show ()
	{
		parent::show ();
		if ($this->config['equal'] == 'true')
			_e ('is <strong>Greater Than Or Equal To</strong> ', 'filled-in');
		else
			_e ('is <strong>Greater Than</strong> ', 'filled-in');
			
		if (!isset ($this->config['value']))
			_e ('<em>&lt;not configured&gt;</em>', 'filled-in');
		else
			echo $this->config['value'];
	}
}

$this->register ('Filter_Is_Greater');
?>