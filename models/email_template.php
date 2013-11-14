<?php

include (dirname (__FILE__).'/email_attachment.php');

class Email_Template extends Filled_In_Plugin
{
  var $name        = null;
  var $from        = null;
  var $subject     = null;
  var $text        = null;
  var $html        = null;
	var $attachments = array ();
	var $to          = null;
  
  function Email_Template ($arr)
  {
		$this->register_plugin ('filled-in', dirname (__FILE__));

    $this->name = $this->sanitize_name ($arr['name']);
    
    if (isset ($arr['from']))
    	$this->from = stripslashes ($arr['from']);
    
    if (isset ($arr['text']))
      $this->text = stripslashes ($arr['text']);
    
    if (isset ($arr['html']))
      $this->html = stripslashes ($arr['html']);

    if (isset ($arr['subject']))
      $this->html = stripslashes ($arr['subject']);
  }
  
	function sanitize_name ($name)
	{
		// Sanitize the form name
		$name = trim ($name);
		$name = preg_replace ('@[/\<\>\*~$#]@', '_', $name);
		
		// Reduce underscores
		$name = preg_replace ('/ +/', ' ', $name);
		return $name;
	}
	
	function show ($template)
	{
		$this->render_admin ('email/item', array ('template' => $template));
	}
}
?>