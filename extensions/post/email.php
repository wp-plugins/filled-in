<?php

require_once (dirname (__FILE__).'/../../models/email_template.php');

class Post_Email extends FI_Post
{
	var $text             = null;
	var $html             = null;
	var $from             = null;
	var $subject          = null;
	var $uploads_attached = false;
	var $attachments      = array ();
	var $swift            = null;     // We store this for effeciency in reports
	
	function process (&$source)
	{
		if ($this->config['template'] == '' || $this->config['address'] == '')
			return true;

		$post = $source->get_source ('post');

	  $this->from    = '"'.get_option ('blogname').'" <'.get_option ('admin_email').'>';
	  $this->subject = 'Submission to form: '.$post->form->name;
	  
		$to = $this->fill_in_details ($source, $this->config['address']);
    $this->load_template ($this->config['template']);
		
		// Fill in details
		$this->text = $this->fill_in_details ($source, $this->text);
		$this->html = $this->fill_in_details ($source, $this->html, true);
		$this->text = str_replace ('$contents$', $this->text_content ($source), $this->text);
		$this->html = str_replace ('$contents$', $this->html_content ($source), $this->html);

		$this->from     = $this->fill_in_details ($source, $this->from);
		$this->subject  = $this->fill_in_details ($source, $this->subject);		
		
		// Split the 'to' addresses into an array
		$addresses = explode (';', $to);
		if (count ($addresses) > 0)
		{
			$to = array ();
			foreach ($addresses AS $address)
				$to[] = trim ($address);
		}
		else
			$to = array ($to);
		
		return $this->send ($this->from, $to, $this->subject, $this->config['template']);
	}
	
	function send ($from, $to, $subject, $template)
	{
		assert ('strlen ($template) > 0');
		include_once (dirname (__FILE__).'/../../lib/swift3/Swift.php');

		if ($this->swift == null)
		{
			if (get_option ('filled_in_smtp_host') != '')
			{
				$type = SWIFT_SMTP_ENC_OFF;
				if (get_option ('filled_in_smtp_ssl') == 'ssl')
					$type = SWIFT_SMTP_ENC_SSL;
				else if (get_option ('filled_in_smtp_ssl') == 'tls')
					$type = SWIFT_SMTP_ENC_TLS;
					
				include_once (dirname (__FILE__).'/../../lib/swift3/Swift/Connection/SMTP.php');
				$connection = new Swift_Connection_SMTP (get_option ('filled_in_smtp_host'), get_option ('filled_in_smtp_port'), $type);
				if (get_option ('filled_in_smtp_username') != '')
				{
					$connection->setUsername (get_option ('filled_in_smtp_username'));
					$connection->setUsername (get_option ('filled_in_smtp_password'));			
				}
			}
			else
			{
				include_once (dirname (__FILE__).'/../../lib/swift3/Swift/Connection/NativeMail.php');
				$connection = new Swift_Connection_NativeMail ();
			}
		
			$this->swift = &new Swift ($connection);
		}
		
		$message = &new Swift_Message ($subject);

		// Add any attachments
		$attach = new EmailAttachment ($template);
		$attachments = $attach->get ();
		$attachments = array_merge ($attachments, $this->attachments);

		if (count ($attachments) > 0)
		{
			foreach ($attachments AS $upload)
			{
				$info = getimagesize ($upload->stored_location);
				
				// Embed an image or add attachment
				if ($info[2] != 0 && preg_match ('/"(.*?)'.$upload->name.'"/', $this->html) > 0)
					$this->html = preg_replace ('/"(.*?)'.$upload->name.'"/', '"'.$message->attach (new Swift_Message_Image (new Swift_File ($upload->stored_location), $upload->name)).'"', $this->html);
				else
					$message->attach (new Swift_Message_Attachment (new Swift_File ($upload->stored_location), $upload->name));
			}
		}

		// Mangling for the horrible OS X Eudora client
		$this->text = str_replace ("\n", "\r", $this->text);
		
		// Attach the parts
		$message->attach (new Swift_Message_Part ($this->text));
		if (strlen ($this->html) > 0)
		{
			$this->html = '<html><head><title>'.$subject.'</title></head><body>'.wpautop ($this->html).'</body></html>';
			$message->attach (new Swift_Message_Part ($this->html, 'text/html'));
		}
		
		// Get recipients
		$recipients = &new Swift_RecipientList();
		foreach ($to AS $address)
			$recipients->addTo ($this->get_email ($address));
		
		// Send it
		$result = @$this->swift->send ($message, $recipients, $this->get_email ($from));

		if ($result == 0)
			return sprintf (__ ('An error occurred while sending an email', 'filled-in'));
		return true;
	}
	
	function get_email ($address)
	{
		// Returns a Swift_Address
		if (preg_match ('/(.*?)<(.*?)>/', str_replace ('"', '', $address), $matches) > 0)
			return new Swift_Address (trim ($matches[2]), trim ($matches[1]));
		return new Swift_Address ($address);
	}
	
	function load_template ($template)
	{
	  $templates = get_option ('filled_in_templates');
	  if (isset ($templates[$template]))
	  {
	    $this->text = $templates[$template]->text;
	    $this->html = $templates[$template]->html;
	    
	    if (strlen ($templates[$template]->from) > 0)
	      $this->from = $templates[$template]->from;
	      
	    if (strlen ($templates[$template]->subject) > 0)
	      $this->subject = $templates[$template]->subject;
	  }
	  else
	  {
	    // Use defaults
	    $this->text = '$contents$';
	    $this->html = '$contents$';
	  }
	}
	
