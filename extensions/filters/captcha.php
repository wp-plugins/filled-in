<?php

class Filter_CAPTCHA extends FI_Filter
{
	// Config
	function filter (&$value, $all_data)
	{
		session_start ();

		$word_ok = false;
		if (!empty ($_SESSION['freecap_word_hash']) && !empty ($value))
		{
			if ($_SESSION['hash_func'](strtolower ($value)) == $_SESSION['freecap_word_hash'])
			{
				$_SESSION['freecap_attempts'] = 0;
				$_SESSION['freecap_word_hash'] = false;

				$word_ok = true;
			}
		}
		
		if ($word_ok == true)
			return true;
		return __ ('Incorrect CAPTCHA result', 'filled-in');
	}
	
	function name ()
	{
		return __ ("Is CAPTCHA", 'filled-in');
	}
	
	// Too many attempts warning
	function save ($config)
	{
		$arr = array
		(
			'attempts'   => intval ($config['attempts']),
			'warning'    => $config['warning'],
			'words'      => $config['words'],
			'length'     => intval ($config['length']),
			'imagetype'  => $config['imagetype'],
			'background' => $config['background'],
			'toomany'    => $config['toomany'],
			'blur'       => isset ($config['blur']) ? 'true' : 'false'
		);
		
		if (strlen ($arr['toomany']) == 0)
			$arr['toomany'] = __ ('Too many attempts, consider glasses', 'filled-in');
			
		if ($arr['attempts'] == 0)
			$arr['attempts'] = 10;
			
		if ($arr['length'] == 0 || $arr['length'] > 20)
			$arr['length'] = 6;
		return $arr;
	}
	
	function edit ()
	{
		parent::edit ();
	?>
	<tr>
		<th><?php _e ('Max attempts', 'filled-in'); ?>:
		</th>
		<td>
			<input type="text" name="attempts" size="4" value="<?php echo $this->config['attempts'] ?>"/>
			<span class="sub"><?php _e ('Number of attempts before being blocked', 'filled-in'); ?></span>
		</td>
	</tr>
	<tr>
		<th><?php _e ('Blocked message', 'filled-in'); ?>:</th>
		<td>
			<input type="text" size="40" name="toomany" value="<?php echo htmlspecialchars ($this->config['toomany']) ?>" id="toomany"/>
			<span class="sub"><?php _e ('What to display when blocked', 'filled-in'); ?></span>
		</td>
	</tr>
	<tr>
		<th><?php _e ('Warning', 'filled-in'); ?>:<br/>
			<span class="sub"><?php _e ('Overlaid warning', 'filled-in'); ?></span>
		</th>
		<td>
			<input type="text" style="width: 95%" name="warning" value="<?php echo $this->config['warning'] ?>"/>
		</td>
	</tr>

	<tr>
		<th><?php _e ('Word generation', 'filled-in'); ?>:</th>
		<td>
			<select name="words">
				<option value="dict"<?php if ($this->config['words'] == 'dict') echo ' selected="selected"' ?>>Dictionary</option>
				<option value="random"<?php if ($this->config['words'] == 'random') echo ' selected="selected"' ?>>Random</option>
			</select>
			
			<?php _e ('Length', 'filled-in'); ?>: <input type="text" size="4" name="length" value="<?php echo $this->config['length'] ?>" id="length"/>
		</td>
	</tr>
	<tr>
		<th><?php _e ('Image type', 'filled-in'); ?>:</th>
		<td>
			<select name="imagetype">
				<option value="png"<?php if ($this->config['imagetype'] == 'png') echo ' selected="selected"' ?>>PNG</option>
				<option value="jpeg"<?php if ($this->config['imagetype'] == 'jpeg') echo ' selected="selected"' ?>>JPEG</option>
				<option value="gif"<?php if ($this->config['imagetype'] == 'gif') echo ' selected="selected"' ?>>GIF</option>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php _e ('Background', 'filled-in'); ?>:</th>
		<td>
			<select name="background">
				<option value="0"<?php if ($this->config['background'] == '0') echo ' selected="selected"' ?>>Transparent</option>
				<option value="1"<?php if ($this->config['background'] == '1') echo ' selected="selected"' ?>>White with grid</option>
				<option value="2"<?php if ($this->config['background'] == '2') echo ' selected="selected"' ?>>White with squiggles</option>
				<option value="3"<?php if ($this->config['background'] == '3') echo ' selected="selected"' ?>>Morphed blocks</option>
			</select>
			
			<label for="blur"><?php _e ('Blurred', 'filled-in'); ?>:</label>
			<input type="checkbox" name="blur" id="blur"<?php if ($this->config['blur'] == 'true') echo ' checked="checked"' ?>/>
		</td>
	</tr>
	<?php
	}
	
	function show ()
	{
		parent::show ();
		_e ('is a <strong>CAPTCHA</strong>', 'filled-in');
	}
	
	function modify ($text)
	{
		session_start ();
		
		$_SESSION['freecap_config'] = $this->config;
		
		$url = '<div class="form_captcha"><img src="'.get_bloginfo ('wpurl').'/wp-content/plugins/filled-in/lib/freecap/freecap.php'.'" alt="captcha"/></div>';
		return preg_replace ('@<input(.*?)name="'.$this->name.'"(.*?)/>@', $url.'<br/><input$1name="'.$this->name.'"$2/>', $text);
	}
}

$this->register ('Filter_CAPTCHA');