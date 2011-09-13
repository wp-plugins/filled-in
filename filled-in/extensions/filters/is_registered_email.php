<?php

class Filter_WP_Email extends FI_Filter
{
	function filter (&$value, $all_data)
	{
		if (!function_exists ('validate_username'))
			require_once( ABSPATH . WPINC . '/registration.php');

		$email = apply_filters( 'user_registration_email', $value);

		// Check the username
		if ( $email == '' )
			return __('please enter a valid username', 'filled-in');
		else if (email_exists ($email))
			return __ ('already exists, please choose another', 'filled-in');
		elseif (!is_email( $email ))
			return __('please enter a valid username.');

		return true;
	}
	
	function name ()
	{
		return __ ("WP Email", 'filled-in');
	}

	function show ()
	{
		parent::show ();
		_e ('is not a registered <strong>WordPress Email</strong>', 'filled-in');
	}
}

$this->register ('Filter_WP_Email');
?>