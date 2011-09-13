<?php

include (dirname (__FILE__).'/../models/stats.php');

define ('FILLED_IN_DB', '4');

class Filled_In_Admin extends Filled_In_Plugin
{
	function Filled_In_Admin ()
	{
		if (file_exists (ABSPATH.'wp-includes/pluggable-functions.php'))
			include (ABSPATH.'wp-includes/pluggable-functions.php');
		else
			include (ABSPATH.'wp-includes/pluggable.php');
		
		$this->register_plugin ('filled-in', dirname (__FILE__));
		
		/// Addition  1.7.6 - additional API
		$this->readme_URL = 'http://plugins.trac.wordpress.org/browser/filled-in/trunk/readme.txt?format=txt';    
	  add_action( 'in_plugin_update_message-filled-in/filled_in.php', array( &$this, 'plugin_update_message' ) );
	  /// End of addition
		
		$this->add_action ('activate_filled-in/filled_in.php', 'activate');
		if (current_user_can ('edit_posts') && is_admin ())
		{
			// Register our menus
			$this->add_filter( 'admin_menu' );
			$this->add_action( 'wp_print_scripts' );
			$this->add_action( 'admin_head', 'wp_print_styles' );
			$this->add_action( 'admin_print_styles', 'wp_print_styles' );
			$this->add_action( 'admin_footer' );
			$this->add_filter( 'contextual_help', 'contextual_help', 10, 2 );
		}

		$this->register_plugin_settings( 'filled-in/filled_in.php' );

		$this->add_filter ('audit_collect');
		FI_Extension_Factory::get ();
	}

