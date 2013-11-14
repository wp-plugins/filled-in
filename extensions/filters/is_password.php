<?php

class Filter_Is_Password extends FI_Filter
{
	function filter (&$value, $all_data)
	{
		return true;
	}

	function pre ($value)
	{
		return '********';
	}
	
	function name ()
	{
		return __ ("Is Password", 'filled-in');
	}
	
	function show ()
	{
		parent::show ();
		_e ('is <strong>Password</strong>', 'filled-in');
	}
}

$this->register ('Filter_Is_Password');
?>