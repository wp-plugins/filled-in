<?php

class FI_Data_FILES extends FI_Data_Source
{
	var $upload_directory;
	var $recovered_data;
	var $form_id;
	
	function FI_Data_FILES ($data, $config = '')
	{
		$this->data = array ();
		
		// Extract upload data, making multiple uploads into a simple array
		if (is_array ($data) && count ($data) > 0)
		{
			foreach ($data AS $field => $upload)
			{
				if (is_array ($upload['name']))
				{
					foreach ($upload['name'] AS $pos => $name)
					{
						if (!empty ($upload['tmp_name'][$pos]) && $upload['error'][$pos] == 0)
							$this->data[$field][] = new FI_Upload (array ('name' => $upload['name'][$pos], 'tmp_name' => $upload['tmp_name'][$pos]));
						else if (!isset ($this->data[$field]))
							$this->data[$field] = array ();
					}
				}
				else if ($upload['error'] == 0)
					$this->data[$field][] = new FI_Upload ($upload);
				else
					$this->data[$field] = array ();
			}
		}
		else if (is_object ($data))
			$this->data = unserialize ($data->upload);

		if (!empty ($config))
		{
			$this->upload_directory = rtrim ($config['upload_directory'], '/');

			if (FI_Errors::load (intval ($config['recovery'])) !== false)
			{
				$data = FI_Data::load (intval ($config['recovery']));
				$this->recovered_data = $data->sources['file']->data;
				if (is_array ($this->recovered_data))
					$this->data = array_merge_recursive ($this->data, $this->recovered_data);
			}
		}
	}
	
	function delete ()
	{
		if (count ($this->data) > 0)
		{
			foreach ($this->data AS $pos => $files)
			{
				foreach ($this->data[$pos] AS $key => $file)
					$this->data[$pos][$key]->delete ();
			}
		}
	}
	
	function save ($dataid, $filters)
	{
		// Return the data array
		if (count ($this->data) > 0)
		{
			$pos = 0;
			foreach ($this->data AS $field => $files)
			{
				foreach ($files AS $item => $file)
				{
					$this->data[$field][$item]->id = $dataid.'_'.$pos;
					$file->id = $dataid.'_'.$pos;
					$dest = "{$this->upload_directory}/{$field}/{$file->id}.upload";
					$pos++;
					
					if (!isset ($this->recovered_data[$field]) && !$file->move_to ($dest))
						unset ($this->data[$field][$item]);
					$this->data[$field][$item] = $file;  // PHP4
				}
			}

			return array ('upload' => serialize ($this->data));
		}
		return array ();
	}
	
	function refill_data ($text, $errors, $id)
	{
		assert (is_string ($text));
		assert (is_a ($errors, 'FI_Errors'));
		
		$saveduploads = array ();

		// Replace uploads
		if (count ($this->data) > 0)
		{
			// Insert data back into form
			foreach ($this->data AS $field => $value)
			{
				if ($errors->in_error ($field))
				{
					// Flag the field as being in error
					$replacer = new Form_Replacer ($field, $value, $errors->in_error ($field));
					$text = preg_replace_callback ('/<input([\w\s_&\.\-"=]+)name="'.$field.'(\[\])?"(.*?)\/>/', array (&$replacer, 'replace_file'), $text);
				}
				else
					$saveduploads[$field] = $value;
			}
		}
		
		if (count ($saveduploads) > 0)
		{
			$plugin = new Filled_In_Plugin;
			$plugin->register_plugin ('filled-in', dirname (__FILE__).'/../../../');
			$first = true;
			
			// Insert a notice about the saved uploads
			foreach ($saveduploads AS $field => $value)
			{
				if (!empty ($value))
				{
					$notice = $plugin->capture ('saved_uploads', array ('uploads' => $value));
					if ($first)
					{
						$notice .= '<input type="hidden" name="filled_in_upload" value="'.$id.'"/>';
						$first = false;
					}
				
					$text = preg_replace ('@<input(.*?)type="file"(.*?)name="'.$field.'([\[\]]*)"(.*?)/>@', $notice, $text, 1);
					$text = preg_replace ('@<input(.*?)type="file"(.*?)name="'.$field.'([\[\]]*)"(.*?)/>@', '', $text, count ($value) - 1);
				}
			}
		}
		
		return $text;
	}

	function what_group () { return 'files'; }
}

?>