	function plugin_settings ($links)	{
		$settings_link = '<a href="tools.php?page=filled_in.php">'.__('Forms', 'filled-in').'</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
	
	function version ()
	{
		$plugin_data = implode ('', file (dirname (__FILE__).'/../filled_in.php'));
		
		if (preg_match ('|Version:(.*)|i', $plugin_data, $version))
			return trim ($version[1]);
		return '';
	}
	
	function contextual_help ($help, $screen)
	{
		if ($screen == 'tools_page_filled_in')
		{
			$help .= '<h5>' . __('Filled In Help') . '</h5><div class="metabox-prefs">';
			$help .= '<a href="http://urbangiraffe.com/plugins/filled-in/">'.__ ('Filled In Documentation', 'filled-in').'</a><br/>';
			$help .= '<a href="http://urbangiraffe.com/support/forum/filled-in">'.__ ('Filled In Support Forum', 'filled-in').'</a><br/>';
			$help .= '<a href="http://urbangiraffe.com/tracker/projects/filled-in/issues?set_filter=1&tracker_id=1">'.__ ('Filled In Bug Tracker', 'filled-in').'</a><br/>';
			$help .= '<a href="http://urbangiraffe.com/plugins/filled-in/faq/">'.__ ('Filled In FAQ', 'filled-in').'</a><br/>';
			$help .= __ ('Please read the documentation and FAQ, and check the bug tracker, before asking a question.', 'filled-in');
			$help .= '</div>';
		}
		
		return $help;
	}
	
	function admin_footer() {
		if ( isset($_GET['page']) && $_GET['page'] == basename( __FILE__ ) ) {
			$options = $this->get_options();

			if ( !$options['support'] ) {
?>
<script type="text/javascript" charset="utf-8">
	jQuery(function() {
		jQuery('#support-annoy').animate( { opacity: 0.2, backgroundColor: 'red' } ).animate( { opacity: 1, backgroundColor: 'yellow' });
	});
</script>
<?php
			}
		}
	}
		
	function wp_print_scripts ()
	{
		if ( ( isset ($_GET['page']) && $_GET['page'] == 'filled_in.php') )
			wp_enqueue_script ('filledin', $this->url ().'/controller/admin.js', array ('jquery', 'jquery-ui-sortable', 'jquery-form'), $this->version ());
	}
	
	function wp_print_styles ()
	{
		if ( ( isset ($_GET['page']) && $_GET['page'] == 'filled_in.php') ) {
			$this->render_admin ('head');

			echo '<link rel="stylesheet" href="'.$this->url ().'/controller/admin.css" type="text/css" media="screen" title="no title" charset="utf-8"/>';

			if (!function_exists ('wp_enqueue_style'))
				echo '<style type="text/css" media="screen">
				.subsubsub {
					list-style: none;
					margin: 8px 0 5px;
					padding: 0;
					white-space: nowrap;
					font-size: 11px;
					float: left;
				}
				.subsubsub li {
					display: inline;
					margin: 0;
					padding: 0;
				}
				</style>';
				
			if (get_option ('filled_in') !== FILLED_IN_DB)
	 			$this->upgrade_tables ();
		}
	}
	
	function is_25 ()
	{
		global $wp_version;
		if (version_compare ('2.5', $wp_version) <= 0)
			return true;
		return false;
	}
	
	function audit_collect ($items)
	{
		$items['filledin'] = 'Filled In management';
		return $items;
	}
	
	function admin_menu ()
	{
		add_management_page (__("Filled In", 'filled-in'), __("Filled In", 'filled-in'), "administrator", 'filled_in.php', array ($this, "display_admin_screen"));
	}
	
	function upgrade_tables ()
	{
		global $wpdb;

		$current = get_option ('filled_in');
		if ($current == "true")
		{
			$wpdb->query ("ALTER TABLE `{$wpdb->prefix}filled_in_data` ADD `time_to_complete` int(11) unsigned NOT NULL DEFAULT '0'");
			$wpdb->query ("ALTER TABLE `{$wpdb->prefix}filled_in_data` ADD `upload` text DEFAULT NULL AFTER `data`");
			$wpdb->query ("ALTER TABLE `{$wpdb->prefix}filled_in_data` ADD `cookie` text DEFAULT NULL AFTER `data`");
			$wpdb->query ("ALTER TABLE `{$wpdb->prefix}filled_in_forms` ADD `options` mediumtext NOT NULL");
			$wpdb->query ("ALTER TABLE `{$wpdb->prefix}filled_in_data` CHANGE `form` `form_id` int(11) UNSIGNED NOT NULL DEFAULT '0'");
			$wpdb->query ("ALTER TABLE `{$wpdb->prefix}filled_in_forms` ADD `type` enum('form','report') default 'form'");
			
			$wpdb->query ("CREATE TABLE `{$wpdb->prefix}filled_in_errors` (
			  `id` int(11) unsigned NOT NULL auto_increment,
			  `form_id` int(11) unsigned NOT NULL default '0',
			  `data_id` int(11) unsigned NOT NULL default '0',
			  `type` enum('pre','post','filter','result') NOT NULL default 'pre',
			  `message` text NOT NULL,
			  PRIMARY KEY  (`id`)
			)");
			
			$wpdb->query ("CREATE TABLE `{$wpdb->prefix}filled_in_extensions` (
			  `id` int(11) unsigned NOT NULL auto_increment,
			  `form_id` int(11) unsigned NOT NULL,
			  `name` varchar(50) default NULL,
  			`base` enum('pre','filter','post','result') NOT NULL default 'pre',
			  `type` varchar(50) NOT NULL,
			  `config` text NOT NULL,
			  `position` int(10) unsigned NOT NULL default '0',
			  `status` enum('on','off') NOT NULL default 'on',
			  PRIMARY KEY  (`id`)
			) ");
			
			// Convert all thank-you messages to a result processor
			$forms = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}filled_in_forms WHERE thankyou != ''");
			if (count ($forms) > 0)
			{
				foreach ($forms AS $form)
					$wpdb->query ("INSERT INTO {$wpdb->prefix}filled_in_extensions (form_id,type,base, config,status) VALUES ('{$form->id}','Result_Display_Message','result', '".$wpdb->escape (serialize (array ('message' => $form->thankyou, 'autop' => 'true')))."','on')");
			}
			
			// Convert all processors and filters to new extension format
			$list = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}filled_in_filters");
			if (count ($list) > 0)
			{
				foreach ($list AS $filter)
					$wpdb->query ("INSERT INTO {$wpdb->prefix}filled_in_extensions (form_id, name, type, base, config, status) VALUES ('{$filter->form}','{$filter->field}','{$filter->filter}', 'filter', '{$filter->arguments}', 'on')");
			}
			
			$list = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}filled_in_processors");
			if (count ($list) > 0)
			{
				foreach ($list AS $proc)
					$wpdb->query ("INSERT INTO {$wpdb->prefix}filled_in_extensions (form_id, type, base, config, status) VALUES ('{$proc->form}','{$proc->processor}', '{$proc->stage}', '{$proc->value}', 'on')");
			}
			
