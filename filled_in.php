<?php
/*
Plugin Name: Filled In
Plugin URI: http://urbangiraffe.com/plugins/filled-in/
Description: Generic form processor allowing forms to be painlessly processed and aggregated, with numerous options to validate data and perform custom commands
Author: John Godley
Version: 1.7.4
Author URI: http://urbangiraffe.com/
*/

include (dirname (__FILE__).'/plugin.php');
include (dirname (__FILE__).'/models/form.php');

global $fi_globals;
$fi_globals = array ();

// And the shortest plugin award goes to...
if (is_admin ())
	include ('controller/admin.php');
else
	include ('controller/front.php');