	function add_text_row ($field, $value, $biggest)
	{
		return $field.str_repeat (' ', max (1, $biggest - strlen ($field))).': '.$value."\r\n";
	}
	
	function add_html_row ($field, $value)
	{
		// We add a <br/> because of the OS X Eudora client
		return '<tr><th align="right" style="padding: 0 3px">'.$field.':</th><td>'.htmlspecialchars ($value)."<br/></td></tr>\r\n";
	}

	function fill_in_details ($source, $text, $encode = false)
	{
		assert (is_a ($source, 'FI_Data'));
		
		$server = $source->get_source ('server');
		$post   = $source->get_source ('post');
		$cookie = $source->get_source ('cookies');
		$upload = $source->get_source ('files');
		
		// Replace server details
		$text = str_replace ('$from$',   $this->from, $text);
		$text = str_replace ('$remote$', $server->remote_host, $text);
		$text = str_replace ('$agent$',  $server->user_agent, $text);
		
		// Replace fields
		$text = $post->replace ($text, $encode);
		$text = $cookie->replace ($text, $encode);

		// Attach any file uploads
		if (count ($upload->data) > 0 && is_array ($upload->data))
		{
			foreach ($upload->data AS $field => $upload)
			{
				// Is this in the email?
				if (strpos ($text, '$'.$field.'$') !== false)
				{
					$this->attachments = array_merge ($this->attachments, $upload);
					$text = str_replace ('$'.$field.'$', '', $text);
				}
			}
		}
		
		return $text;
	}
	
	function html_content ($source)
	{
		assert (is_a ($source, 'FI_Data'));

		$post   = $source->get_source ('post');
		$server = $source->get_source ('server');
		$cookie = $source->get_source ('cookies');
		
		$body = '<table cellpadding="4" cellspacing="1">'."\r\n";
		$body .= '<colgroup span="1" style="background-color: #DFE3E4"/>'."\r\n";
		$body .= $this->add_html_row ('Remote host', $server->remote_host);
		$body .= $this->add_html_row ('Browser',     $server->user_agent);

		if (count ($post->data) > 0)
		{
			foreach ($post->data AS $key => $field)
		    $body .= $this->add_html_row ($key, $post->display ($key, false));
		}
		
		if (count ($cookie->data) > 0 && is_array ($cookie->data))
		{
			foreach ($cookie->data AS $key => $field)
		    $body .= $this->add_html_row ($key, $field);
		}
		
		$body .= "</table>";
		return preg_replace ('/(?:\r|\n|mime-version:|content-type:|cc:|to:)/is', '', $body);
	}
	
	function text_content ($source)
	{
		assert (is_a ($source, 'FI_Data'));
		
		$biggest = strlen ('Remote host: ');
		
		$post   = $source->get_source ('post');
		$server = $source->get_source ('server');
		$cookie = $source->get_source ('cookies');
		
		// First figure out the biggest field
		foreach (array ($post, $cookie) AS $datasource)
		{
			if (count ($datasource->data) > 0)
			{
				foreach ($datasource AS $key => $field)
				{
					if (strlen ($key) + 2 > $biggest)
						$biggest = strlen ($key) + 2;
				}
			}
		}

		$body  = '';
		$body .= $this->add_text_row ('Remote host', $server->remote_host, $biggest);
		$body .= $this->add_text_row ('Browser',     $server->user_agent, $biggest);

		if (count ($post->data) > 0 && is_array ($post->data))
		{
			foreach ($post->data AS $key => $field)
		    $body .= $this->add_text_row ($key, $post->display ($key, false), $biggest);
		}
		
		if (count ($cookie->data) > 0 && is_array ($cookie->data))
		{
			foreach ($cookie->data AS $key => $field)
		    $body .= $this->add_text_row ($key, $field, $biggest);
		}

		return preg_replace ('/(?:mime-version:|content-type:|cc:|to:)/is', '', $body);
	}
	
	function name ()
	{
		return __ ("Send as email (deprecated)", 'filled-in');
	}
	
	function edit ()
	{
	  $templates = get_option ('filled_in_templates');  

	  ?>
   <tr>
     <th width="50"><?php _e ('To', 'filled-in'); ?>:</th>
     <td><input style="width: 95%" type="text" name="address" value="<?php echo htmlspecialchars (isset($this->config['address']) ? $this->config['address'] : '') ?>"/></td>
   </tr>
	<tr>
		<th><?php _e ('Template', 'filled-in'); ?>:</th>
		<td>
			<select name="template">
			  <option value="default"><?php _e ('Default', 'filled-in'); ?></option>
			<?php if (count ($templates) > 0) : ?>
			  <?php foreach ($templates AS $temp) : ?>
			  <option value="<?php echo $temp->name ?>" <?php if ($temp->name == $this->config['template'] ? $this->config['template'] : '') echo ' selected="selected"' ?>><?php echo $temp->name ?></option>
			  <?php endforeach; ?>
			<?php endif; ?>
			</select>
		</td>
	</tr>
	  <?php
	}
	
	function show ()
	{
		parent::show ();
		$to = htmlspecialchars (isset($this->config['address']) ? $this->config['address'] : '');
		if ($to == '')
			$to = __ ('<em>&lt;not configured&gt;</em>');

		$template = htmlspecialchars (isset($this->config['template']) ? $this->config['template'] : '');
		if ($template == '')
			$template = __ ('<em>&lt;not configured&gt;</em>');
			
		printf (__ (' to <strong>%s</strong>, with template \'%s\'', 'filled-in'), $to, $template);
	}
	
	function save ($config)
	{
		return array ('address' => $config['address'], 'template' => $config['template']);
  }

	function is_editable () { return true;}
}

$this->register ('Post_Email');
?>