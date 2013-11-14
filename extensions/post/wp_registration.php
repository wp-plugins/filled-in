<?php

class WordPress_Registration extends FI_Post
{
	function process (&$source)
	{
		require_once (ABSPATH . WPINC . '/registration-functions.php');

		if (!get_settings ('users_can_register'))
			return __ ('Registration is disabled', 'filled-in');
		
		$data =& $source->get_source ('post');
		
		// Get the field we are using
		$field_login = $this->config['username'];
		$field_email = $this->config['email'];
		$field_url   = $this->config['url'];
		
		// Now get the value
		$user_login = $data->data[$field_login];
		$user_email = $data->data[$field_email];
		$user_url   = $data->data[$field_url];
		
		// Copy the code from wp-register.php
		$user_login = sanitize_user ($user_login);

		$errors = array ();
		if ( $user_login == '' )
			$errors = __('Please enter a username.', 'filled-in');

		/* checking e-mail address */
		if ($user_email == '')
			$errors = __('Please type your e-mail address.', 'filled-in');
		else if (!is_email($user_email))
		{
			$errors = __('The email address isn&#8217;t correct.', 'filled-in');
			$user_email = '';
		}

		if ( ! validate_username($user_login) )
		{
			$errors = __('This username is invalid.  Please enter a valid username.', 'filled-in');
			$user_login = '';
		}

		if ( username_exists( $user_login ) )
		{
			$errors = __('This username is already registered, please choose another one.', 'filled-in');
			if ($this->config['duplicates'] == 'true')
				return true;
		}

		/* checking the email isn't already used by another user */
		global $wpdb;
		$email_exists = $wpdb->get_row("SELECT user_email FROM $wpdb->users WHERE user_email = '$user_email'");
		if ( $email_exists)
		{
			$errors = __('This email address is already registered, please supply another.', 'filled-in');
			if ($this->config['duplicates'] == 'true')
				return true;
		}

		if ( 0 == count($errors) )
		{
			$password = substr( md5 (uniqid( microtime() )), 0, 7);
			$first = '';
			$last  = '';
			
			$user = array ('user_login' => $user_login, 'user_pass' => $password, 'user_email' => $user_email);
			
			if (strlen ($user_url) > 0)
				$user['user_url'] = $user_url;
				
			if (strlen ($this->config['name_first']) > 0)
			{
				$first = $data->data[$this->config['name_first']];
				$user['first_name'] = $first;
			}
				
			if (strlen ($this->config['name_last']) > 0)
			{
				$last = $data->data[$this->config['name_last']];
				$user['last_name'] = $last;
			}
			
			if (strlen ($this->config['name_full']) > 0)
			{
				$parts = explode (' ', $data->data[$this->config['name_full']]);
				$parts = array_filter ($parts);
				
				$user['first_name'] = $parts[0];
				if (count ($parts) > 1)
					$user['last_name'] = $parts[count ($parts) - 1];
					
				$first = $user['first_name'];
				$last  = $user['last_name'];
			}
			
			if (strlen ($this->config['name_nick']) > 0)
			{
				$user['user_nickname'] = $data->data[$this->config['name_nick']];
				$user['user_nicename'] = $user_login;
				$user['display_name']  = $user['user_nickname'];
			}
			else
			{
				$user['user_nickname'] = "$first $last";
				$user['user_nicename'] = $user_login;
				$user['display_name']  = $user['user_nickname'];
			}

			$user_id = wp_insert_user($user);
			if ( !$user_id )
				$errors = sprintf(__('Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !', 'filled-in'), get_settings('admin_email'));
			else
			{
				// Send out notification
				if (strlen ($this->config['email_template']) > 0 && $this->config['email_template'] != 'default')
				{
					$email = new Post_Email (array ());
					$email->config['address']  = $user_email;
					$email->config['template'] = $this->config['email_template'];
					
					$data->data['password'] = $password;
					$email->process ($source);
					
					unset ($data->data['password']);
					
					$message  = sprintf(__('New user registration on your blog %s:'), get_option('blogname')) . "\r\n\r\n";
					$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
					$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

					@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);
				}
				else
					wp_new_user_notification ($user_id, $password);

				// Make a note of the user id
				global $filled_in;
				$filled_in->shared['user_id']  = $user_id;
				$filled_in->shared['password'] = $password;
				return true;
			}
		}
		
