<?php

class EmailAttachment extends Filled_In_Plugin
{
	var $template;
	
	function EmailAttachment ($template)
	{
		assert ('strlen ($template) > 0');
		$this->register_plugin ('filled-in', dirname (__FILE__));
		$this->template = $template;
		$this->base     = get_option ('filled_in_attachments');
	}
	
	function sanitize ($name)
	{
		$name = basename ($name);   // sanitize to be filename safe, with no paths
		$name = preg_replace ('/[\*\?\^%\/\\~]/', '', $name);
		$name = preg_replace ('/\.{2,}/', '.', $name);
		return $name;
	}
	
	function upload ($upload, $name)
	{
	  umask (0);

		$newname = $this->sanitize ($name);
		$newname = $this->base.'/'.$this->template.'/';
		
		// Make sure directory exists
		wp_mkdir_p ($newname);
		
		// Copy to upload directory
		if (@rename ($upload, $newname.$name) && @chmod ($newname.$name, 0666))
			return true;
		return false;
	}
	
	function show ()
	{
		$files = $this->get ();
		if (count ($files) > 0)
			$this->render_admin ('email/attachments', array ('files' => $files, 'name' => $this->template));
	}
	
	function download ($file)
	{
		$file = $this->sanitize ($file);
		
		$newname = $this->base.'/'.$this->template.'/';
		$newname .= $file;

		if (file_exists ($newname))
		{
			header ('Content-Type: application/octet-stream');
			header ("Content-Disposition: attachment; filename=\"".$file."\";");
			header ('Content-Length: '.filesize ($newname));
			readfile ($newname);
		}
		else
			_e ("<p>I'm sorry, but that file does not exist</p>", 'filled-in');
	}
	
	function get ()
	{
		$files = array ();
		$list  = glob (rtrim ($this->base, '/')."/{$this->template}/*");
		if (count ($list) > 0)
		{
			foreach ($list AS $file)
				$files[] = new FI_Upload (array ('name' => basename ($file), 'tmp_name' => $file));
		}
		
		return $files;
	}
	
	function delete ($name)
	{
		$name = $this->sanitize ($name);
		
		$newname = $this->base.'/'.$this->template.'/';
		$newname .= $name;

		if (file_exists ($newname))
			unlink ($newname);
	}
}

?>