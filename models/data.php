<?php

include (dirname (__FILE__).'/form_replacer.php');
include (dirname (__FILE__).'/pager.php');

class FI_Data_Source
{
	var $data = null;
	
	function FI_Data_Source ($data, $config = '')
	{
	}

	function sync ($created) { }
	function save ($dataid, $filters) { return array (); }
	function what_group () { return 'error'; }
	function refill_data ($text, $errors) { return $text; }
	function display ($field) { }
	function delete () { }
}
	
class FI_Data
{
	var $id;
	var $form_id;
	var $created;

	var $sources;
	
	function FI_Data ($values = null, $time = null)
	{
		if (is_object ($values))
		{
			$this->id      = $values->id;
			$this->created = mysql2date ('U', $values->created);
			$this->form_id = $values->form_id;
			
			$this->sources['post']    = new FI_Data_POST ($values);
			$this->sources['server']  = new FI_Data_SERVER ($values);
			$this->sources['cookies'] = new FI_Data_COOKIES ($values);
			$this->sources['files']   = new FI_Data_FILES ($values);
		}
		else
		{
			$this->created = time ();

			if ($time != null)
				$this->created = $time;
		}
	}
	
	function add_source ($source)
	{
		$this->sources[$source->what_group ()] = $source;
		$this->sources[$source->what_group ()]->sync ($this->created);
	}
	
	function create ($formid)
	{
		assert ('intval ($formid) > 0');
		global $wpdb;

		$time = date ('Y-m-d H:i:s', $this->created);
		if ($wpdb->query ("INSERT INTO {$wpdb->prefix}filled_in_data (form_id, created) VALUES ('$formid', '$time')"))
		{
			$this->form_id = $formid;
			$this->id      = $wpdb->insert_id;
			return true;
		}
		
		return false;		
	}
	
	function &get_source ($group)
	{
		if (isset ($this->sources[$group]))
			return $this->sources[$group];
		return false;
	}
	
	function save ($filters)
	{
		global $wpdb;
		
		// Get each source to save its data
		$values = array ();

		foreach ($this->sources AS $pos => $source)
			$values = array_merge ($values, $this->sources[$pos]->save ($this->id, $filters));

		// Update data item
		$parts = array ();
		foreach ($values AS $key => $value)
		{
			if (!empty ($value))
				$parts[] = "$key='".$wpdb->escape ($value)."'";
		}

		global $wpdb;
		$sql = "UPDATE {$wpdb->prefix}filled_in_data SET ".implode (', ', $parts)." WHERE id={$this->id}";
		return $wpdb->query ($sql);
	}
	
	function load ($dataid)
	{
		global $wpdb;
		
		$row = $wpdb->get_row ("SELECT data.*,agent.agent AS user_agent_name FROM {$wpdb->prefix}filled_in_data AS data,{$wpdb->prefix}filled_in_useragents AS agent WHERE data.id='$dataid' AND data.user_agent=agent.id");
		if ($row)
			return new FI_Data ($row);
		return false;
	}
	
	function delete ()
	{
		global $wpdb;
		foreach ($this->sources AS $pos => $source)
			$this->sources[$pos]->delete ();
		return $wpdb->query ("DELETE FROM {$wpdb->prefix}filled_in_data WHERE id='{$this->id}'");
	}
	
	// Fills data back into the form
	function refill_data ($text, $errors)
	{
		assert (is_a ($errors, 'FI_Errors'));

		foreach ($this->sources AS $pos => $source)
			$text = $this->sources[$pos]->refill_data ($text, $errors, $this->id);
		return $text;
	}
	