			// Now update the positions of all extensions
			$list = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}filled_in_extensions");
			if (count ($list) > 0)
			{
				$items = array ();
				foreach ($list AS $ext)
					$items[$ext->form_id][$ext->base][] = $ext->id;
				
				foreach ($items AS $formid => $item)
				{
					foreach ($item AS $base => $ids)
					{
						foreach ($ids AS $pos => $id)
							$wpdb->query ("UPDATE {$wpdb->prefix}filled_in_extensions SET position=$pos WHERE id='$id'");
					}
				}
			}
			
			// Convert errors
			$list = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}filled_in_data WHERE status != 'ok'");
			if (count ($list) > 0)
			{
				foreach ($list AS $error)
				{
					$wpdb->query ("INSERT INTO {$wpdb->prefix}filled_in_errors (form_id,data_id,type,message) VALUES ('{$error->form_id}','{$error->id}','{$error->status}','".$wpdb->escape ($error->message)."')");
				}
			}

			// Delete filter and processor tables
			$wpdb->query ("DROP TABLE {$wpdb->prefix}filled_in_filters");
			$wpdb->query ("DROP TABLE {$wpdb->prefix}filled_in_processors");
			$wpdb->query ("ALTER TABLE `{$wpdb->prefix}filled_in_forms` DROP `thankyou`");
			$wpdb->query ("ALTER TABLE `{$wpdb->prefix}filled_in_forms` DROP `last_checked`");
			$wpdb->query ("ALTER TABLE `{$wpdb->prefix}filled_in_data` DROP `status`");
			$wpdb->query ("ALTER TABLE `{$wpdb->prefix}filled_in_data` DROP `message`");

         $wpdb->query( "CREATE INDEX filled_errors_data ON `{$wpdb->prefix}filled_in_errors` (`data_id`)" );
         $wpdb->query( "CREATE INDEX filled_form_data ON `{$wpdb->prefix}filled_in_data` (`form_id`)" );

			delete_option ('filled_in_ajax');
		}
		elseif ($current == 2) {
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}filled_in_data` ADD INDEX  (`user_agent`)");
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}filled_in_data` ADD INDEX  (`form_id`)");
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}filled_in_errors` ADD INDEX  (`data_id`)");
		}
		elseif( 3 == $current ){
         $wpdb->query( "CREATE INDEX filled_errors_data ON `{$wpdb->prefix}filled_in_errors` (`data_id`)" );
         $wpdb->query( "CREATE INDEX filled_form_data ON `{$wpdb->prefix}filled_in_data` (`form_id`)" );
      }

		update_option ('filled_in', FILLED_IN_DB);
		return true;
	}
	
	function activate ()
	{
		if (($sql = @file_get_contents (dirname (__FILE__).'/create.sql')) !== false)
		{
			global $wpdb;
			
			/// Addition  1.7.6 - create tables with the same charset as WPDB
			if ( ! empty($wpdb->charset) )
      	$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
      if ( ! empty($wpdb->collate) )
      	$charset_collate .= " COLLATE $wpdb->collate";
			if ( ! empty($charset_collate) ) 
			  $sql = str_replace ( 'CHARSET = latin1', $charset_collate, $sql );
			/// End of Addition  
			
			$sql = str_replace ('filled_in', $wpdb->prefix.'filled_in', $sql);
			if (preg_match_all ('/CREATE(.*?);/s', $sql, $matches) > 0)
			{
				foreach ($matches[0] AS $table)
				{
					if ($wpdb->query ($table) === false)
						return false;
				}
				
				update_option ('filled_in', FILLED_IN_DB);
				return true;
			}
		}
		
		return false;
	}
	
	function submenu ($inwrap = false)
	{
		// Decide what to do
		$sub = isset ($_GET['sub']) ? $_GET['sub'] : '';
	  $url = explode ('&', $_SERVER['REQUEST_URI']);
	  $url = $url[0];

		if (!$this->is_25 () && $inwrap == false)
			$this->render_admin ('submenu', array ('url' => $url, 'sub' => $sub, 'class' => 'id="subsubmenu"'));
		else if ($this->is_25 () && $inwrap == true)
			$this->render_admin ('submenu', array ('url' => $url, 'sub' => $sub, 'class' => 'class="subsubsub"', 'trail' => ' | '));
			
		return $sub;
	}
	
	function display_admin_screen ()
	{
		// Decide what to do
		$sub = $this->submenu ();
    
		if (isset($_GET['sub'])) {
	    if ($_GET['sub'] == 'templates')
	      return $this->display_email_screen ();
	    else if ($_GET['sub'] == 'options' && current_user_can ('edit_plugins'))
	      return $this->display_options ();
	    else if ($_GET['sub'] == 'reports' && current_user_can ('edit_plugins'))
			{
				if (isset ($_GET['edit']))
					return $this->edit_report (intval ($_GET['edit']));
				else
	      	return $this->display_report ();
			}
		}
		else
		{
			// What we display depends on the GET arguments
			if (isset ($_GET['edit']) && current_user_can ('edit_plugins'))
				$this->display_edit_page ($_GET['edit']);
			else if (isset ($_GET['total']))
				$this->display_stats ($_GET['total']);
			else if (isset ($_GET['errors']))
				$this->display_stats ($_GET['errors'], 'errors');
			else
				$this->display_form_list ();
		}
	}
	
	function edit_report ($report)
	{
		$form = FI_Form::load_by_id ($report);
		if (isset ($_POST['update']))
		{
			$result = $form->update_details ($_POST['new_name'], '', '');
			if ($result === true)
				$this->render_message (__ ('Your report has been updated', 'filled-in'));
			else
				$this->render_error ($result);
		}
		
		$this->render_admin ('report/edit', array ('report' => $form));
	}
	
	function display_report ()
	{
		$pager  = new FI_Pager (array ('perpage' => 0), '');
		
		if (isset ($_POST['create']))
		{
			$result = FI_Form::create ($_POST['form_name'], 'report');
			if ($result === true)
				$this->render_message (__("Your batch has been created", 'filled-in'));
			else
				$this->render_error ($result);
		}
		else if (isset ($_POST['run']))
		{
			if (!ini_get ('safe_mode'))
				set_time_limit (0);
			
			$success   = 0;
			$failed    = 0;
			$startdate = '';
			$enddate   = '';
			
			if ($_POST['start_type'] == 'custom')
				$startdate = sprintf ('%04d-%02d-%02d %02d:%02d:00', $_POST['start_year'], $_POST['start_month'], $_POST['start_day'], $_POST['start_hour'], $_POST['start_minute']);
			
			if ($_POST['end_type'] == 'custom')
				$enddate = sprintf ('%04d-%02d-%02d %02d:%02d:00', $_POST['end_year'], $_POST['end_month'], $_POST['end_day'], $_POST['end_hour'], $_POST['end_minute']);
			
			$data   = FI_Data::load_by_form (intval ($_POST['form']), $pager, $startdate, $enddate);
			$report = FI_Form::load_by_id (intval ($_POST['report']));

			// Push data through report
			if (count ($report->extensions['post']) > 0 && count ($data) > 0)
			{
				foreach ($report->extensions['post'] AS $extension)
				{
					foreach ($data AS $item)
					{
						if ($extension->run ($item) === true)
							$success++;
						else
							$failed++;
					}
				}
			}
			
			$this->render_message (sprintf (__ ('Your batch has finished.  %d entries were processed, %d failed', 'filled-in'), $success + $failed, $failed));
		}
		
		$sub = isset ($_GET['sub']) ? $_GET['sub'] : '';
	  $url = explode ('&', $_SERVER['REQUEST_URI']);
	  $url = $url[0];
	
		$reports    = FI_Form::load_all ($pager, 'report');
		$forms      = FI_Form::load_all ($pager);
		$base       = $url;
		$end_date   = date ('Y-m-d H:i:s');
		$start_date = date ('Y-m-d H:i:s', time () - 24 * 60 * 60);
		
		global $wp_locale;
		if (is_array ($wp_locale))
			$months = $wp_locale->months;     // WordPress 2.1
		else
		{
			$months['01'] = __('January');
			$months['02'] = __('February');
			$months['03'] = __('March');
			$months['04'] = __('April');
			$months['05'] = __('May');
			$months['06'] = __('June');
			$months['07'] = __('July');
			$months['08'] = __('August');
			$months['09'] = __('September');
			$months['10'] = __('October');
			$months['11'] = __('November');
			$months['12'] = __('December');
		}
		
		$this->render_admin ('report/report', array ('reports' => $reports, 'forms' => $forms, 'base' => $base, 'end_date' => $end_date, 'start_date' => $start_date, 'months' => $months));
	}
	
	function display_email_screen ()
	{
	  $templates = get_option ('filled_in_templates');
	  if (isset ($_POST['create']) && check_admin_referer ('filledin-add_template'))
	  {
	    $template = new Email_Template ($_POST);
	    if (isset ($templates[$template->name]))
				$this->render_error (__('That template already exists', 'filled-in'));
	    else
	    {
	      $templates[$template->name] = $template;
	      update_option ('filled_in_templates', $templates);
				$this->render_message (__('Email template created', 'filled-in'));
      }
	  }

	  $templates = get_option ('filled_in_templates');  
		$this->render_admin ('email/templates', array ('templates' => $templates));
	}
	
	function display_options ()
	{
		if (current_user_can ('administrator'))
		{
		  if (isset ($_POST['save']) && check_admin_referer ('filledin-save_options'))
		  {
		    update_option ('filled_in_css',         isset ($_POST['css']) ? 'true' : 'false');
				update_option ('filled_in_smtp_host',   $_POST['smtp_host']);
				update_option ('filled_in_smtp_port',   intval ($_POST['smtp_port']));
				update_option ('filled_in_smtp_ssl',    $_POST['smtp_ssl']);
				update_option ('filled_in_smtp_username',    $_POST['smtp_username']);
				update_option ('filled_in_smtp_password',    $_POST['smtp_password']);
				update_option ('filled_in_attachments', rtrim ($_POST['attachments'], '/'));
				update_option ('filled_in_uploads',     rtrim ($_POST['uploads'], '/'));
				update_option ('filled_in_cookies',     trim ($_POST['cookies']));
			
				$this->render_message (__('Options updated', 'filled-in'));
		  }
			else if (isset ($_POST['destroy']) && check_admin_referer ('filledin-remove_plugin'))
			{
				$this->remove_everything ();
				$this->render_message (__ ('All Filled In data has been removed and the plugin de-activated'));
				return;
			}
	
			$this->render_admin ('options');
		}
  }

	function display_form_list ()
	{
		if (isset ($_POST['create']) && check_admin_referer ('filledin-create_form'))
		{
			$result = FI_Form::create ($_POST['form_name']);
			if ($result === true)
				$this->render_message (__("Your form has been created", 'filled-in'));
			else
				$this->render_error ($result);
		}

		$sub = isset ($_GET['sub']) ? $_GET['sub'] : '';
	  $url = explode ('&', $_SERVER['REQUEST_URI']);
	  $url = $url[0];
	
		$pager = new FI_Pager ($_GET, $_SERVER['REQUEST_URI'], 'name', 'ASC');
		$base  = $url;
		$admin = current_user_can ('edit_plugins');
		
		$stats = new FI_FormStats (FI_Form::load_all ($pager), $pager);
		$this->render_admin ('form/list', array ('forms' => $stats->forms, 'base' => $base, 'admin' => $admin, 'pager' => $pager));
	}
	
	function display_edit_page ($id)
	{
		if (($form = FI_Form::load_by_id ($id)))
		{
			$result = true;
			$msg = '';
			if (isset ($_POST['update']))
			{
				$msg = __ ("Form details updated successfully", 'filled-in');
				$result = $form->update_details ($_POST['new_name'], $_POST['quickview'], $_POST['special']);
			}
			else if (isset ($_POST['update_options']))
			{
				$msg = __ ("Form details updated successfully", 'filled-in');
				$result = $form->update_options ($_POST['custom_submit'], isset ($_POST['top_of_page']) ? true : false);
			}

			if ($result === true && $msg != '')
				$this->render_message ($msg);
			else if ($result !== true)
				$this->render_error ($error);
			
			global $wp_roles;
			$this->render_admin ('form/edit', array ('form' => $form, 'roles' => $wp_roles));
		}
		else
			$this->render_error (__ ('Invalid form id', 'filled-in'));
	}

	function display_stats ($formid, $type = '')
	{
		if (($form = FI_Form::load_by_id ($formid)))
		{
			$pager = new FI_Pager ($_GET, $_SERVER['REQUEST_URI'], 'created', 'DESC');
			
			if ($type == 'errors')
			{
				if (isset ($_POST['delete']))
					FI_Data::delete_errors ($form->id);

				$stats = FI_Data::load_by_form_errors ($form->id, $pager);
			}
			else
			{
				if (isset ($_POST['delete']))
					FI_Data::delete_results ($form->id);

				$stats = FI_Data::load_by_form ($form->id, $pager);
			}

			// Decide what columns to display
			$columns = array ();
			if ($form->quickview)
				$columns = explode (',', $form->quickview);
			else if (count ($stats) > 0)
				$columns = array_slice (array_keys ($stats[0]->sources['post']->data), 0, 4);
	
			$title = $type == 'errors' ? __ ('Failed results', 'filled-in') : __ ('Successful results', 'filled-in');
			$this->render_admin ('stat/statistics', array ('title' => $title, 'form' => $form, 'stats' => $stats, 'columns' => $columns, 'pager' => $pager));
		}
		else
			$this->render_error (__ ("Invalid id", 'filled-in'));
	}
	
	function remove_everything ()
	{
		global $wpdb;
		
		$wpdb->query ("DROP TABLE {$wpdb->prefix}filled_in_data");
		$wpdb->query ("DROP TABLE {$wpdb->prefix}filled_in_extensions");
		$wpdb->query ("DROP TABLE {$wpdb->prefix}filled_in_errors");
		$wpdb->query ("DROP TABLE {$wpdb->prefix}filled_in_forms");
		$wpdb->query ("DROP TABLE {$wpdb->prefix}filled_in_useragents");
		
		$options = array ('filled_in', 'filled_in_attachments', 'filled_in_cookies', 'filled_in_css', 'filled_in_from', 'filled_in_smtp_host', 'filled_in_smtp_port', 'filled_in_smtp_ssl', 'filled_in_templates', 'filled_in_uploads');
		array_walk ($options, 'delete_option');
		
		$current = get_option('active_plugins');
		array_splice($current, array_search('filled-in/filled_in.php', $current), 1 ); // Array-fu!
		update_option('active_plugins', $current);
	}
}

// The admin plugin!
$obj = new Filled_In_Admin ();
