<?php

class Pre_Logged_Out extends FI_Pre
{
	function process (&$source)
	{
		if (is_user_logged_in ())
			return __ ("You must not be logged in", 'filled-in');
		return true;
	}
	
	function name ()
	{
		return __ ("Must not be logged in", 'filled-in');
	}
}

$this->register ('Pre_Logged_Out');
?>