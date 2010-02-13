<?php

class Pre_Include_Fields extends FI_Pre
{
	function process (&$data)
	{
		$fields = explode (',', $this->config['fields']);
		$newdata = array ();
		
		if (count ($fields) > 0)
		{
			$source =& $data->get_source ('post');
			foreach ($fields AS $field)
			{
				if (isset ($source->data[$field]))
					$newdata[$field] = $source->data[$field];
			}
		}

		$source->data = $newdata;
		return true;
	}
	
	function name ()
	{
		return __ ("Include these fields", 'filled-in');
	}
	
	function edit ()
	{
	  ?>
		<tr>
			<th><?php _e ('Fields', 'filled-in'); ?>:</th>
			<td>
				<input size="40" type="text" name="fields" value="<?php echo htmlspecialchars ($this->config['fields']) ?>"/>
				<span class="sub"><?php _e ('separate fields with comma', 'filled-in'); ?></span>
			</td>
		</tr>
	  <?php
	}
	
	function show ()
	{
		parent::show ();

		$fields = $this->config['fields'];
		if ($fields == '')
			echo __ (' <em>&lt;not configured&gt;</em>', 'filled-in');
		else
			echo " '$fields'";
	}
	
	function save ($config)
	{
	  return array ('fields' => preg_replace ('/[^A-Za-z0-9\-_,]/', '', $config['fields']));
  }
	
	function is_editable ()
	{
		return true;
	}
}

$this->register ('Pre_Include_Fields');
?>