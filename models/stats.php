<?php

class FI_FormStats
{
	var $forms;
	
	function FI_FormStats ($forms, &$pager)
	{
		// Get stats for each form
		if (count ($forms) > 0)
		{
			foreach ($forms AS $form)
			{
				$this->forms[] = array
				(
					'form'           => $form,
					'total_results'  => $this->total_results ($form->id),
					'total_errors'   => $this->total_errors ($form->id),
					'last_submitted' => $this->last_submitted ($form->id)
				);
			}
		}
		
		// Custom reorder
		if ($pager->is_secondary_sort ())
		{
			usort ($this->forms, array (&$this, 'sort'.$pager->order_by));
			if ($pager->order_direction == 'DESC')
				$this->forms = array_reverse ($this->forms);
		}
	}
	
	function sort_success ($a, $b)
	{
		if ($a['total_results'] == $b['total_results'])
			return strcmp ($a['form']->name, $b['form']->name);
		return ($a['total_results'] < $b['total_results']) ? -1 : 1;
	}
	
	function sort_failed ($a, $b)
	{
		if ($a['total_errors'] == $b['total_errors'])
			return strcmp ($a['form']->name, $b['form']->name);
		return ($a['total_errors'] < $b['total_errors']) ? -1 : 1;
	}
	
	function sort_last ($a, $b)
	{
		if ($a['last_submitted'] == $b['last_submitted'])
			return strcmp ($a['form']->name, $b['form']->name);
		return ($a['last_submitted'] < $b['last_submitted']) ? -1 : 1;
	}
	
	function total_results ($formid)
	{
		global $wpdb;
    $sql = "SELECT count(`form_id`) - (SELECT count(`form_id`) FROM `{$wpdb->prefix}filled_in_errors` WHERE `form_id`=$formid) FROM `{$wpdb->prefix}filled_in_data` WHERE `form_id`=$formid";
		
		return $wpdb->get_var ($sql);
	}
	
	function total_errors ($formid)
	{
		global $wpdb;
		return $wpdb->get_var ("SELECT COUNT(id) FROM {$wpdb->prefix}filled_in_errors WHERE form_id='$formid'");
	}
	
	function last_submitted ($formid)
	{
		global $wpdb;
		$row = $wpdb->get_row ("SELECT created FROM {$wpdb->prefix}filled_in_data WHERE form_id='$formid' ORDER BY created DESC LIMIT 0,1");
		if (isset ($row->created))
			return mysql2date ('U', $row->created);
		return false;
	}
}
?>