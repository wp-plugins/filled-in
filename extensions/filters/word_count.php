<?php

class Filter_Word_Count extends FI_Filter
{
	function filter (&$value, $all_data)
	{
		if ($this->config['shortest'] > 0 && $this->config['longest'] > 0)
		{
			if (str_word_count ($value) >= $this->config['shortest'] && str_word_count ($value) < $this->config['longest'])
				return true;
			return sprintf (__ ('value must be between %d and %d words long', 'filled-in'), $this->config['shortest'], $this->config['longest']);
		}
		else if ($this->config['shortest'] > 0)
		{
			if (str_word_count ($value) >= $this->config['shortest'])
				return true;
			return sprintf (__ ("value must be %d words long", 'filled-in'), $this->config['shortest']);
		}
		else if ($this->config['longest'] > 0)
		{
			if (str_word_count ($value) < $this->config['longest'])
				return true;
			return sprintf (__ ("value must be less than %d words long", 'filled-in'), $this->config['longest']);
		}

		return true;
	}
	
	function name ()
	{
		return __ ("Word Count", 'filled-in');
	}

	
	function save ($config)
	{
		if ($config['longest'] < $config['shortest'])
			$config['longest'] = '';
		return array ('shortest' => intval ($config['shortest']), 'longest' => intval ($config['longest']));
	}
	
	function edit ()
	{
		parent::edit ();
	?>
	<tr>
		<th><?php _e ('Smallest', 'filled-in'); ?>:</th>
		<td><input type="text" name="shortest" value="<?php echo $this->config['shortest'] ?>"/> <span class="sub"><?php _e ('words, leave empty for no shortest', 'filled-in'); ?></em></td>
	</tr>
	<tr>
		<th><?php _e ('Largest', 'filled-in'); ?>:</th>
		<td><input type="text" name="longest" value="<?php echo $this->config['longest'] ?>"/> <span class="sub"><?php _e ('words, leave empty for no longest', 'filled-in'); ?></em></td>
	</tr>
	<?php
	}
	
	function show ()
	{
		parent::show ();
		_e ('with <strong>Word Count</strong> ', 'filled-in');
		if ($this->config['shortest'] > 0 && $this->config['longest'] > 0)
			printf (__ ('between %d and %d words long'), $this->config['shortest'], $this->config['longest']);
		else if ($this->config['shortest'] > 0)
			printf (__ ('at least %d words long'), $this->config['shortest']);
		else if ($this->config['longest'] > 0)
			printf (__ ('less than %d words long'), $this->config['longest']);
		else
			printf (__ ('<em>&lt;not configured&gt;</em>', 'filled-in'));
	}
}

$this->register ('Filter_Word_Count');

?>