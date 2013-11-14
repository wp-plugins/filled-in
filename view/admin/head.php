<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<script type="text/javascript">
  wp_base = '<?php echo $this->url () ?>/controller/admin_ajax.php';
	wp_confirm_ext = '<?php _e ('Are you sure you want to delete that extension?', 'filled-in') ?>';
	wp_confirm_form = '<?php _e ('Are you sure you?', 'filled-in') ?>';
	wp_confirm_data = '<?php _e ('Are you sure you want to delete these entries?', 'filled-in') ?>';
	wp_confirm_file = '<?php _e ('Are you sure you want to delete that file?', 'filled-in') ?>';
</script>