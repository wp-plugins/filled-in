<?php

class Result_Display_Form extends FI_Results
{
	function process (&$source)
	{
		$copy = $source;
		if (isset ($copy->sources['files']))
			unset ($copy->sources['files']);     // We don't want uploads going back in

		$text = $this->original_text;
		$errors = new FI_Errors;
		if ($this->config['prefill'] != 'false')
			$text = $copy->refill_data ($text, $errors);

		return $text;
	}

	function name ()
	{
		return __ ("Display input form", 'filled-in');
	}
	
	function edit ()
	{
		?>
		<tr>
				<td width="100"><label for="prefill"><?php _e ('Pre-fill form', 'filled-in'); ?>:</label:</td>
				<td>
					<input type="checkbox" name="prefill" id="prefill"<?php if ($this->config['prefill'] != 'false') echo ' checked="checked"'?>/>
				</td>
			</tr>		
		<?php
	}
	
	function show ()
	{
		parent::show ();
		if (!isset ($this->config['prefill']) || $this->config['prefill'] == 'true')
			_e (' (pre-filled)', 'filled-in');
	}
	
	function save ($arr)
	{
		return array ('prefill' => isset ($arr['prefill']) ? 'true' : 'false');
  }

	function is_editable () { return true;}
}


$this->register ('Result_Display_Form');
?>