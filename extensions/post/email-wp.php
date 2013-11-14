<?php

require_once (dirname (__FILE__).'/../../models/email_template.php');

class Post_Email_WP extends FI_Post
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

      $subject = $this->config['subject'];
      if( strlen(trim($subject)) == 0 ) {
         $this->subject = 'Submission to form: '.$post->form->name;
      } else {
         $this->subject = $subject;
      }

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

   function send ($from, $to, $subject, $template) {
      $this->from = $from;
      $this->subject = $subject;

      //add attachments
      $attach = new EmailAttachment( $template );
      $attachments = $attach->get();
      $attachments = array_merge( $attachments, $this->attachments );

      if( count( $attachments ) > 0 ){
         foreach( $attachments AS $upload ){
            $info = getimagesize( $upload->stored_location );

            if( $info[2] != 0 && preg_match( '/"(.*?)'.$upload->name.'"/', $this->html ) > 0 ){
               $this->html = preg_replace( '/"(.*?)'.$upload->name.'"/', '', $this->html );
            }

            $this->attachments[] = $upload->stored_location;
         }
      }

      //add headers
      $headers = "MIME-Version: 1.0\n" ;
      $headers .= "Content-Type: text/html; charset=UTF-8\n";
      $headers .= "From: " . $this->from . "\r\n";

      if(trim($this->html) == ''){
         $this->html = $this->text;
      }

      //content of mail
      $message = '<html><head><title>' . $this->subject . '</title></head><body>' . wpautop($this->html) . '</body></html>';

      //send it
      return wp_mail( $to, $this->subject, $message, $headers, $this->attachments );
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
         if( 'no' == $this->config['replyto'] || !trim( $this->config['replyto-email'] ) )
            $this->html = '$contents$';
         else
            $this->html = '<p>$replyto$</p>'."\n\n".'$contents$';
      }
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
      $text = $upload->replace( $text, $encode );
      $text = $post->replace ($text, $encode);
      $text = $cookie->replace ($text, $encode);

      if( 'no' == $this->config['replyto'] || !trim( $this->config['replyto-email'] ) )
         $text = str_replace( '$replyto$', '', $text );
      else{
         $strReplyTo = '<a href=\'mailto:' . $post->replace( $this->config['replyto-email'], false );

         $aReplyTo = array();
         if( trim( $this->config['replyto-email-bcc'] ) )
            $aReplyTo[] = 'bcc=' . $post->replace( $this->config['replyto-email-bcc'], false );
         if( trim( $this->config['replyto-email-content'] ) )
            $aReplyTo[] = 'body=' . rawurlencode( $post->replace( $this->config['replyto-email-content'], false ) );

         if( trim( $this->config['replyto-email-subject'] ) )
            $aReplyTo[] = 'subject=' . rawurlencode( $post->replace( $this->config['replyto-email-subject'], false ) );
         else
            $aReplyTo[] = 'subject=' . rawurlencode( 'Re: ' );

         $strReplyTo .= '?' . implode( '&', $aReplyTo ) . '\'>Click to reply</a>';

         $text = str_replace( '$replyto$', $strReplyTo, $text );
      }

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
		$body .= $this->add_html_row ('Remote host', htmlspecialchars ($server->remote_host));
		$body .= $this->add_html_row ('Browser',     htmlspecialchars ($server->user_agent));

		if (count ($post->data) > 0)
		{
			foreach ($post->data AS $key => $field)
		    $body .= $this->add_html_row( $key, $post->display( $key, false, true ) );
		}
		
		if (count ($cookie->data) > 0 && is_array ($cookie->data))
		{
			foreach ($cookie->data AS $key => $field)
		    $body .= $this->add_html_row( $key, htmlspecialchars( $field ) );
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
		return __ ("Send as email", 'filled-in');
	}

   function edit () {
      $templates = get_option ('filled_in_templates');  

      ?>

   <tr>
      <th width="150"><?php _e( 'To', 'filled-in' ); ?>:</th>
      <td><input style="width: 95%" type="text" name="address" value="<?php echo htmlspecialchars (isset($this->config['address']) ? $this->config['address'] : '') ?>"/><br />
      <small>(use </small>;<small> to separate multiple addresses; enter $field_name$ to use any of the form field)</small></td>
   </tr>

   <tr id="replyto-<?php echo $this->id; ?>">
      <th width="150"><?php _e( 'Use ReplyTo', 'filled-in' ); ?>:</th>
      <td>
         <input type="checkbox" name="replyto" value="yes" <?php if( 'yes' == $this->config['replyto'] ) echo 'checked="checked"'; ?> />
      </td>
   </tr>
   <tr class="replyto" valign="top">
      <th width="150"><?php _e( 'ReplyTo email', 'filled-in' ); ?>:</th>
      <td>
         <input style="width: 95%" type="text" name="replyto-email" value="<?php echo htmlspecialchars( isset( $this->config['replyto-email'] ) ? $this->config['replyto-email'] : '' ); ?>" /><br />
         <small>Use standard <strong>"name"&lt;email&gt;</strong> syntax to include name. You can use submitted fields.</small>
      </td>
   </tr>
   <tr class="replyto">
      <th width="150" valign="top"><?php _e( 'ReplyTo email BCC', 'filled-in' ); ?>:</th>
      <td>
         <input style="width: 95%" type="text" name="replyto-email-bcc" value="<?php echo htmlspecialchars( isset( $this->config['replyto-email-bcc'] ) ? $this->config['replyto-email-bcc'] : '' ); ?>" /><br />
         <small>Use standard <strong>"name"&lt;email&gt;</strong> syntax to include name. You can use submitted fields.</small>
      </td>
   </tr>
   <tr class="replyto" valign="top">
      <th width="150"><?php _e( 'ReplyTo email subject', 'filled-in' ); ?>:</th>
      <td>
         <input style="width: 95%" type="text" name="replyto-email-subject" value="<?php echo htmlspecialchars( isset( $this->config['replyto-email-subject'] ) ? $this->config['replyto-email-subject'] : '' ); ?>" /><br />
         <small>You can use submitted fields.</small>
      </td>
   </tr>
   <tr class="replyto" valign="top">
      <th width="150"><?php _e( 'ReplyTo email content', 'filled-in' ); ?>:</th>
      <td>
         <textarea style="width: 95%" name="replyto-email-content"><?php echo htmlspecialchars( isset( $this->config['replyto-email-content'] ) ? $this->config['replyto-email-content'] : '' ); ?></textarea><br />
         <small>You can use submitted fields.</small>
      </td>
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

   <tr>
      <th><?php _e ('Subject', 'filled-in'); ?>:</th>
      <td>
         <input style="width: 95%" type="text" name="subject" value="<?php echo htmlspecialchars (isset($this->config['subject']) ? $this->config['subject'] : '') ?>"/><br />
         <small>(works with default template - leave blank for default subject (form name); enter $field_name$ to use any of the form field)</small>
      </td>
   </tr>


   <script type="text/javascript">
      jQuery( "#replyto-<?php echo $this->id; ?>" ).change( function( evt ){
         jQuery( "#replyto-<?php echo $this->id; ?>" ).siblings( ".replyto" ).toggle();
      });

      if( 0 >= jQuery( "#replyto-<?php echo $this->id; ?> input:checked" ).length )
         jQuery( "#replyto-<?php echo $this->id; ?>" ).siblings( ".replyto" ).hide();
   </script>

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

      $subject = htmlspecialchars (strlen($this->config['subject']) ? 'and subject \''.$this->config['subject'].'\'' : '');	

      printf (__ (' to <strong>%s</strong>, with template \'%s\' %s', 'filled-in'), $to, $template, $subject);

      if( 'yes' == $this->config['replyto'] && trim( $this->config['replyto-email'] ) )
         echo '. Using a ReplyTo link to <em>'.$this->config['replyto-email'].'</em>.';
   }

   function save( $config ){
      return array(
         'address' => $config['address'],
         'template' => $config['template'],
         'subject' => $config['subject'],

         'replyto' => (isset( $config['replyto'] )) ? 'yes' : 'no',
         'replyto-email' => $config['replyto-email'],
         'replyto-email-bcc' => $config['replyto-email-bcc'],
         'replyto-email-subject' => $config['replyto-email-subject'],
         'replyto-email-content' => $config['replyto-email-content']
      );
   }

	function is_editable () { return true;}
}

$this->register ('Post_Email_WP');

