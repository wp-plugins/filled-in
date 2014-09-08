<?php

include (dirname (__FILE__).'/extension_factory.php');
include (dirname (__FILE__).'/data.php');
include (dirname (__FILE__).'/file_upload.php');

class FI_Form
{
   var $id            = null;
   var $name          = null;
   var $quickview     = null;
   var $options       = null;
   var $type          = null;

   var $extensions    = null;
   var $sources       = null;
   var $errors        = null;

   public static $aForms = array();

   function FI_Form ($values)
   {
      foreach ($values AS $key => $value)
          $this->$key = $value;

      if ($this->options != '')
         $this->options = unserialize ($this->options);
      $this->extensions = FI_Extension::load_by_form ($this->id);
      $this->errors     = new FI_Errors;
   }

   function load_all (&$pager, $type = 'form')
   {
      assert (is_a ($pager, 'FI_Pager'));
      global $wpdb;

      $forms = array ();

      $results = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}filled_in_forms WHERE type='$type' ".$pager->to_limits (), ARRAY_A);
      if (count ($results) > 0)
      {
         foreach ($results AS $result)
            $forms[] = new FI_Form ($result);
      }

      $pager->set_total ($wpdb->get_var ("SELECT COUNT(*) FROM {$wpdb->prefix}filled_in_forms WHERE type='$type'"));
      return $forms;
   }

   public static function load_by_id ($id)
   {
      $id = intval( $id );
      if( isset( self::$aForms[$id] ) )
         return self::$aForms[$id];

      global $wpdb;

      $form = $wpdb->get_row ("SELECT * FROM {$wpdb->prefix}filled_in_forms WHERE id='$id'", ARRAY_A);
      if( $form ){
         self::$aForms[$id] = new FI_Form ($form);
         return self::$aForms[$id];
      }

      return false;
   }

   function load_by_name ($name, $type='form')
   {
      global $wpdb;
      
      $name = $wpdb->escape ($name);
      $form = $wpdb->get_row ("SELECT * FROM {$wpdb->prefix}filled_in_forms WHERE name='$name' AND type='$type'", ARRAY_A);
      if ($form)
         return new FI_Form ($form);
      return false;
   }

   function create ($name, $type = 'form')
   {
      if (strlen ($name) > 0)
      {
         global $wpdb;
         
         $name = FI_Form::sanitize_name ($name);
         
         // First check if form already exists
         if ($wpdb->get_var ("SELECT count(id) FROM {$wpdb->prefix}filled_in_forms WHERE name='$name' AND type='$type'") == 0)
         {
            $name = $wpdb->escape ($name);
            $wpdb->query ("INSERT INTO {$wpdb->prefix}filled_in_forms (name,type) VALUES ('$name','$type')");
            return true;
         }
         
         if ($type == 'form')
            return __ ("A form of that name already exists", 'filled-in');
         else
            return __ ("A report of that name already exists", 'filled-in');
      }
      
      if ($type == 'form')
         return __ ("Invalid form name", 'filled-in');
      else
         return __ ("Invalid report name", 'filled-in');
   }

   function delete ()
   {
      global $wpdb;
      
      if ($wpdb->query ("DELETE FROM {$wpdb->prefix}filled_in_forms WHERE id='{$this->id}'") !== false)
         return true;
      return __ ("Failed to delete form", 'filled-in');
   }

   function update_options( $customsubmit, $customid, $strSubmitAnchor ){
      global $wpdb;

      $this->options['custom_submit'] = $customsubmit;
      $this->options['custom_id'] = $customid;
      $this->options['submit-anchor'] = $strSubmitAnchor;

      $custom = $wpdb->escape( serialize( $this->options ) );
      $wpdb->query( "UPDATE {$wpdb->prefix}filled_in_forms SET options='$custom' WHERE id='{$this->id}'" );

      return true;
   }

   function update_details ($newname, $quick, $special)
   {
      assert (is_string ($newname));
      assert (is_string ($quick));

      global $wpdb;

      $type = $this->type;

      $name = FI_Form::sanitize_name ($newname);
      if (strlen ($name) > 0)
      {
         // First check if name is a duplicate
         if ($this->name == $name || $wpdb->get_var ("SELECT count(id) FROM {$wpdb->prefix}filled_in_forms WHERE name='".$wpdb->escape ($name)."' AND type='$type'") == 0)
         {
            $this->quickview = trim (preg_replace ('/[^A-Za-z0-9,\-_\[\]]/', '', $quick));
            $this->options['ajax']   = $special == 'ajax' ? 'true' : 'false';
            $this->options['upload'] = $special == 'upload' ? 'true' : 'false';

            $quick   = $wpdb->escape ($this->quickview);
            $options = serialize ($this->options);

            $this->name = $name;
            $sql = "UPDATE {$wpdb->prefix}filled_in_forms SET name='".$wpdb->escape ($name)."', quickview='$quick', options='$options' WHERE id='{$this->id}'";
            if ($wpdb->query ($sql) !== false)
               return true;

            return sprintf (__ ("Failed to update %s %s", 'filled-in'), $type, $this->name);
         }

         return sprintf (__ ("A %s of that name already exists", 'filled-in'), $type);
      }

      return sprintf (__ ("Invalid %s name", 'filled-in'), $type);
   }

   function sanitize_name ($name)
   {
      // Sanitize the form name
      $name = trim ($name);
      $name = preg_replace ('/[^0-9a-zA-Z_\-:\.]/', '_', $name);
      
      // Reduce underscores
      $name = preg_replace ('/_+/', '_', $name);
      $name = trim ($name, '_');
      
      // First character must be alphabetic
      $name = preg_replace ('/^([0-9]*)(.*)/', '$2', $name);
      return $name;
   }

   function submit ($data_sources)
   {
      $this->sources = $data_sources;
      if ($this->sources->create ($this->id))
      {
         // Run pre
         $first = $this->run_stage (isset($this->extensions['pre']) ? $this->extensions['pre'] : array(), 'pre');

         // Pre filter
         $save = $this->sources->save ($this->extensions['filter']);

         // Filter and post
         if ($first && $this->run_stage ($this->extensions['filter'], 'filter'))
            $this->run_stage ($this->extensions['post'], 'post');

         return $save;
      }

      return false;
   }

   function run_stage ($extensions, $group)
   {
      if (count ($extensions) > 0)
      {
         $errors = false;
         foreach ($extensions AS $pos => $extension)
         {
            if (($result = $extensions[$pos]->run ($this->sources)) !== true)
            {
               if( 'post' == $group ){
                  $aData = array();
                  $aData['date'] = date( 'Y-m-d H:i:s' );
                  $aData['result'] = $result;
                  $aData['form'] = $extension->form_id;
                  $aData['type'] = $extension->type;
                  $aData['config'] = $extension->config;

                  update_option( 'filled_in_recent_error_data', $aData );
                  update_option( 'filled_in_recent_error', 'yes' );
                  continue;
               }

               $errors = true;
               if ($group != 'filter')
                  break;           // We stop on first non-filter error
            }
         }

         if ($errors)
         {
            $this->errors->gather ($extensions);
            $this->errors->save ($this->id, $this->sources->id);
            return false;
         }
      }
      return true;
   }
}

?>