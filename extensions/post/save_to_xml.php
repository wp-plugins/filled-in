<?php

class Save_To_XML extends FI_Post
{
	var $has_run = false;
	
	function process (&$source)
	{
		$write_header = false;
		if (file_exists ($this->config['filename']) === false || $this->config['overwrite'] == 'true')
			$write_header = true;
			
		if ($this->has_run == true)
			$write_header = false;
		
		$file = fopen ($this->config['filename'], $write_header == true ? 'w+' : 'r+');
		if ($file)
		{
			if ($write_header == true)
				@fputs ($file, "<?xml version=\"1.0\"?>\r\n<filledin>\r\n");
			else
				@fseek ($file, -13, SEEK_END);  // This ensures we overwrite the closing element

			$post    = $source->get_source ('post');
			$cookies = $source->get_source ('cookies');
			$server  = $source->get_source ('server');

			$data = "\t<submission date=\"".date ('Y-m-d', $source->created)."\" time=\"".date ('H:i:s', $source->created)."\">\r\n";
			$data .= "\t\t<user ip=\"{$server->remote_host}\">".htmlspecialchars ($server->user_agent)."</user>\r\n";
			$data .= "\t\t<completed>{$post->time_to_complete}</completed>\r\n";
			
			if (count ($post->data) > 0)
			{
				foreach ($post->data AS $key => $value)
					$data .= "\t\t<field name=\"$key\">".htmlspecialchars ($post->display ($key, false))."</field>\r\n";
			}

			if (is_array ($cookies->data) && count ($cookies->data) > 0)
			{
				foreach ($cookies->data AS $key => $value)
					$data .= "\t\t<cookie name=\"$key\">".htmlspecialchars ($value)."</field>\r\n";
			}

			$data .= "\t</submission>\r\n</filledin>\r\n";
			
			$this->has_run = true;
			@fputs ($file, $data);
			@fclose ($file);
			return true;
		}
		
		return __ ('Unable to save XML data', 'filled-in');
	}

	function name ()
	{
		return __ ("Save to XML file", 'filled-in');
	}
	
	function edit ()
	{
		?>
    <tr>
      <td width="130"><?php _e ('XML Filename', 'filled-in') ?>:</td>
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

$this->register ('Save_To_XML');
?>