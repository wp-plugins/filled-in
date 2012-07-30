<?php

include (dirname (__FILE__).'/extensions.php');

class FI_Extension_Factory
{
	var $available;
	
	function FI_Extension_Factory ()
	{
		/*include the regular extensions directory*/
    $this->include_extensions(ABSPATH.'wp-content/plugins/filled-in/extensions/*', false);
    
    /*include the external extensions directory*/
    $this->include_extensions(ABSPATH.'wp-content/plugins/filled-in-extensions/*', true);    
	}
  
  function include_extensions ($extension_dir, $external = false) {
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
						else {
              if (!$external) {
                $subfile_exploded = explode('/', $subfile);
                $file_name = $subfile_exploded[count($subfile_exploded) - 1];
                $file_dir = $subfile_exploded[count($subfile_exploded) - 2];
                
                $file = ABSPATH.'wp-content/plugins/filled-in-extensions/'.$file_dir.'/'.$file_name;
                if (file_exists($file)) {
                  $subfile = $file;
                }
              }
              include_once ($subfile); 
            }						
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
			$fi_globals['extension_factory'] = new FI_Extension_Factory ();
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