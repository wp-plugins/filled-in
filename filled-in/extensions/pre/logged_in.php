<?php

class Pre_Logged_in extends FI_Pre
{
	function process (&$source)
	{
		if (is_user_logged_in ())
			return true;
		return __ ("You must be logged in", 'filled-in');
	}
	
	function name ()
	{
		return __ ("Must be logged in", 'filled-in');
	}
}

$this->register ('Pre_Logged_in');
?>