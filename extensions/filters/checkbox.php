<?php

class Filter_Checkbox extends FI_Filter
{
	function filter (&$value, $all_data)
	{
		if ($this->config['smallest'] > 0 || $this->config['largest'] > 0)
		{
			if ($this->config['smallest'] > 0 && $this->config['largest'] > 0)
			{
				if (count ($value) >= $this->config['smallest'] && count ($value) <= $this->config['largest'])
					return true;
					
				if (!is_array ($value))
					$value = 'no';
				return sprintf (__ ('between %d and %d items are required', 'filled-in'), $this->config['smallest'], $this->config['largest']);
			}
			else if ($this->config['smallest'] > 0)
			{
				if (count ($value) < $this->config['smallest'])
				{
					if (!is_array ($value))
						$value = 'no';
					return sprintf (__ ("at least %d item(s) are required", 'filled-in'), $this->config['smallest']);
				}
			}
			else if ($this->config['largest'] > 0)
			{
				if (count ($value) > $this->config['largest'])
				{
					if (!is_array ($value))
						$value = 'no';
					return sprintf (__ ("no more than %d item(s) are required", 'filled-in'), $this->config['largest']);
				}
			}
			else if (count ($value) == 0)
				$value = __ ('no', 'filled-in');
		}
		else if ($value == 'on')
			$value = __ ('yes', 'filled-in');
		else if (empty ($value))
			$value = __ ('no', 'filled-in');
		return true;
	}
	
	function name ()
	{
		return __ ("Checkbox/Radio", 'filled-in');
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
		<td><input type="text" name="smallest" value="<?php echo $this->config['smallest'] ?>"/> <span class="sub"><?php _e ('items required, leave empty for no smallest', 'filled-in'); ?></span></td>
	</tr>
	<tr>
		<th><?php _e ('Largest', 'filled-in'); ?>:</th>
		<td><input type="text" name="largest" value="<?php echo $this->config['largest'] ?>"/> <span class="sub"><?php _e ('items required, leave empty for no smallest', 'filled-in'); ?></span></td>
	</tr>
	<?php
	}
	
	function show ()
	{
		parent::show ();
		_e ('is a <strong>Checkbox/Radio</strong>', 'filled-in');
		if ($this->config['smallest'] > 0 && $this->config['largest'] > 0)
			printf (__ (' with between %d and %d items'), $this->config['smallest'], $this->config['largest']);
		else if ($this->config['smallest'] > 0)
			printf (__ (' with at least %d item(s)'), $this->config['smallest']);
		else if ($this->config['largest'] > 0)
			printf (__ (' with less than %d item(s)'), $this->config['largest']);
	}
}

$this->register ('Filter_Checkbox');
?>