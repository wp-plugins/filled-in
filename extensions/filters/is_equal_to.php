<?php

class Filter_Is_Equal extends FI_Filter
{
	function filter (&$value, $all_data)
	{
		$this->config['values'] = $this->replace_fields ($all_data, $this->config['values']);
		$equals = preg_split ('/[\n\r]+/', $this->config['values']);
		if (count ($equals) > 0)
		{
			$matched = false;
			foreach ($equals AS $item)
			{
				if ($this->config['regex'] == 'true')
				{
					if (preg_match ('@'.str_replace ('@', '\\@', $item).'@', $value, $matches) > 0)
					{
						$matched = true;
						break;
					}
				}
				else if ($value == $item)
				{
					$matched = true;
					break;
				}
			}

			if ($matched == true && $this->config['not'] == 'true')
				return sprintf (__ ("must not equal '%s'", 'filled-in'), $value);
			else if ($matched == false && $this->config['not'] != 'true')
			{
				if (count ($equals) == 1)
					return sprintf (__ ("must equal '%s'", 'filled-in'), $this->config['values']);
				else
					return sprintf (__ ('must equal one of: %s', 'filled-in'), "'".implode ('\' or \'', $equals)."'");
			}
		}
		
		return true;
	}
	
	function name ()
	{
		return __ ("Is Equal", 'filled-in');
	}
	
	function save ($config)
	{
		return array ('values' => $config['values'], 'regex' => (isset ($config['regex']) ? 'true' : 'false'), 'not' => isset ($config['not']) ? 'true' : 'false');
	}
	
	function edit ()
	{
		parent::edit ();
	?>
	<tr>
		<th><label for="not"><?php _e ('Not equal')?>:</label></th>
		<td valign="top">
			<input type="checkbox" name="not" id="not" <?php if ($this->config['not'] == 'true') echo ' checked="checked"' ?>/>
		</td>
	</tr>
	<tr>
		<th valign="top"><?php _e ('Values', 'filled-in') ?>:<br/>
			<span class="sub"><?php _e ('Each value on a separate line.', 'filled-in'); ?></span>
		</th>
		<td>
			<textarea name="values" rows="5" style="width: 95%"><?php echo htmlspecialchars ($this->config['values']) ?></textarea>
		</td>
	</tr>
	<tr>
		<th><label for="regex"><?php _e ('Regex')?>:</label></th>
		<td valign="top">
			<input type="checkbox" name="regex" id="regex" <?php if ($this->config['regex'] == 'true') echo ' checked="checked"' ?>/>
		</td>
	</tr>
	<tr>
		<th></th>
		<td><span class="sub"><?php _e ('Remember that you can use other fields (i.e. this field is less than <code>$otherfield$</code>)', 'filled-in') ?></span></td>
	</tr>
	<?php
	}
	
	function show ()
	{
		parent::show ();
		if ($this->config['not'] == 'true')
			_e ('is <strong>Not Equal To</strong>: ', 'filled-in');
		else
			_e ('is <strong>Equal To</strong>: ', 'filled-in');
			
		if (!isset ($this->config['values']) || $this->config['values'] == '')
			_e ('<em>&lt;not configured&gt;</em>', 'filled-in');
		else
		{
			$values = preg_split ('/[\n\r]+/', $this->config['values']);
			echo "'".implode ('\' or \'', $values)."'";
		}
	}
}

$this->register ('Filter_Is_Equal');
?>