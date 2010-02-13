<?php

class Filter_File_Upload extends FI_Filter
{
	function filter (&$upload, $all_data)
	{
		$count = count ($upload);

		// Check min/max number of files
		if ($count < $this->config['file_min'] && $this->config['file_min'] != 0)
			return sprintf (__ ('you need to upload at least %d file(s)', 'filled-in'), $this->config['file_min']);

		if ($count > $this->config['file_max'] && $this->config['file_max'] != 0)
			return sprintf (__ ('the maximum number of uploads is %d', 'filled-in'), $this->config['file_max']);
			
		// Now check each file individually
		$errors = array ();

		if ($count > 0)
		{
			foreach ($upload AS $pos => $fileupload)
			{
				$error = false;
			
				// Check filesize
				if ($this->config['file_size'] > 0 && ($fileupload->size () / 1024) > $this->config['file_size'])
				{
					$errors[] = sprintf (__ ('the uploaded file is %dKB, which is larger than the maximum allowed of %dKB', 'filled-in'), $fileupload->size () / 1024, $this->config['file_size']);
					$error = true;
				}

				// Check extension
				$extensions = explode (',', $this->config['file_type']);
				$parts      = explode ('.', $fileupload->name);
				if ($this->config['file_type'] != '' && count ($extensions) > 0 && count ($parts) > 1)
				{
					if (!in_array ($parts[count ($parts) - 1], $extensions))
					{
						$errors[] = __ ('the file is of an invalid type', 'filled-in');
						$error = true;
					}
				}
	
				if ($error)
				{
					unset ($upload[$pos]);
					$fileupload->delete ();
				}
			}
		}

		if (count ($errors) == 0)
			return true;
		return $errors[0];
	}
	
	function name ()
	{
		return __ ("File Upload", 'filled-in');
	}

	
	function save ($config)
	{
		return array
		(
			'file_type' => trim (str_replace ('.', '', $config['file_type'])),
			'file_size' => intval ($config['file_size']),
			'file_min'  => intval ($config['file_min']),
			'file_max'  => intval ($config['file_max'])
		);
	}
	
	function edit ()
	{
		parent::edit ();
	?>
	<tr>
		<th><?php _e ('Max file size', 'filled-in'); ?>:</th>
		<td><input type="text" name="file_size" value="<?php echo $this->config['file_size'] ?>"/> KB</td>
	</tr>
	<tr>
		<th><?php _e ('Min files', 'filled-in'); ?>:</th>
		<td><input type="text" name="file_min" value="<?php echo $this->config['file_min'] ?>"/> (<?php _e ('0 for no minimum', 'filled-in'); ?>)</td>
	</tr>
	<tr>
		<th><?php _e ('Max files', 'filled-in'); ?>:</th>
		<td><input type="text" name="file_max" value="<?php echo $this->config['file_max'] ?>"/> (<?php _e ('0 for no maximum', 'filled-in'); ?>)</td>
	</tr>
	<tr>
		<th valign="top"><?php _e ('File types', 'filled-in'); ?>:</th>
		<td>
			<input type="text" name="file_type" value="<?php echo htmlspecialchars (stripslashes ($this->config['file_type'])) ?>"/>
			<em><?php _e ('Separate file types with a comma.  No period is required', 'filled-in'); ?></em>
			<p><?php _e ('Note: Restriction is only on filename and does not prevent a user uploading a renamed file', 'filled-in'); ?></p>
		</td>
	</tr>
	<?php
	}
	
	function show ()
	{
		parent::show ();
		_e ('with <strong>File Upload</strong> ', 'filled-in');
		$parts = array ();
		if ($this->config['file_size'] > 0)
			$parts[] = sprintf (__ ('maximum filesize of %dKB', 'filled-in'), $this->config['file_size']);
		if ($this->config['file_type'] != '')
			$parts[] = sprintf (__ ('restrict to files of type: %s', 'filled-in'), $this->config['file_type']);
		if ($this->config['file_min'] != 0)
			$parts[] = sprintf (__ ('minimum of %d %s', 'filled-in'), $this->config['file_min'], $this->config['file_min'] == 1 ? __ ('file', 'filled-in') : __ ('files', 'filled-in'));
		if ($this->config['file_max'] != 0)
			$parts[] = sprintf (__ ('maximum of %d %s', 'filled-in'), $this->config['file_max'], $this->config['file_max'] == 1 ? __ ('file', 'filled-in') : __ ('files', 'filled-in'));
			
		echo implode (', ', $parts);
	}
	
	function accept_what_source () { return 'files'; }
}

$this->register ('Filter_File_Upload');

?>