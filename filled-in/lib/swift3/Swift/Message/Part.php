<?php

/**
 * Swift Mailer Message MIME Part
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Message
 * @license GNU Lesser General Public License
 */

require_once dirname(__FILE__) . "/Mime.php";

/**
 * MIME Part body component for Swift Mailer
 * @package Swift_Message
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_Message_Part extends Swift_Message_Mime
{
	/**
	 * Constructor
	 * @param mixed The data to use in the body
	 * @param string Mime type
	 * @param string The encoding format used
	 * @param string The charset used
	 */
	function Swift_Message_Part($data=null, $type="text/plain", $encoding=null, $charset=null)
	{
		$this->Swift_Message_Mime();
		
		$this->setContentType($type);
		$this->setEncoding($encoding);
		$this->setCharset($charset);
		$this->setFlowed(false);
		
		if ($data !== null)
		{
			$this->setData($data);
			if ($charset === null)
			{
				if (is_string($data) && $this->_encoder->isUTF8($data)) $this->setCharset("utf-8");
				else $this->setCharset("iso-8859-1"); //The likely encoding
			}
		}
	}
	/**
	 * Set the charset of the document
	 * @param string The charset used
	 */
	function setCharset($charset)
	{
		$this->headers->setAttribute("Content-Type", "charset", $charset);
		if (($this->getEncoding() == "7bit") && (strtolower($charset) == "utf-8" || strtolower($charset) == "utf8")) $this->setEncoding("8bit");
	}
	/**
	 * Get the charset used in the document
	 * Returns null if none is set
	 * @return string
	 */
	function getCharset()
	{
		if ($this->headers->hasAttribute("Content-Type", "charset"))
		{
			return $this->headers->getAttribute("Content-Type", "charset");
		}
		else
		{
			return null;
		}
	}
	/**
	 * Set the "format" attribute to flowed
	 * @param boolean On or Off
	 */
	function setFlowed($flowed=true)
	{
		$value = null;
		if ($flowed) $value = "flowed";
		$this->headers->setAttribute("Content-Type", "format", $value);
	}
	/**
	 * Pre-compilation logic
	 */
	function preBuild()
	{
		if (!$this->getEncoding()) $this->setEncoding("8bit");
		if ($this->getCharset() === null && !$this->numChildren())
		{
			if (is_string($this->getData()) && $this->_encoder->isUTF8($this->getData()))
			{
				$this->setCharset("utf-8");
			}
			else $this->setCharset("iso-8859-1");
		}
		elseif ($this->numChildren())
		{
			$this->setCharset(null);
		}
	}
}