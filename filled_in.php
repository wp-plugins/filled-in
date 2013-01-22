<?php
/*
Plugin Name: Filled In
Plugin URI: http://urbangiraffe.com/plugins/filled-in/
Description: Generic form processor allowing forms to be painlessly processed and aggregated, with numerous options to validate data and perform custom commands
Author: John Godley
Version: 1.8.5
Author URI: http://urbangiraffe.com/
*/

/// Addition  1.7.6 - additional API
include (dirname (__FILE__).'/fp-api.php');
/// End of Addition
include (dirname (__FILE__).'/plugin.php');
include (dirname (__FILE__).'/models/form.php');

global $fi_globals;
$fi_globals = array ();

// And the shortest plugin award goes to...
if (is_admin ())
	include (dirname (__FILE__).'/controller/admin.php');
else
	include (dirname (__FILE__).'/controller/front.php');
