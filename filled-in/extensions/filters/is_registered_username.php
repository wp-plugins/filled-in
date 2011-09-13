<?php

class Filter_Username extends FI_Filter
{
	function filter (&$value, $all_data)
	{
		if (!function_exists ('validate_username'))
			require_once( ABSPATH . WPINC . '/registration.php');

		$user_login = sanitize_user ($value);

		// Check the username
		if ( $user_login == '' )
			return __('please enter a valid username', 'filled-in');
		else if (username_exists ($user_login))
			return __ ('already exists, please choose another', 'filled-in');
		elseif (!validate_username ($user_login ))
			return __('please enter a valid username.');

		return true;
	}
	
	function name ()
	{
		return __ ("WP Username", 'filled-in');
	}

	function show ()
	{
		parent::show ();
		_e ('is not a <strong>WordPress Username</strong>', 'filled-in');
	}
}

$this->register ('Filter_Username');
?>