<?php

class WordPress_Lost_Password extends FI_Post
{
	function process (&$source)
	{
		// Get the field we are using
		$field_email    = $this->config['email'];
		$field_login    = $this->config['username'];

		$data = $source->get_source ('post');
		$user_login = $data->data[$field_login];
		$user_login = sanitize_user( $user_login );
		$user_email = $data->data[$field_email];

		do_action('lostpassword_post');

		$user_data = get_userdatabylogin ($user_login);
		
		// redefining user_login ensures we return the right case in the email
		$my_user_login = $user_data->user_login;
		$my_user_email = $user_data->user_email;

		if (!$my_user_email || $my_user_email != $user_email)
			return __('invalid username / e-mail combination.', 'filled-in');
		else
		{
			global $wpdb;
			
			do_action ('retrieve_password', $my_user_login);
		
			// Generate something random for a password... md5'ing current time with a rand salt
			$key = substr( md5( uniqid( microtime() ) ), 0, 8);
			
			// Now insert the new pass md5'd into the db
			$wpdb->query("UPDATE $wpdb->users SET user_activation_key = '$key' WHERE user_login = '$my_user_login'");
			
			$message = __('Someone has asked to reset the password for the following site and username.') . "\r\n\r\n";
			$message .= get_option('home') . "\r\n\r\n";
			$message .= sprintf(__('Username: %s'), $my_user_login) . "\r\n\r\n";
			$message .= __('To reset your password visit the following address, otherwise just ignore this email and nothing will happen.') . "\r\n\r\n";
			$message .= get_option('siteurl') . "/wp-login.php?action=rp&key=$key\r\n";
		
			if (FALSE == wp_mail($my_user_email, sprintf(__('[%s] Password Reset'), get_option('blogname')), $message))
				return __('the e-mail could not be sent.');
			return true;
		}
		
		return __ ("incorrect credentials during login", 'filled-in');
	}

	function name ()
	{
		return __ ("WordPress Lost Password", 'filled-in');
	}
	
	function edit ()
	{
		?>
    <tr>
      <td width="130"><?php _e ('Field for username', 'filled-in') ?>:</td>
      <td><input style="width: 95%" type="text" name="username" value="<?php echo htmlspecialchars ($this->config['username']) ?>"/></td>
    </tr>
		<tr>
			<td width="130"><?php _e ('Field for email', 'filled-in') ?>:</td>
      <td><input style="width: 95%" type="text" name="email" value="<?php echo htmlspecialchars ($this->config['email']) ?>"/></td>
		</tr>
	  <?php
	}
	
	function show ()
	{
		parent::show ();
		$username = $this->config['username'];
		$email = $this->config['email'];
		
		if ($email == '')
			$email    = __ ('<em>&lt;not configured&gt;</em>', 'filled-in');
			
		if ($username == '')
			$username = __ ('<em>&lt;not configured&gt;</em>', 'filled-in');

		printf (__ (" with field '<strong>%s</strong>' for email, and '<strong>%s</strong>' for username", 'filled-in'), $email, $username);
	}
	
	function save ($config)
	{
		return array ('username' => $config['username'], 'email' => $config['email']);
  }

	function is_editable () { return true;}
}

$this->register ('WordPress_Lost_Password');
?>