<?php

class Result_Redirect_URL extends FI_Results
{
	function process (&$source)
	{
		global $filled_in;
		if ($filled_in->is_ajax)
		{
			ob_start ();
			?>
			<script type="text/javascript">
				document.location.href = '<?php echo $this->config["url"] ?>';
			</script>
			<?php
			$output = ob_get_contents ();
			ob_end_clean ();
			return $output;
		}
		else
		{
			wp_redirect ($this->config['url']);
			exit;
		}
	}

	function name ()
	{
		return __ ("Redirect to URL", 'filled-in');
	}
	
	function edit ()
	{
		?>
	<tr>
		<td width="50"><?php _e ('URL', 'filled-in'); ?>:</td>
		<td>
			<input style="width: 95%" type="text" name="url" value="<?php echo $this->config['url'] ?>" id="post_id"/>
		</td>
	</tr>		
<?php
	}
	
	function show ()
	{
		parent::show ();
		if (isset ($this->config['url']) != 0)
			$url = $this->config['url'];
		else
			$url = __ ('<em>&lt;not configured&gt;</em>', 'filled-in');
		echo ' '.$url;
	}
	
	function save ($arr)
	{
		return array ('url' => $arr['url']);
  }

	function is_editable () { return true;}
}


$this->register ('Result_Redirect_URL');
?>