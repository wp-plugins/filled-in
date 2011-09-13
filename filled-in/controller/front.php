<?php

class Filled_In extends Filled_In_Plugin
{
	var $forms         = array ();
	var $grab          = null;
	var $regex         = '@(?:<p>\s*)?<form([^>]*)id="(.*?)"(.*?)>(.*?)</form>(?:\s*</p>)?@s';
	var $regexp        = '@(?:<p>\s*)?<form([^>]*)id="(.*?)"(.*?)>(.*?)<!--formout-->(?:\s*</p>)?@s';
	var $have_ajax     = false;
	var $is_ajax       = false;
	var $original_text = null;
	var $shared        = array ();

	function Filled_In ()
	{
		include_once (dirname (__FILE__).'/../models/form.php');

		FI_Extension_Factory::get ();
		
		$this->register_plugin ('filled-in', dirname (__FILE__));
		
		// Decide what to do depending if this a GET or POST
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset ($_POST['filled_in_form']))
		{
			$this->add_action ('plugins_loaded', 'grab_post_data');
			if (isset ($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
				$this->add_action ('template_redirect', 'handle_ajax');
		}
		
		// Standard filters & actions
		$this->add_action ('template_redirect', 'pre_load', 2);      // Determines if this page has any forms so we know if we need CSS
		$this->add_filter ('the_content');                           // Munges any forms in post content
		$this->add_filter ('the_excerpt', 'the_content');            // Munges any forms in excerpt
		$this->add_filter ('the_filled_in_form');
		$this->add_filter ('the_content', 'form_clean', 15);
		$this->add_filter ('the_excerpt', 'form_clean', 15);
	}

	function form_clean ($text)
	{
		return str_replace ('<!--formout-->', '', $text);
	}
	
	function the_filled_in_form ($text)
	{
		return $this->the_content ($text);
	}
	
	function pre_load ()
	{
		global $posts;

		// A workaround for the aggressive wpautop
		// foreach (array ('the_content', 'the_excerpt') AS $filter)
		// {
		// 	if (remove_filter ($filter, 'wpautop') === true)
		// 		$this->add_action ($filter, 'wpautop', 11);
		// }
		
		// Preload the forms so we know if any CSS/AJAX is needed
		if (count ($posts) > 0)
		{
			foreach ($posts AS $pos => $item)
			{
				if (preg_match_all ($this->regex, $posts[$pos]->post_content, $matches) > 0)
				{
					foreach ($matches[2] AS $name)
					{
						$newform = FI_Form::load_by_name ($name);
						if ($newform !== false)
							$this->forms[$name] = $newform;
					}
				}
			}
			
			if (count ($this->forms) > 0)
				$this->add_action ('wp_head');
		}
	}
	
	function wpautop ($text)
	{
		// WordPress makes a mess of any form.  Here we remove the form, manually wpautop it, and then put the form back
		if (preg_match_all ($this->regexp, $text, $matches) > 0)
		{
			$text = preg_replace ($this->regexp, '[[form $2]]', $text);
			$text = wpautop ($text);
			
			foreach ($matches[2] AS $pos => $name)
				$text = preg_replace ('@(?:<p>\s*)?\[\[form '.$name.'\]\](?:\s*</p>)?@', $matches[0][$pos], $text);
		}
		else
			$text = wpautop ($text);
		return $text;
	}
	
	function unhide_form ($matches)
	{
		return $this->forms[$matches[1]];
	}
	
	function the_content ($text)
	{
		$text = preg_replace_callback ($this->regex, array (&$this, 'replace_form'), $text);
		if (isset($this->replace_entire_post))
			return $this->replace_entire_post;
		return $text;
	}
	
	function handle_ajax ()
	{
		// We only execute this on an AJAX call - basically we stop WordPress from
		// running through its full loop and instead output only the part of the post we want
		$this->is_ajax = true;
		
		global $posts;

		// Go through all posts and find the one with our form
		if (count ($posts) > 0)
		{
			$regex = str_replace ('id="(.*?)"', 'id="('.$this->grab->name.')"', $this->regex);
			
			foreach ($posts AS $pos => $item)
			{
				if (preg_match ($regex, $posts[$pos]->post_content, $matches) > 0)
				{
					echo preg_replace_callback ($regex, array (&$this, 'replace_form'), $matches[0]);
					break;
				}
			}
		}

		exit;
	}
	
	function grab_post_data ()
	{
		if (isset ($_POST['filled_in_form']))
		{
			$data = new FI_Data;

			$data->add_source (new FI_Data_POST ($_POST));
			$data->add_source (new FI_Data_FILES ($_FILES, array ('upload_directory' => get_option ('filled_in_uploads'), 'recovery' => isset($_POST['filled_in_upload']) ? $_POST['filled_in_upload'] : '')));
			$data->add_source (new FI_Data_COOKIES ($_COOKIE, preg_split ('/[\s,]+/', get_option ('filled_in_cookies'))));
			$data->add_source (new FI_Data_SERVER ($_SERVER));
			
			// We process the form here so that we have the option of redirecting on success
			// and also of removing the data from $_POST so as not to collide with WordPress
			if (($this->grab = FI_Form::load_by_id (intval ($_POST['filled_in_form']))))
			{
				$this->grab->submit ($data);
				$this->have_ajax = $this->grab->options['ajax'] == 'true' ? true : false;
			}

			$_POST  = array ();
			$_FILES = array ();
		}
	}
	
	function wp_head ()
	{
		$css  = get_option ('filled_in_css') == 'true' ? true : false;
		$ajax = false;
		
		foreach ($this->forms AS $form)
		{
			if ($form->options['ajax'] == 'true')
				$ajax = true;
		}
		
		$this->render ('head', array ('css' => $css, 'ajax' => $ajax));
	}
	
	function replace_form ($matches)
	{
		$form_id   = $matches[2];
		$form_text = '<form'.$matches[1].'id="'.$matches[2].'"'.$matches[3].'>'.$matches[4].'</form>';
		$this->original_text = $form_text;

		if ($this->grab && $form_id == $this->grab->name)
		{
			// Display the form results - this is in a POST request
			$name = $this->grab->name;
			$data = $this->form_results ($this->grab, $form_text, $this->is_ajax);
		}
		else
		{
			// GET request - display the form
			global $post;
		
			if (!isset ($this->forms[$form_id]))
			{
				$loaded = FI_Form::load_by_name ($form_id);
				if ($loaded !== false)
					$this->forms[$form_id] = $loaded;
			}
			
			if (isset ($this->forms[$form_id]))
			{
				$name = $form_id;
				$data = $this->munge ($this->forms[$form_id], $form_text, $_SERVER['REQUEST_URI'], $post->ID, $this->is_ajax);
			}
			else
				return $form_text;
		}
		
		if ($name)
			return $data;
		return sprintf (__ ("Form '%s' could not be found", 'filled-in'), $form_id);
	}
	
	// Only gets fed the form itself, non of the surrounding stuff
	function form_results ($form, $text, $isajax)
	{
		global $post;
		if ($form->errors->have_errors ())
		{
			// Display errors
			// if ($form->errors->what_type () == 'filter')
			// {
				$text   = $form->sources->refill_data ($text, $form->errors);
				$text   = $this->munge ($form, $text, $_SERVER['REQUEST_URI'], $post->ID, $isajax);
				if ($form->errors->what_type () == 'filter')
					$errors = $this->capture ('error_filters', array ('error_message' => $form->errors->to_string (), 'errors' => $form->errors));
				else
					$errors = $this->capture ('error_list', array ('errors' => $form->errors, 'message' => __ ('Thank you for your submission.  Unfortunately your data was not accepted for the following reasons:', 'filled-in')));
				
				$before = $text;
				$text = preg_replace ('@<input(.*?)name="filled_in_errors"(.*?)/>@', $errors, $text);
				if ($before == $text)
					$text = preg_replace ('@<form([^>]*)id="'.$form->name.'"(.*?)>@', '<form$1id="'.$form->name.'"$2>'."\r\n$errors", $text);
			// }
			// else
			// 	return $this->capture ('error_list', array ('errors' => $form->errors, 'message' => __ ('Thank you for your submission.  Unfortunately your data was not accepted for the following reasons:', 'filled-in')));
		}
		else if (count ($form->extensions['result']))
		{
			$this->original_text = $this->munge ($form, $text, $_SERVER['REQUEST_URI'], $post->ID, $isajax);

			// Display results
			foreach ($form->extensions['result'] AS $key => $result)
			{
				$form->extensions['result'][$key]->original_text = $this->original_text;
				if ($result->is_enabled ())
					$newtext[] = $form->extensions['result'][$key]->process ($form->sources);
			}

			$text = '';
			if (count ($newtext) > 0)
				$text = implode ('', $newtext);
				
			if (!$this->is_ajax && strpos ($text, 'id="'.$form->name.'"') === false)
				$text = '<div id="'.$form->name.'">'.$text.'</div>';
		}

		if (isset($this->replace_entire_post) && $this->replace_entire_post)
			$this->replace_entire_post = $text;
		return $text;
	}
	
	/// This is the magic form processing code.
	function munge ($form, $text, $url, $post, $isajax, $time = '')
	{
		assert (is_a ($form, 'FI_Form'));
		assert (is_string ($text));
		assert ('intval ($post) > 0');
		assert ('$url != ""');

		if (preg_match ('@<form(.*?)>(.*?)</form>@s', $text, $matches) > 0)
		{
			// Remove form parameters that we'll replace ourselves
			$matches[1] = preg_replace ('/action="(.*?)"/', '', $matches[1]);
			$matches[1] = preg_replace ('/method="(.*?)"/', '', $matches[1]);
			$matches[1] = preg_replace ('@enctype="multipart/form-data"@', '', $matches[1]);
			$matches[1] = preg_replace ('@\s{1,}@', ' ', $matches[1]);

			// Run it through any form modifiers
			if (isset($form->extensions['filter']) && count ($form->extensions['filter']) > 0)
			{
				foreach ($form->extensions['filter'] AS $key => $filter)
				{
					if ($form->extensions['filter'][$key]->is_enabled ())
						$matches[2] = $form->extensions['filter'][$key]->modify ($matches[2]);
				}
			}

			$arr = array
			(
				'params'  => trim ($matches[1]),
				'inside'  => trim ($matches[2]),
				'formid'  => $form->id,
				'name'    => $form->name,
				'pageid'  => $post,
				'action'  => $url,
				'upload'  => $form->options['upload'] == 'true' ? ' enctype="multipart/form-data"' : '',
				'time'    => $time == '' ? time () : $time,
				'base'    => $url,
				'waiting' => isset($form->options['custom_submit']) ? $form->options['custom_submit'] : '',
				'top'     => isset($form->options['top_of_page']) && $form->options['top_of_page'] == true ? '' : '#'.$form->name,
			);

			if ($form->options['ajax'] == 'true')
				$arr['ajax'] = $this->capture_admin ('form/form_observe', $arr);
		
			// Then mix together for 5 minutes
			if ($isajax == true || $form->options['ajax'] != 'true')
				$text = $this->capture_admin ('form/form_replace', $arr);
			else
				$text = $this->capture_admin ('form/form_replace_ajax', $arr);
			
			$text .= '<!--formout-->';
		}
		
		return $text;
	}
}

// The main control process...
$filled_in = new Filled_In ();
