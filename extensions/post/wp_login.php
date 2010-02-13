<?php

class WordPress_Login extends FI_Post
{
	function process (&$source)
	{
		// Get the field we are using
		$field_pass     = $this->config['password'];
		$field_login    = $this->config['username'];
		$field_remember = $this->config['remember'];

		$user_login   = '';
		$user_pass    = '';
		$using_cookie = false;

		$data = $source->get_source ('post');
		$user_login = $data->data[$field_login];
		$user_login = sanitize_user( $user_login );
		$user_pass  = $data->data[$field_pass];

		$rememberme = isset ($data->data[$field_remember]) ? true : false;
		if ($field_remember == '')
			$rememberme = false;

		do_action_ref_array('wp_authenticate', array(&$user_login, &$user_pass));

		if ( $user_login && $user_pass )
		{
			global $user;
			$user = new WP_User(0, $user_login);

			if ( wp_login($user_login, $user_pass, $using_cookie) )
			{
				do_action ('wp_login', $user_login);
				wp_setcookie($user_login, $user_pass, false, '', '', $rememberme);
				return true;
			}
		}
		
		return __ ("Incorrect credentials during login", 'filled-in');
	}

	function name ()
	{
		return __ ("WordPress Login", 'filled-in');
	}
	
	function edit ()
	{
		?>
    <tr>
      <td width="130"><?php _e ('Field for username', 'filled-in') ?>:</td>
      <td><input style="width: 95%" type="text" name="username" value="<?php echo htmlspecialchars ($this->config['username']) ?>"/></td>
    </tr>
		<tr>
			<td width="130"><?php _e ('Field for password', 'filled-in') ?>:</td>
      <td><input style="width: 95%" type="text" name="password" value="<?php echo htmlspecialchars ($this->config['password']) ?>"/></td>
		</tr>
		<tr>
			<td width="130"><?php _e ('Field for \'remember me\'', 'filled-in') ?>:</td>
      <td><input style="width: 95%" type="text" name="remember" value="<?php echo htmlspecialchars ($this->config['remember']) ?>"/></td>
		</tr>
	  <?php
	}
	
	function show ()
	{
		parent::show ();
		$username = $this->config['username'];
		$password = $this->config['password'];
		
		if ($password == '')
			$password    = __ ('<em>&lt;not configured&gt;</em>', 'filled-in');
			
		if ($username == '')
			$username = __ ('<em>&lt;not configured&gt;</em>', 'filled-in');

		printf (__ (" with field '<strong>%s</strong>' for password, and '<strong>%s</strong>' for username", 'filled-in'), $password, $username);
	}
	
	function save ($config)
	{
		return array ('username' => $config['username'], 'password' => $config['password'], 'remember' => $config['remember']);
  }

	function is_editable () { return true;}
}

$this->register ('WordPress_Login');
?>