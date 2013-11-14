<?php

class FI_Data_POST extends FI_Data_Source
{
	var $time_to_complete = 0;
	var $form             = null;
	var $page             = null;

	function FI_Data_POST ($data, $config = '')
	{
		if (is_array ($data))
		{
			// Extract data from $_POST
			if (isset ($data['filled_in_form']) && intval ($data['filled_in_form']) > 0)
			{
				$this->form = FI_Form::load_by_id (intval ($data['filled_in_form']));
				unset ($data['filled_in_form']);
			}

			if (isset ($data['filled_in_errors']))
				unset ($data['filled_in_errors']);

			if (isset ($data['filled_in_upload']))
				unset ($data['filled_in_upload']);

			if (isset ($data['filled_in_start']))
			{
				$this->time_to_complete = intval ($data['filled_in_start']);
				unset ($data['filled_in_start']);
			}
			
			if (isset ($data['_']))
				unset ($data['_']);      // Caused by some AJAX thing
				
			$this->data = $data;
		}
		else if (is_object ($data))
		{
			$this->time_to_complete = $data->time_to_complete;
			$this->data = '';
			if ($data->data)
				$this->data = @unserialize ($data->data);
		}

		// Stripslashes on the data
		$this->data = $this->clean ($this->data);
	}
	
	function sync ($time)
	{
		$this->time_to_complete = $time - $this->time_to_complete;
		if ($this->time_to_complete < 0)
			$this->time_to_complete = 0;
	}
	
	function save ($dataid, $filters)
	{
		unset ($this->data['']);
		if (count ($this->data) > 0)
		{
			// Filter data to save
			$copy = $this->data;

			if (count ($filters) > 0)
			{
				foreach ($filters AS $filter)
				{
					if (isset ($copy[$filter->name]))
						$copy[$filter->name] = $filter->pre ($copy[$filter->name]);
				}
			}
			
			return array ('data' => serialize ($copy), 'time_to_complete' => $this->time_to_complete);
		}
		return array ();
	}
	
	function clean ($data)
	{
		if (is_array ($data))
		{
			foreach ($data AS $key => $arg)
				$data[$key] = $this->clean ($arg);
			return $data;
		}
		
		return stripslashes ($data);
	}
	
	function refill_data ($text, $errors)
	{
		// Insert data back into form
		foreach ($this->data AS $field => $value)
		{	
			// Find the input in the HTML
			$replacer = new Form_Replacer ($field, $value, $errors->in_error ($field));
	
			// Replace any input, select, or textarea fields
			$text = preg_replace_callback( '/<input([^>]+?)name="'.$field.'(\[\])?"(.*?)\/>/', array( $replacer, 'replace_input' ), $text );
			$text = preg_replace_callback( '/<select([^>]+?)name="'.$field.'(\[\])?"(.*?)>(.*?)<\/select>/s', array( $replacer, 'replace_select' ), $text );
			$text = preg_replace_callback( '/<textarea([^>]+?)name="'.$field.'"(.*?)>(.*?)<\/textarea>/s', array( $replacer, 'replace_textarea' ), $text );
		}

		return $text;
	}

   function LinkUrl( $strText, $strField ){

      if( isset( $this->aSpecialFormat[$strField] ) )
         $strText = preg_replace( '~(http(?:s)?://[\S]*)~i', '<a href="$1">$1</a>', $strText );

      return $strText;
   }

   function replace( $text, $encode = false, $bWpAutop = false ){
      assert (is_string ($text));
      assert (is_bool ($encode));

      foreach( $this->data as $key => $value ){

         if( !$encode ){
            if( is_array( $value ) )
               $value = implode( " ,\n", $value );

            $text = str_replace( "\$$key\$", $value, $text );
         }else{
            if( !is_array( $value ) )
               $text = str_replace( "\$$key\$", $this->LinkUrl( htmlspecialchars( $value ), $key ), $text );
            else{
               foreach( $value as &$val )
                  $val = $this->LinkUrl( htmlspecialchars( $val ) );
               $value = implode( " ,<br />\n", $value );

               $text = str_replace( "\$$key\$", $value, $text );
            }

            if( $bWpAutop )
               $text = wpautop( $text );
         }
      }

      return $text;
   }

   function display( $field, $echo = true, $bEncode = false ){

      if( isset( $this->data[$field] ) ){

         if( !is_array( $this->data[$field] ) ){
            if( $bEncode )
               $str = $this->LinkUrl( htmlspecialchars( $this->data[$field] ), $field );
            else
               $str = $this->data[$field];

         }else{
            $aData = array();

            foreach( $this->data[$field] as $pos => $value ){
               if( '' == $value )
                  continue;

               if( $bEncode )
                  $aData[] = $this->LinkUrl( htmlspecialchars( $value ), $field );
               else
                  $aData[] = $value;
            }

            if( $bEncode )
               $str = implode( " ,<br />", $aData );
            else
               $str = implode( " ,\n", $aData );
         }
      }

      if (!$echo)
         return $str;
      echo $str;
   }

	function what_group () { return 'post'; }
}

?>