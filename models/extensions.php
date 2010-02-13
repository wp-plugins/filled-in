<?php

include (dirname (__FILE__).'/errors.php');
include (dirname (__FILE__).'/extensions/pre.php');
include (dirname (__FILE__).'/extensions/result.php');
include (dirname (__FILE__).'/extensions/post.php');
include (dirname (__FILE__).'/extensions/filter.php');

class FI_Extension
{
	var $id        = null;
	var $form_id   = null;
	var $name      = null;
	var $type      = null;
	var $config    = null;
	var $position  = null;
	var $status    = null;
	
	var $errors    = null;
	
	function FI_Extension ($values)
	{
		assert (is_array ($values));
		
		foreach ($values AS $key => $value)
		 	$this->$key = $value;
		
		if (!is_array ($this->config) && strlen ($this->config) > 0)
			$this->config = unserialize ($this->config);
	}

	function load_by_form ($formid)
	{
		assert ('intval ($formid) > 0');
		
		global $wpdb;

		$items = array ();
		$list = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}filled_in_extensions WHERE form_id='$formid' ORDER BY position ASC", ARRAY_A);
		if (count ($list) > 0)
		{
			foreach ($list AS $item)
			{
				if (class_exists ($item['type']))
				{
					$obj = new $item['type'] ($item);
					$items[$obj->what_group ()][] = $obj;
				}
			}
		}
		
		return $items;
	}
	
	function load ($id)
	{
		assert ('intval ($id) > 0');
		
		global $wpdb;
		$row = $wpdb->get_row ("SELECT * FROM {$wpdb->prefix}filled_in_extensions WHERE id='$id'", ARRAY_A);
		if ($row)
			return new $row['type'] ($row);
		return false;
	}
	
	function delete ()
	{
		global $wpdb;
		return $wpdb->query ("DELETE FROM {$wpdb->prefix}filled_in_extensions WHERE id='{$this->id}'") == 1;
	}

  function update ($config, $name = '', $extraconfig = null)
  {
		assert (is_a ($config, 'FI_Data_POST'));

 		global $wpdb;
 		$this->name   = $wpdb->escape ($name);
		$this->config = $this->save ($config->data);
		if (!is_null ($extraconfig))
			$this->config = array_merge ($this->config, $extraconfig);
			
		$config       = $wpdb->escape (serialize ($this->config));
 		return $wpdb->query ("UPDATE {$wpdb->prefix}filled_in_extensions SET name='{$this->name}', config='$config' WHERE id='{$this->id}'") == 1;
  }

	function create ($formid, $type)
	{
		assert ('intval ($formid) > 0');
		assert (is_string ($type));
		
		global $wpdb;
		
		$type = FI_Form::sanitize_name ($type);
		$type = $wpdb->escape ($type);

		if (class_exists ($type))
		{
			$obj = new $type (array ());
			
			// Get current count
			$count = $wpdb->get_var ("SELECT COUNT(*) FROM {$wpdb->prefix}filled_in_extensions WHERE form_id='$formid' AND base='".$obj->what_group ()."'");
			if ($wpdb->query ("INSERT INTO {$wpdb->prefix}filled_in_extensions (form_id,type,base,position) VALUES ('$formid','$type','".$obj->what_group ()."','$count')") !== false)
				return true;
			return __ ("Failed to add extension to form", 'filled-in');
		}
		return __ ('Extensions type is invalid', 'filled-in');
	}
	
	function disable ()
	{
		global $wpdb;
		$this->status = 'off';
		$wpdb->query ("UPDATE {$wpdb->prefix}filled_in_extensions SET status='off' WHERE id='{$this->id}'");
	}
	
	function enable ()
	{
		global $wpdb;
		$this->status = 'on';
		$wpdb->query ("UPDATE {$wpdb->prefix}filled_in_extensions SET status='on' WHERE id='{$this->id}'");
	}
	
	// Takes an array of filter IDs
	function reorder ($order)
	{
		assert (is_array ($order));
		
		global $wpdb;

		$pos = 0;
		foreach ($order AS $id)
		{
			$wpdb->query ("UPDATE {$wpdb->prefix}filled_in_extensions SET position=$pos WHERE id='$id'");
			$pos++;
		}
	}

	function run (&$data)
	{
		if ($this->is_enabled ())
		{
			$result = $this->process ($data);
			if ($result !== true)
			{
				$data->get_source ($this->what_group ());
				$this->errors = $result;
			}
		
			return $result;
		}
		
		return true;
	}

	
	function replace_fields ($all_data, $text)
	{
		if (is_array ($all_data))
		{
			foreach ($all_data AS $key => $value)
			{
				if (is_array ($value))
					$value = implode (', ', $value);
				$text = str_replace ("\$$key\$", $value, $text);
			}
		}
		
		return $text;
	}
	
	function prefill ($text, $data) { return $text; }
	function what_group () { return ''; }
	function edit () {	}
	function show ()	{	echo '<strong>'.$this->name ().'</strong>'; }
	function save ($config) { return $config; }
	function is_enabled () { return $this->status == 'on'; }
	function name () { return ""; }
	function is_editable () { return false;	}
}

?>