<?php

class FI_Data_COOKIES extends FI_Data_Source
{
	function FI_Data_COOKIES ($data, $config = '')
	{
		if (is_array ($data))
		{
			assert (is_array ($config));

			// Remove unwanted cookies
			foreach ($config AS $allowed)
			{
				if (isset ($data[$allowed]))
					$this->data[$allowed] = $data[$allowed];
			}
		}
		else if (is_object ($data))
		{
			$this->data = unserialize ($data->cookie);
		}
	}
	
	/// Addition  2011/01/25,  1.7.7
	static function protect_attributes( $matches ) {
	  $matches[0] = str_replace( '%', '&#37;', $matches[0] );
	  return $matches[0];
	}
	/// End of addition  2011/01/25

	function replace ($text, $encode = false)
	{
		assert (is_string ($text));
		assert (is_bool ($encode));
		
		if (count ($this->data) > 0 && is_array ($this->data))
		{
    	foreach ($this->data as $key => $value)
      	$text = str_replace ("%$key%", $encode == true ? htmlspecialchars ($value) : $value, $text);
		}

		// Remove non-existant cookies
		/// Modification  2011/01/25, 1.7.7
    //return preg_replace ('/%(.*?)%/', '-', $text);
    $text = preg_replace_callback( '/(src|href)=\'.*?\'/', 'FI_Data_COOKIES::protect_attributes', $text );
    $text = preg_replace_callback( '/(src|href)=".*?"/', 'FI_Data_COOKIES::protect_attributes', $text );
    return preg_replace ('/%(.*?)%/', '-', $text);
    /// End of modification
	}

	function save ($dataid, $filters)
	{
		if (count ($this->data) > 0)
			return array ('cookie' => serialize ($this->data));
		return array ();
	}
	
	function what_group () { return 'cookies'; }
}


?>