	function load_by_form ($formid, &$pager, $startdate = '', $enddate = '')
	{
		assert (is_a ($pager, 'FI_Pager'));
		global $wpdb;

		$daterange = '';
		if ($startdate != '')
			$daterange .= " AND {$wpdb->prefix}filled_in_data.created > '$startdate'";
			
		if ($enddate != '')
			$daterange .= " AND {$wpdb->prefix}filled_in_data.created < '$enddate'";
		
		// Big SQL to get all rows that don't have errors
		$sql = "SELECT {$wpdb->prefix}filled_in_data.*,{$wpdb->prefix}filled_in_useragents.agent AS user_agent_name FROM {$wpdb->prefix}filled_in_data
		 			  LEFT JOIN {$wpdb->prefix}filled_in_errors ON {$wpdb->prefix}filled_in_data.id={$wpdb->prefix}filled_in_errors.data_id
						LEFT JOIN {$wpdb->prefix}filled_in_useragents ON {$wpdb->prefix}filled_in_data.user_agent={$wpdb->prefix}filled_in_useragents.id";
					
		$total = str_replace (",{$wpdb->prefix}filled_in_useragents.agent AS user_agent_name", '', $sql);
		$total = str_replace ("{$wpdb->prefix}filled_in_data.*", 'COUNT(*)', $total);

		$pager->set_total ($wpdb->get_var ($total.$pager->to_conditions ("{$wpdb->prefix}filled_in_errors.data_id IS NULL AND {$wpdb->prefix}filled_in_data.form_id=$formid $daterange", array ('data', 'cookie'))));

		$results = array ();
		$rows = $wpdb->get_results ($sql.$pager->to_limits ("{$wpdb->prefix}filled_in_errors.data_id IS NULL AND {$wpdb->prefix}filled_in_data.form_id=$formid $daterange", array ('data', 'cookie')));

		if ($rows)
		{
			foreach ($rows AS $row)
				$results[] = new FI_Data ($row);
		}
		
		return $results;
	}
	
	function load_by_form_errors ($formid, &$pager)
	{
		assert (is_a ($pager, 'FI_Pager'));

		global $wpdb;

		$pager->set_total ($wpdb->get_var ("SELECT COUNT(data.id) FROM {$wpdb->prefix}filled_in_data AS data,{$wpdb->prefix}filled_in_errors AS errors".$pager->to_conditions ("data.form_id='$formid' AND data.id=errors.data_id", array ('data', 'cookie'))));
		
		$results = array ();
		$rows = $wpdb->get_results ("SELECT data.*,errors.type AS error_type,errors.message AS error_message FROM {$wpdb->prefix}filled_in_data AS data,{$wpdb->prefix}filled_in_errors AS errors".$pager->to_limits ("data.form_id='$formid' AND data.id=errors.data_id", array ('data', 'cookie')));

		if ($rows)
		{
			foreach ($rows AS $row)
			{
				$data = new FI_Data ($row);
				$data->errors = new FI_Errors (array ('id' => 1, 'form_id' => $formid, 'data_id' => $row->id, 'type' => $row->error_type, 'message' => $row->error_message));
				$results[] = $data;
			}
		}
		
		return $results;
	}
	
	function delete_results ($formid)
	{
		global $wpdb;
		
		$sql = "DELETE FROM {$wpdb->prefix}filled_in_data USING {$wpdb->prefix}filled_in_data
		 			  LEFT JOIN {$wpdb->prefix}filled_in_errors ON {$wpdb->prefix}filled_in_data.id={$wpdb->prefix}filled_in_errors.data_id
					  WHERE {$wpdb->prefix}filled_in_errors.data_id IS NULL AND {$wpdb->prefix}filled_in_data.form_id=$formid ";

		$wpdb->query ($sql);
	}
	
	function delete_errors ($formid)
	{
		global $wpdb;

		$sql = "DELETE FROM {$wpdb->prefix}filled_in_data USING {$wpdb->prefix}filled_in_data
		 			  LEFT JOIN {$wpdb->prefix}filled_in_errors ON {$wpdb->prefix}filled_in_data.id={$wpdb->prefix}filled_in_errors.data_id
					  WHERE {$wpdb->prefix}filled_in_errors.data_id={$wpdb->prefix}filled_in_data.id AND {$wpdb->prefix}filled_in_data.form_id=$formid ";
		
		$wpdb->query ($sql);
		$wpdb->query ("DELETE FROM {$wpdb->prefix}filled_in_errors WHERE form_id=$formid");
	}
}



include (dirname (__FILE__).'/data/source_post.php');
include (dirname (__FILE__).'/data/source_files.php');
include (dirname (__FILE__).'/data/source_cookies.php');
include (dirname (__FILE__).'/data/source_server.php');



?>
