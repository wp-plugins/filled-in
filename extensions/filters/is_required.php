<?php

class Filter_Is_Required extends FI_Filter
{
	function filter (&$value, $all_data)
	{
		if ($this->config['smallest'] > 0 && $this->config['largest'] > 0)
		{
			if (count ($value) >= $this->config['smallest'] && count ($value) <= $this->config['largest'])
				return true;
			return sprintf (__ ('between %d and %d items are required', 'filled-in'), $this->config['smallest'], $this->config['largest']);
		}
		else if ($this->config['smallest'] > 0)
		{
			if (count ($value) < $this->config['smallest'])
				return true;
			return sprintf (__ ("at least %d items are required", 'filled-in'), $this->config['smallest']);
		}
		else if ($this->config['largest'] > 0)
		{
			if (count ($value) > $this->config['largest'])
				return true;
			return sprintf (__ ("no more than %d items are required", 'filled-in'), $this->config['largest']);
		}
		else if (strlen ($value) > 0)
			return true;
		return __ ("a value is required", 'filled-in');
	}
	
	function name ()
	{
		return __ ("Is Required", 'filled-in');
	}
	
	function save ($config)
	{
		if ($config['largest'] < $config['smallest'])
			$config['largest'] = '';
		return array ('smallest' => intval ($config['smallest']), 'largest' => intval ($config['largest']));
	}
	
	function edit ()
	{
		parent::edit ();
	?>
	<tr>
		<th><?php _e ('Smallest', 'filled-in'); ?>:</th>
		<td><input type="text" name="smallest" value="<?php echo isset($this->config['smallest']) ? $this->config['smallest'] : '' ?>"/> <span class="sub"><?php _e ('items required, leave empty for no smallest', 'filled-in'); ?></span></td>
	</tr>
	<tr>
		<th><?php _e ('Largest', 'filled-in'); ?>:</th>
		<td><input type="text" name="largest" value="<?php echo isset($this->config['largest']) ? $this->config['largest'] : '' ?>"/> <span class="sub"><?php _e ('items required, leave empty for no largest', 'filled-in'); ?></span></td>
	</tr>
	<?php
	}
	
	function show ()
	{
		parent::show ();
		_e ('is <strong>Required</strong>', 'filled-in');
		if (isset($this->config['smallest']) && $this->config['smallest'] > 0 && $this->config['largest'] > 0)
			printf (__ (' with between %d and %d items'), $this->config['smallest'], $this->config['largest']);
		else if (isset($this->config['smallest']) && $this->config['smallest'] > 0)
			printf (__ (' with at least %d items'), $this->config['smallest']);
		else if (isset($this->config['largest']) && $this->config['largest'] > 0)
			printf (__ (' with less than %d items'), $this->config['largest']);
	}
}

$this->register ('Filter_Is_Required');
?>