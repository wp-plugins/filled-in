<?php

class FI_Upload
{
	var $id;
	var $name;
	var $stored_location;
	
	function FI_Upload ($values)
	{
		$this->id              = $values['id'];
		$this->name            = basename ($values['name']);
		$this->stored_location = $values['tmp_name'];
		$this->name            = preg_replace ('@[\~\#\$\*\\\?/]@', '', $this->name);
	}
	
	function unique_name ($dest)
	{
		// Create a unique version of the file
		$pos    = 1;
		$target = $dest;
		while (file_exists ($target))
		{
			$parts  = pathinfo ($dest);
			$name   = str_replace ('.'.$parts['extension'], '', $parts['basename']).".$pos.".$parts['extension'];
			$target = dirname ($dest).'/'.$name;
			$pos++;
		}
		
		return $target;
	}
	
	function move_to_unique ($dest)
	{
		$target = $this->unique_name ($dest);

		wp_mkdir_p (dirname ($target));  // Ensure directory exist
		if (@rename ($this->stored_location, $target) === false)
			return false;
			
		chmod ($target, 0777);		
		$this->stored_location = $target;
		return true;	
	}
	
	function move_to ($dest)
	{
		assert (is_string ($dest));

		wp_mkdir_p (dirname ($dest));
		
		if ((isset ($_SERVER['REQUEST_METHOD']) && @move_uploaded_file ($this->stored_location, $dest) !== false) || (!isset ($_SERVER['REQUEST_METHOD']) && @rename ($this->stored_location, $dest) !== false))
		{
			$this->stored_location = $dest;
			@chmod ($dest, 0777);
			return true;
		}
		return false;
	}
	
	function delete ()
	{
		return @unlink ($this->stored_location);
	}
	
	function size ()
	{
		return @filesize ($this->stored_location);
	}
	
	function name_to_lower ()
	{
		$this->name = strtolower ($this->name);
	}
}

?>