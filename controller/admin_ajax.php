<?php

include ('../../../../wp-config.php');

class Filled_In_Admin_AJAX extends Filled_In_Plugin
{
	function Filled_In_Admin_AJAX ($id, $command)
	{
		if (!current_user_can ('edit_posts'))
			die ('<p style="color: red">You are not allowed access to this resource</p>');
		
		FI_Extension_Factory::get ();
		
		$this->register_plugin ('filled-in', dirname (__FILE__));
		if (method_exists ($this, $command))
			$this->$command ($id);
		else
			die ('<p style="color: red">That function is not defined</p>');
	}
	
	function delete_forms ($id)
	{
		if (current_user_can ('administrator') && check_ajax_referer ('filledin-delete_items'))
		{
			foreach ($_POST['checkall'] AS $id)
			{
				// Delete form
				$form = FI_Form::load_by_id ($id);
				$form->delete ();
		
				// Delete results & extensions
				global $wpdb;
				$wpdb->query ("DELETE FROM {$wpdb->prefix}filled_in_extensions WHERE form_id='$id'");
				$wpdb->query ("DELETE FROM {$wpdb->prefix}filled_in_data WHERE form_id='$id'");
				$wpdb->query ("DELETE FROM {$wpdb->prefix}filled_in_errors WHERE form_id='$id'");
			}
		}
	}
	
	function edit_template ($id)
	{
	  $items = get_option ('filled_in_templates');
		$attachments = new EmailAttachment ($id);

		$this->render_admin ('email/edit_template', array ('template' => $items[$id], 'attachments' => $attachments));
	}

	function update_template ($id)
	{
	  $items = get_option ('filled_in_templates');
	  $items[$id]->from = stripslashes ($_POST['from']);
	  $items[$id]->text = stripslashes ($_POST['text']);
	  $items[$id]->html = stripslashes ($_POST['html']);
	  $items[$id]->subject = stripslashes ($_POST['subject']);
    
	  update_option ('filled_in_templates', $items);
  
	  Email_Template::show ($items[$id]);
	}
	
	function delete_templates ($id)
	{
		if (check_ajax_referer ('filledin-delete_items') && current_user_can ('administrator'))
		{
		  $items = get_option ('filled_in_templates');

			foreach ($_POST['checkall'] AS $id)
			  unset ($items[$id]);

		  update_option ('filled_in_templates', $items);
		}
	}
	
	function cancel_template ($id)
	{
	  $items = get_option ('filled_in_templates');
	  Email_Template::show ($items[$id]);
	}

	// ====================
	// Extension functions
	// ====================
	function extension_add ($formid)
	{
		if (check_ajax_referer ('filledin-add_extension'))
		{
			$result = FI_Extension::create ($formid, $_POST['type']);
		
			$form = FI_Form::load_by_id ($formid);
			$this->render_admin ('extension/current', array ('form' => $form, 'group' => $_GET['group'], 'error' => $result));
		}
	}

	function extension_delete ($id)
	{
		if (check_ajax_referer ('filledin-extension_delete_'.$id))
		{
			$ext = FI_Extension::load ($id);
			$ext->delete ();
		}
	}
	
	function extension_save ($id)
	{
		$ext = FI_Extension::load ($id);
		$ext->update (new FI_Data_POST ($_POST));
		$this->render_admin ('extension/show', array ('extension' => $ext));
	}
	
	function extension_order ($id)
	{
		FI_Extension::reorder ($_POST['ext']);
	}
		
	function extension_enable ($id)
	{
		if (check_ajax_referer ('filledin-extension_enable_'.$id))
		{
			$ext = FI_Extension::load ($id);
			$ext->enable ();
			$this->render_admin ('extension/show', array ('extension' => $ext));
		}
	}
	
	function extension_disable ($id)
	{
		if (check_ajax_referer ('filledin-extension_disable_'.$id))
		{
			$ext = FI_Extension::load ($id);
			$ext->disable ();
			$this->render_admin ('extension/show', array ('extension' => $ext));
		}
	}
	
	function extension_edit ($id)
	{
		$ext = FI_Extension::load ($id);
		$this->render_admin ('extension/edit', array ('extension' => $ext));
	}
	
	function extension_show ($id)
	{
		$ext = FI_Extension::load ($id);
		$this->render_admin ('extension/show', array ('extension' => $ext));		
	}
	
	
	
	
	
	
	
	function refresh_files ($id)
	{
		$attachments = new EmailAttachment ($id);
		$attachments->show ();
	}
	
	function delete_file ($id)
	{
		$attachments = new EmailAttachment ($id);
		$attachments->delete ($_GET['file']);
		$attachments->show ();
	}
	
	// ====================
	// Stat functions
	// ====================
	
	function show_stat ($id)
	{
		$stat   = FI_Data::load ($id);
		$errors = FI_Errors::load ($id);
		$form   = FI_Form::load_by_id ($stat->form_id);

		$columns = array ();
		if ($form->quickview)
			$columns = explode (',', $form->quickview);
		else if (count ($stat) > 0)
			$columns = array_slice (array_keys ($stat->sources['post']->data), 0, 4);

		$this->render_admin ('stat/stat_details', array ('columns' => count ($columns), 'stat' => $stat, 'post' => $stat->get_source ('post'), 'cookies' => $stat->get_source ('cookies'), 'server' => $stat->get_source ('server'), 'files' => $stat->get_source ('files'), 'errors' => $errors));
	}
	
	function delete_stats ($id)
	{
		if (check_ajax_referer ('filledin-delete_stats'))
		{
			foreach ($_POST['checkall'] AS $id)
			{
				$data  = FI_Data::load ($id);
				$error = FI_Errors::load ($id);
				if ($error != false)
					$error->delete ();
				$data->delete ();
			}
		}
	}
}


$id  = $_GET['id'];
$cmd = $_GET['cmd'];

$obj = new Filled_In_Admin_AJAX ($id, $cmd);
