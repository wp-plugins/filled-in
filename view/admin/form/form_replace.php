<?php 
  if (!defined ('ABSPATH')) die ('No direct access allowed');
  $bNotice = get_option('filled_in_notice');
  if( current_user_can('manage_options') && $bNotice == 'false' ) {
      include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
      $bActive = is_plugin_active('fv-antispam/fv-antispam.php');    
      if (!$bActive) { ?>
        <p>
          <small>(For better protection of this form install or activate <a href="http://wordpress.org/extend/plugins/fv-antispam/" target="_blank">FV Antispam</a>.)</small>
        </p>
<?php }
      else { 
        $aOptions = get_option('fv_antispam');
        if (!$aOptions['protect_filledin']) { ?>
          <p>
            <small>(For better protection check the "<i>Protect Filled in forms</i>" in <a href="<?php echo site_url() . '/wp-admin/options-general.php?page=fv-antispam/fv-antispam.php'?>">FV Antispam</a> options.)</small>
          </p>  
  <?php }
      }
  }
?>
<form method="post" action="<?php echo $action.$top ?>" <?php echo $params ?><?php echo $upload ?>>
	<input type="hidden" name="filled_in_form" value="<?php echo $formid ?>"/>
	<input type="hidden" name="filled_in_start" value="<?php echo $time ?>"/>

	<?php echo $inside; ?>
</form>

<?php if (isset($ajax)) echo $ajax ?>
