<?php

class Form_Replacer extends Filled_In_Plugin
{
	var $field    = null;
	var $value    = null;
	var $error    = false;
	var $modified = false;
	
	function Form_Replacer ($field, $value, $error)
	{
		$this->register_plugin ('filled-in', dirname (__FILE__));

		$this->field          = $field;
		$this->value          = $value;
		$this->error          = $error;
	}
	
	function replace_input ($matches)
	{
		// First modify the classes
		$other = $this->modify_class (trim (trim ($matches[1]).' '.trim ($matches[3])));

		preg_match ('/type=["\'](\w+)["\']/', $other, $types);
		$type = $types[1];

		if ($type == 'text' || $type == 'password' || $type == 'hidden')
			$txt = $this->replace_input_text ($other, $matches[2]);
		else if ($type == 'radio')
			$txt = $this->replace_input_radio ($other, $matches[2]);
		else if ($type == 'checkbox')
			$txt = $this->replace_input_checkbox ($other, $matches[2]);
		else
			$txt = '<input '.trim ($other).' name="'.$this->field.$matches[2].'"/>';

		return $txt;
	}
	
	function replace_file ($matches)
	{
		$other = $this->modify_class (trim (trim ($matches[1]).' '.trim ($matches[3])));
		return '<input type="file" name="'.$this->field.$matches[2].'"'.$other.'/>';
	}
	
	function replace_input_text ($contents, $multi)
	{
		// Remove any existing value
		$contents = preg_replace ('/\s*value=["\'](.*?)["\']/', '', $contents);
		return '<input '.trim ($contents).' name="'.$this->field.$multi.'" value="'.htmlspecialchars ($this->value).'"/>';
	}
	
	function replace_input_checkbox ($contents, $multi)
	{
		$contents = trim (preg_replace ('/\s*checked="checked"/', '', $contents));

		if (is_array ($this->value))
		{
			foreach ($this->value AS $item)
			{
				if (strpos ($contents, "value=\"$item\""))
				{
					$contents .= ' checked="checked"';
					break;
				}
			}
		}
		else if ($this->value != '' && $this->value != 'no')
			$contents .= ' checked="checked"';
			
		$contents = trim ($contents);
		$str = "<input $contents name=\"{$this->field}$multi\"/>";
		if ($this->error)
			return "<span class=\"form_wrap\">$str</span>";
		return $str;
	}
	
	function replace_input_radio ($contents, $multi)
	{
		$contents = trim (str_replace ('checked="checked"', '', $contents));
		preg_match ('/value=["\'](.*?)["\']/', $contents, $values);

		if ($values[1] == $this->value)
			$contents .= ' checked="checked"';
			
		$str = "<input $contents name=\"{$this->field}$multi\"/>";
		if ($this->error)
			return "<span class=\"form_wrap\">$str</span>";
		return $str;
	}
	
	function replace_select ($matches)
	{
		assert (is_array ($matches));

		// First modify the classes
		$other = $this->modify_class (trim (trim ($matches[1]).' '.trim ($matches[3])));

		// matches[4] is select innerHTML
		$matches[4] = preg_replace ('/\s*selected="selected"/', '', trim ($matches[4]));
		$matches[4] = str_replace ('selected', '', $matches[4]);    // For those bad kind of days
		
		if (is_array ($this->value))
			$rep = $this->value;
		else
			$rep = array ($this->value);
		
		foreach ($rep AS $item)
			$matches[4] = str_replace ('value="'.$item.'"', 'value="'.$item.'" selected="selected"', $matches[4]);
		
		$other = trim ($other);
		if ($other != '')
			$other .= ' ';
		return '<select '.$other.'name="'.$this->field.$matches[2].'">'."\r\n".$matches[4]."\r\n</select>";
	}
	
	function replace_textarea ($matches)
	{
		assert (is_array ($matches));
		
		// First modify the classes
		$other = $this->modify_class (trim ($matches[1]).' '.trim ($matches[2]));

		// matches[3] is value
		$matches[3] = htmlspecialchars ($this->value);
		return '<textarea '.trim ($other).' name="'.$this->field.'">'.$matches[3].'</textarea>';
	}
	
	function modify_class ($text)
	{
		if ($this->error)
		{
			$this->modified = true;
			preg_match ('/class="(.*?)"/', $text, $classes);
			$text = preg_replace ('/\s*class="(.*?)"/', '', $text);
			
			if ( isset( $classes[1]) )
				$text = 'class="form_error '.trim ($classes[1]).'" '.trim ($text);
			else
				$text = 'class="form_error" '.trim ($text);

			$text = str_replace ('form_error "', 'form_error"', $text);
			$text = trim ($text);
		}

		$text = str_replace ('  class=', ' class=', $text);  // Because I like neat
		return $text;
	}
	
	function is_modified () { return $this->modified; }
}


?>