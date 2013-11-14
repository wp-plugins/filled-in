<?php

class PostToPommo extends FI_Post
{
	function process (&$source)
	{
		if (!defined ('_poMMo_embed'))
			define('_poMMo_embed', TRUE);
			
		if (file_exists ($this->config['pommo_dir'].'/bootstrap.php'))
		{
			require_once ($this->config['pommo_dir'].'/bootstrap.php');
			
			$pommo->init(array('authLevel' => 0, 'noSession' => TRUE));
			
			Pommo::requireOnce ($pommo->_baseDir.'inc/helpers/validate.php');
			Pommo::requireOnce ($pommo->_baseDir.'inc/helpers/subscribers.php');

			$data   = $source->get_source ('post');
			$server = $source->get_source ("server");

			// Form data
			$fields = preg_split ('/[\n\r]+/', $this->config['fields']);
			$extra   = array ();
			if (count ($fields) > 0)
			{
				foreach ($fields AS $field)
				{
					$parts = explode ('=', $field);
					if (count ($parts) == 2 && isset ($data->data[trim ($parts[0])]))
						$extra[trim ($parts[1])] = $data->data[trim ($parts[0])];
				}
			}
					
			// Fill out the data for poMMo
			$subscriber = array
			(
				'email'      => $data->data[$this->config['email']],
				'registered' => time (),
				'ip'         => $server->ip,
				'status'     => 1,
				'data'       => $extra,
			);
			
			if ($this->config['confirm'] == 'true')
			{
				Pommo::requireOnce($pommo->_baseDir . 'inc/helpers/messages.php');
				
				$subscriber['pending_code'] = PommoHelper::makeCode();
				$subscriber['pending_type'] = 'add';
				$subscriber['status']       = 2;
			}
			
			$error = true;
			if (!PommoHelper::isEmail ($subscriber['email']) && strlen ($subscriber['email']) == 0)
				$error = __ ('poMMo is incorrectly configured (no email)', 'filled-in');
			else if (PommoHelper::isDupe ($subscriber['email']))
				return true;    // We silently ignore duplicates.  No point bothering the user
			else if (!PommoValidate::subscriberData ($subscriber['data'], array('active' => FALSE)))
				$error = __ ('poMMo is incorrectly configured (bad data)', 'filled-in');
			else
				$id = PommoSubscriber::add ($subscriber);
				
			if ($error == true && $id === false)
				$error = __ ('poMMo failed to register', 'filled-in');
			else if ($this->config['confirm'] == 'true')
			{
				if (!PommoHelperMessages::sendConfirmation($subscriber['email'], $subscriber['pending_code'], 'subscribe'))
				{
					PommoSubscriber::delete($id);
					$error = __ ('poMMo failed to send confirmation message', 'filled-in');
				}
			}
				
			ob_end_flush();
			return $error;
		}
		else
			return __ ('poMMo could not be found', 'filled-in');
	}

	function name ()
	{
		return __ ("Add to poMMo mailing list", 'filled-in');
	}
	
	function edit ()
	{
		?>
	    <tr>
	      <td width="130"><?php _e ('poMMo directory', 'filled-in') ?>:</td>
	      <td>
					<input type="text" name="pommo_dir" value="<?php echo htmlspecialchars ($this->config['pommo_dir']) ?>"/>
					<span class="sub"><?php _e ('Full directory to the poMMo installation', 'filled-in'); ?></span>
				</td>
	    </tr>
	    <tr>
	      <td width="130"><?php _e ('Email field', 'filled-in') ?>:</td>
	      <td>
					<input type="text" name="email" value="<?php echo htmlspecialchars ($this->config['email']) ?>"/>
					<span class="sub"><?php _e ('Name of the Filled In field you want to use as the email address', 'filled-in'); ?></span>
				</td>
	    </tr>
			<tr>
				<td width="130" valign="top">
					<?php _e ('Data fields', 'filled-in') ?>:<br/>
					<span class="sub"><?php _e ('One field per line as FIELD=[poMMo field ID] (i.e. name=1)', 'filled-in'); ?></span>
				</td>
	      <td>
					<textarea name="fields" rows="5" cols="40"><?php echo $this->config['fields'] ?></textarea>
				</td>
			</tr>
	    <tr>
	      <td width="130"><?php _e ('Confirmation required', 'filled-in') ?>:</td>
	      <td>
					<input type="checkbox" name="confirm" <?php if ($this->config['confirm'] == 'true') echo ' checked="checked"'; ?>/>
				</td>
	    </tr>
	  <?php
	}
	
	function show ()
	{
		parent::show ();
		
		$email = $this->config['email'];
		if ($email == '')
			$email = __ ('<em>&lt;not configured&gt;</em>', 'filled-in');
		
		$fields = preg_split ('/[\n\r]+/', $this->config['fields']);
		if ($this->config['fields'] != '' && count ($fields) > 0)
			$fields = "and data fields '".implode ("', '", $fields)."'";
		else
			$fields = '';
?>
 with email taken from '<?php echo $email ?>' <?php echo $fields?>
<?php
	}
	
	function save ($config)
	{
		return array
		(
			'pommo_dir' => rtrim ($config['pommo_dir'], '/'),
			'email'     => $config['email'],
			'fields'    => $config['fields'],
			'confirm'   => isset ($config['confirm']) ? 'true' : 'false'
		);
  }

	function is_editable () { return true;}
}

$this->register ('PostToPommo');

?>