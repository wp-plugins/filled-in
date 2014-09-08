<?php

class FI_Filter extends FI_Extension
{
	function sanitize_fieldname ($name)
	{
		// Sanitize the form name
		$name = trim ($name);
		$name = str_replace (' ', '_', $name);
		$name = preg_replace ('/[^0-9a-zA-Z_\-:\.]/', '_', $name);
		
		// Reduce underscores
		$name = preg_replace ('/_+/', '_', $name);
		$name = trim ($name, '_');
		
		// First character must be alphabetic
		$name = preg_replace ('/^([0-9]*)(.*)/', '$2', $name);
		return $name;
	}
	
  function update ($config, $name = '', $extraconfig = null)
  {
    $name = ( $name ) ? $name : $this->sanitize_fieldname ($config->data['field_name']);
    $extraconfig = ( $extraconfig ) ? $extraconfig : array ('error' => $config->data['error']);
		assert (is_a ($config, 'FI_Data_POST'));
		return parent::update ($config, $name, $extraconfig);
  }

	function run (&$data)
	{
		assert (is_a ($data, 'FI_Data'));

		$result = true;
		if ($this->is_enabled ())
		{
			$source =& $data->get_source ($this->accept_what_source ());
			if (isset ($source->data[$this->name]))
				$value = $source->data[$this->name];
			else
				$value = null;

			$result = $this->filter ($value, $source->data);
			if ($result !== true)
			{
				if (isset ($this->config['error']) && strlen ($this->config['error']) > 0)
					$this->errors = array ($this->name, $this->config['error']);
				if (!empty($result) && !empty($value))
					$this->errors = array ($this->name, $this->name.' - '.$result);
			}
			
			if (!is_null ($value))
				$source->data[$this->name] = $value;
		}

		return $result;
	}
	
	function modify ($text) { return $text; }
	
	function is_editable () { return true; }
	function what_group () { return 'filter'; }
	function accept_what_source () { return 'post'; }
	function show () { printf (__ ("Field '<strong>%s</strong>' ", 'filled-in'), $this->name); }
	function pre ($value) { return $value; }
	
	// These must be extended in the child class
	function edit ()
	{
		?>
	<tr>
		<th width="100"><?php _e ('Field', 'filled-in') ?>:</th>
		<td>
			<input type="text" name="field_name" value="<?php echo $this->name ?>" style="width: 95%"/>
		</td>
	</tr>
	<tr>
		<th><?php _e ('Error message', 'filled-in') ?>:</th>
		<td>
			<input type="text" name="error" value="<?php echo isset($this->config['error']) ? $this->config['error'] : '' ?>" style="width: 95%"/>
		</td>
	</tr>
	<?php
	}
}


?>