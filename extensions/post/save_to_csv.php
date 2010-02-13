<?php

class Save_To_CSV extends FI_Post
{
	var $has_run = false;
	
	function process (&$source)
	{
		$write_header = false;
		if (file_exists ($this->config['filename']) === false || $this->config['overwrite'] == 'true')
			$write_header = true;
		
		if ($this->has_run == true)
			$write_header = false;
			
		$file = @fopen ($this->config['filename'], $write_header === true ? 'w' : 'a');
		if ($file)
		{
			$server  = $source->get_source ('server');
			$cookies = $source->get_source ('cookies');
			$post    = $source->get_source ('post');
			$files   = $source->get_source ('files');
			
			// Form the data from fields + special
			$data['date']             = $this->escape (date ('Y-m-d', $source->created));
			$data['time']             = $this->escape (date ('H:i:s', $source->created));
			$data['time_to_complete'] = $this->escape ($post->time_to_complete);
			$data['user_agent']       = $this->escape ($server->user_agent);
			$data['ip']               = $this->escape ($server->remote_host);
			
			if (count ($post->data) > 0)
			{
				foreach ($post->data AS $key => $value)
					$data[$key] = $this->escape ($post->display ($key, false));
			}
			
			if (count ($files->data) > 0 && is_array ($files->data))
			{
				foreach ($files->data AS $key => $fileitem)
				{
					$tmp = array ();
					foreach ($fileitem AS $item)
						$tmp[] = $item->stored_location;
						
					$data[$key] = implode (',', $tmp);
				}
			}
			
			$cookiedata = array ();
			if (is_array ($cookies->data) && count ($cookies->data) > 0)
			{
				foreach ($cookies->data AS $key => $value)
					$cookiedata[] = $this->escape ($cookies->data[$key]);
			}

			if ($write_header)
				@fputs ($file, implode (',', array_keys ($data))."\r\n");		

			$str = implode (',', $data);
			if (count ($cookiedata) > 0)
				$str .= ','.implode (',', $cookiedata);
				
			@fputs ($file, $str."\r\n");
			@fclose ($file);
			$this->has_run = true;
			return true;
		}
		
		return __ ('Unable to save CSV data', 'filled-in');
	}

	function escape ($value)
	{
		// Escape any special values
		$double = false;
		if (strpos ($value, ',') !== false || strpos ($value, "\r") !== false || strpos ($value, "\n") !== false)
			$double = true;

		if (strpos ($value, '"') !== false)
		{
			$double = true;
			$value  = str_replace ('"', '""', $value);
		}

		$value = str_replace ("\n", "\r", $value);
		if ($double === true)
			$value = '"'.$value.'"';
		return $value;
	}
	
	function name ()
	{
		return __ ("Save to CSV file", 'filled-in');
	}
	
	function edit ()
	{
		?>
    <tr>
      <td width="130"><?php _e ('CSV Filename', 'filled-in') ?>:</td>
      <td><input style="width: 95%" type="text" name="filename" value="<?php echo htmlspecialchars ($this->config['filename']) ?>"/></td>
    </tr>
		<tr>
			<td width="130"><?php _e ('Overwrite', 'filled-in') ?>:</td>
      <td><input type="checkbox" name="overwrite" <?php if ($this->config['overwrite'] == 'true') echo ' checked="checked"'?>/></td>
		</tr>
	  <?php
	}
	
	function show ()
	{
		parent::show ();
		$filename = $this->config['filename'];
		
		if ($filename == '')
			$filename    = __ ('<em>&lt;not configured&gt;</em>', 'filled-in');
		
		if ($this->config['overwrite'] == 'true')
			$overwrite = __ ('(file is overwritten)', 'filled-in');

	  ?>
	  '<strong><?php echo $filename ?></strong>' <?php echo $overwrite ?>
		<?php
	}
	
	function save ($config)
	{
		return array ('filename' => preg_replace ('/[\*\?]/', '', $config['filename']), 'overwrite' => isset ($config['overwrite']) ? 'true' : 'false');
  }

	function is_editable () { return true;}
}

$this->register ('Save_To_CSV');
?>