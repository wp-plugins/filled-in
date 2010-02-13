<?php

class Add_To_Profile extends FI_Post
{
	function process (&$source)
	{
		$post =& $source->get_source ('post');

		if ($this->config['logic_field'] != '' && (empty ($post->data[$this->config['logic_field']]) || $post->data[$this->config['logic_field']] == 'no'))
			return true;   // No need to carry on
		
		// Form data
		$parts = explode (',', $this->config['fields']);
		$parts = array_unique ($parts);

		$data = array ();
		foreach ($parts AS $field)
			$data[$field] = $post->data[$field];

		if (count ($data) == 1)
			$data = array_pop ($data);

		$user = wp_get_current_user ();
		if (empty ($user) || $user == '' || $user->ID == 0)
		{
			global $filled_in;
			$user->ID = $filled_in->shared['user_id'];
		}
			
		if ($user->ID > 0)
		{
			$meta = get_usermeta ($user->ID, $this->config['data_key']);

			if ($this->config['add_method'] == 'append_add')
			{
				if (is_array ($meta) && isset ($meta[0]))
				{
					$meta[] = $data;
					$data   = $meta;
				}
				else if ($meta != '')
					$data = array ($meta, $data);
			}
			else if ($this->config['add_method'] == 'append_key')
			{
				// Make a unique key for this data
				$meta[$this->replace_fields ($post->data, $this->config['append_key'])] = $data;
				$data = $meta;
			}
			else if ($this->config['add_method'] == 'merge')
			{
				if (is_array ($meta))
				{
					// Add fields from old meta that don't exist in new data
					foreach ($meta AS $key => $value)
					{
						if (!isset ($data[$key]))
							$data[$key] = $value;
					}
				}
			}
		
			// Add to profile
			update_usermeta ($user->ID, $this->config['data_key'], $data);
		}

		return true;
	}

	function name ()
	{
		return __ ("Add to WordPress profile", 'filled-in');
	}
	
	function edit ()
	{
		?>
    <tr>
      <td width="130"><?php _e ('Data key', 'filled-in') ?>:<br/><span class="sub"><?php _e ('Name of the profile data', 'filled-in'); ?></span></td>
      <td><input style="width: 95%" type="text" name="data_key" value="<?php echo htmlspecialchars ($this->config['data_key']) ?>"/></td>
    </tr>
		<tr>
			<td width="130"><?php _e ('Fields', 'filled-in') ?>:<br/><span class="sub"><?php _e ('Comma-separated list of fields to add to profile', 'filled-in'); ?></span></td>
      <td><input style="width: 95%" type="text" name="fields" value="<?php echo htmlspecialchars ($this->config['fields']) ?>"/></td>
		</tr>
		<tr>
			<td width="130"><?php _e ('Add method', 'filled-in') ?>:<br/><span class="sub"><?php _e ('How to add the data', 'filled-in'); ?></span></td>
      <td>
				<select name="add_method">
      	<option value="append_add"<?php if ($this->config['add_method'] == 'append_add') echo ' selected="selected"'; ?>>Append to end of list (like a log)</option>
				<option value="append_key"<?php if ($this->config['add_method'] == 'append_key') echo ' selected="selected"'; ?>>Append/merge to list (identified by a key)</option>
				<option value="merge"<?php if ($this->config['add_method'] == 'merge') echo ' selected="selected"'; ?>>Merge values with existing</option>
				<option value="replace"<?php if ($this->config['add_method'] == 'replace') echo ' selected="selected"'; ?>>Replace, erasing existing values</option>
				</select>
      </td>
		</tr>
		<tr>
			<td width="130"><?php _e ('Append key', 'filled-in') ?>:<br/><span class="sub"><?php _e ('Key for append/merge method', 'filled-in'); ?></span></td>
      <td><input style="width: 95%" type="text" name="append_key" value="<?php echo htmlspecialchars ($this->config['append_key']) ?>"/></td>
		</tr>
		<tr>
			<td width="130"><?php _e ('Logic field', 'filled-in') ?>:<br/><span class="sub"><?php _e ('Only add if this field is present', 'filled-in'); ?></span></td>
      <td><input style="width: 95%" type="text" name="logic_field" value="<?php echo htmlspecialchars ($this->config['logic_field']) ?>"/></td>
		</tr>
	  <?php
	}
	
	function show ()
	{
		parent::show ();
		$key    = $this->config['data_key'];
		$fields = $this->config['fields'];
		
		if ($key == '')
			$key    = __ ('<em>&lt;not configured&gt;</em>', 'filled-in');
			
		if ($fields == '')
			$fields = __ ('<em>&lt;not configured&gt;</em>', 'filled-in');
	  
		printf (__ (" with key '<strong>%s</strong>', and fields '<strong>%s</strong>'", 'filled-in'), $key, $fields);
	}
	
	function save ($config)
	{
	  return array ('data_key' => $config['data_key'], 'fields' => $config['fields'], 'add_method' => $config['add_method'], 'append_key' => $config['append_key'], 'logic_field' => $config['logic_field']);
  }

	function is_editable () { return true;}
}

$this->register ('Add_To_Profile');
?>