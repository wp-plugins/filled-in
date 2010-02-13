<?php

include (dirname (__FILE__).'/extensions.php');

class FI_Extension_Factory
{
	var $available;
	
	function FI_Extension_Factory ($extension_dir)
	{
		$groups = glob ($extension_dir, GLOB_ONLYDIR);
		if (count ($groups) > 0)
		{
			foreach ($groups AS $group)
			{
				$subfiles = glob ($group.'/*');
				if (count ($subfiles) > 0)
				{
					foreach ($subfiles AS $subfile)
					{
						if (is_dir ($subfile))
						{
							if (file_exists ("$subfile/".basename ($subfile).'.php'))
								include_once ("$subfile/".basename ($subfile).'.php');
						}
						else
							include_once ($subfile);
					}
				}
			}
		}
	}
	
	// The static method - get a reference to the only Filter_Factory object
	function get ()
	{
		global $fi_globals;
		if (!isset ($fi_globals['extension_factory']))
			$fi_globals['extension_factory'] = new FI_Extension_Factory (ABSPATH.'wp-content/plugins/filled-in/extensions/*');
		return $fi_globals['extension_factory'];
	}
	
	// Helper function for the filters to call to register theirself
	function register ($class)
	{
		$obj = new $class (array ());
		$this->available[$obj->what_group ()][$class] = $obj->name ();
	}
	
	function group ($group)
	{
		return $this->available[$group];
	}
}

?>