		// Need an ignore errors
		return $errors;
	}

	function name ()
	{
		return __ ("WordPress Registration", 'filled-in');
	}
	
	function edit ()
	{
		$templates = get_option ('filled_in_templates');  
		?>
    <tr>
      <td width="130"><?php _e ('Field for username', 'filled-in') ?>:</td>
      <td><input style="width: 95%" type="text" name="username" value="<?php echo htmlspecialchars ($this->config['username']) ?>"/></td>
    </tr>
		<tr>
			<td width="130"><?php _e ('Field for email', 'filled-in') ?>:</td>
      <td><input style="width: 95%" type="text" name="email" value="<?php echo htmlspecialchars ($this->config['email']) ?>"/></td>
		</tr>
		<tr>
			<td width="130"><?php _e ('Field for first name', 'filled-in') ?>:<br/><span class="sub">Optional</span></td>
      <td><input style="width: 95%" type="text" name="name_first" value="<?php echo htmlspecialchars ($this->config['name_first']) ?>"/></td>
		</tr>
		<tr>
			<td width="130"><?php _e ('Field for last name', 'filled-in') ?>:<br/><span class="sub">Optional</span></td>
      <td><input style="width: 95%" type="text" name="name_last" value="<?php echo htmlspecialchars ($this->config['name_last']) ?>"/></td>
		</tr>
		<tr>
			<td width="130"><?php _e ('Field for full name', 'filled-in') ?>:<br/><span class="sub">Optional</span></td>
      <td><input style="width: 95%" type="text" name="name_full" value="<?php echo htmlspecialchars ($this->config['name_full']) ?>"/></td>
		</tr>
		<tr>
			<td width="130"><?php _e ('Field for nickname', 'filled-in') ?>:<br/><span class="sub">Optional</span></td>
      <td><input style="width: 95%" type="text" name="name_nick" value="<?php echo htmlspecialchars ($this->config['name_nick']) ?>"/></td>
		</tr>
		<tr>
			<td width="130"><?php _e ('Field for URL', 'filled-in') ?>:<br/><span class="sub">Optional</span></td>
      <td><input style="width: 95%" type="text" name="url" value="<?php echo htmlspecialchars ($this->config['url']) ?>"/></td>
		</tr>
		<tr>
			<td width="130"><label for="duplicates"><?php _e ('Ignore duplicates', 'filled-in') ?></label>:<br/><span class="sub">Don't report an error on duplicate user</span></td>
      <td valign="top"><input type="checkbox" name="duplicates" id="duplicates"<?php if ($this->config['duplicates'] == 'true') echo ' checked="checked"' ?>/></td>
		</tr>
		<tr>
			<td width="130"><?php _e ('User email notification', 'filled-in') ?>:<br/><span class="sub">Report password to user</span></td>
      <td valign="top">
      	<select name="email_template">
				  <option value="default"><?php _e ('WordPress Default', 'filled-in'); ?></option>
				<?php if (count ($templates) > 0) : ?>
				  <?php foreach ($templates AS $temp) : ?>
				  <option value="<?php echo $temp->name ?>" <?php if ($temp->name == $this->config['email_template']) echo ' selected="selected"' ?>><?php echo $temp->name ?></option>
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
		$username = $this->config['username'];
		$email    = $this->config['email'];
		
		if ($email == '')
			$email    = __ ('<em>&lt;not configured&gt;</em>', 'filled-in');
			
		if ($username == '')
			$username = __ ('<em>&lt;not configured&gt;</em>', 'filled-in');
	  
		printf (__ (" with field '<strong>%s</strong>' for email, and '<strong>%s</strong>' for username", 'filled-in'), $email, $username);
	}
	
	function save ($config)
	{
	  return array
		(
			'username'       => $config['username'],
			'email'          => $config['email'],
			'name_first'     => $config['name_first'],
			'name_last'      => $config['name_last'],
			'name_full'      => $config['name_full'],
			'name_nick'      => $config['name_nick'],
			'url'            => $config['url'],
			'duplicates'     => isset ($config['duplicates']) ? 'true' : 'false',
			'email_template' => $config['email_template']
		);
  }

	function is_editable () { return true;}
}

$this->register ('WordPress_Registration');
?>