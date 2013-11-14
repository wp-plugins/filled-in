<?php

include ('../../../../wp-config.php');

if (!current_user_can ('publish_pages') || !isset ($_GET['id']))
	die ('<p style="color: red">You are not allowed access to this resource</p>');

$id = basename ($_GET['id']);
$id = preg_replace ('/[\*\?\^%\/\\~]/', '', $id);
$uploaded = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && current_user_can ('activate_plugins'))
{
  if (is_uploaded_file ($_FILES['attachment']['tmp_name']))
  {
		$name = $_FILES['attachment']['name'];
		if (strlen ($_POST['name']) > 0)
			$name = $_POST['name'];

		$attachment = new EmailAttachment ($id);
		$uploaded = $attachment->upload ($_FILES['attachment']['tmp_name'], $name);
	}
}
else if (isset ($_GET['file']) && current_user_can ('publish_pages'))
{
	$file = basename ($_GET['file']);
	$file = preg_replace ('/[\*\?\^%\/\\~]/', '', $file);
	
	$attachment = new EmailAttachment ($id);
	$attachment->download ($file);
	return;
} elseif (!isset ($_GET['file']) && current_user_can ('publish_pages') && !current_user_can ('activate_plugins')) {
    die ('<p style="color: red">You are not allowed access to this resource</p>');
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
	<title>Email Attachment</title>
	<link rel="stylesheet" href="<?php bloginfo ('wpurl') ?>/wp-content/plugins/filled-in/controller/admin.css" type="text/css"/>
	<style type="text/css" media="screen">
		* { padding: 0; margin: 0;}
		body, input { font-size: 13px; font-family: "Lucida Grande","Lucida Sans Unicode",Tahoma,Verdana;}
	</style>
</head>

<body>
	<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
		<table>
			<tr>
				<td><?php _e ('Name', 'filled-in') ?>:</td>
				<td><input type="text" name="name" value="" id="name"/></td>
			</tr>
			<tr>
				<td><?php _e ('File', 'filled-in'); ?>:</td>
				<td><input type="file" name="attachment"/></td>
			</tr>
			<tr>
				<td/>
				<td>
					<input type="submit" value="Upload" name="<?php _e ('Upload', 'filled-in'); ?>"/>
					<input type="hidden" name="id" value="<?php echo $id ?>" id="id"/>
				</td>
			</tr>
		</table>
	</form>

<?php if ($uploaded) : ?>
	<script type="text/javascript" charset="utf-8">
		window.parent.refresh_files ('<?php echo $id ?>');
	</script>
<?php endif; ?>
</body>

</html>