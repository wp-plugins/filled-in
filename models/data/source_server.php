<?php

class FI_Data_SERVER extends FI_Data_Source
{
	var $remote_host    = '';
	var $user_agent     = '';
	var $user_agent_id  = 0;
	
	function FI_Data_SERVER ($data, $config = '')
	{
		if (is_array ($data))
		{
			// Extract core data from $_SERVER
			if (isset ($data['REMOTE_ADDR']))
			  $this->remote_host = $data['REMOTE_ADDR'];
			else if (isset ($data['HTTP_X_FORWARDED_FOR']))
			  $this->remote_host = $data['HTTP_X_FORWARDED_FOR'];
		
			$this->user_agent = $data['HTTP_USER_AGENT'];
		}
		else if (is_object ($data))
		{
			// Extract data from database
			$this->remote_host   = long2ip ($data->ip);
			$this->user_agent_id = $data->user_agent;
			$this->user_agent    = isset($data->user_agent_name) ? $data->user_agent_name : '';
		}
	}
	
	function save ($dataid, $filters)
	{
		// Create a user agent record, if necessary
		global $wpdb;
		$agent = $wpdb->get_row ("SELECT * FROM {$wpdb->prefix}filled_in_useragents WHERE agent='{$this->user_agent}'");
		if (!$agent)
		{
			$wpdb->query ("INSERT INTO {$wpdb->prefix}filled_in_useragents (agent) VALUES('".$wpdb->escape ($this->user_agent)."')");
			$this->user_agent_id = intval ($wpdb->insert_id);
		}
		else
			$this->user_agent_id = intval ($agent->id);

		$values['ip']         = sprintf ('%u', ip2long ($this->remote_host));
		$values['user_agent'] = intval ($this->user_agent_id);
		return $values;
	}

	function what_group () { return 'server'; }
}

?>