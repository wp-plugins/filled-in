<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><?php if ($css) : ?>
<link rel="stylesheet" href="<?php bloginfo ('wpurl') ?>/wp-content/plugins/filled-in/form.css" type="text/css" media="all"/>
<?php endif; ?>

<?php if ($ajax) : ?>
<script type="text/javascript" src="<?php bloginfo ('wpurl') ?>/wp-includes/js/jquery/jquery.js"></script>
<script type="text/javascript" src="<?php echo $this->url () ?>/js/filled_in.js"></script>
<?php endif;
