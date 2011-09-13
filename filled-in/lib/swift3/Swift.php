<?php

/**
 * Swift Mailer Core Component.
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift
 * @version 3.0.0
 * @license GNU Lesser General Public License
 */

require_once dirname(__FILE__) . "/Swift/Errors.php";
require_once dirname(__FILE__) . "/Swift/Exception.php";
require_once dirname(__FILE__) . "/Swift/ConnectionBase.php";
require_once dirname(__FILE__) . "/Swift/Message.php";
require_once dirname(__FILE__) . "/Swift/Address.php";
require_once dirname(__FILE__) . "/Swift/RecipientList.php";
require_once dirname(__FILE__) . "/Swift/Log.php";
require_once dirname(__FILE__) . "/Swift/Log/DefaultLog.php";
require_once dirname(__FILE__) . "/Swift/Events/ListenerMapper.php";

if (!defined("SWIFT_NO_START")) define("SWIFT_NO_START", 2);
if (!defined("SWIFT_NO_HANDSHAKE")) define("SWIFT_NO_HANDSHAKE", 4);
if (!defined("SWIFT_ENABLE_LOGGING")) define("SWIFT_ENABLE_LOGGING", 8);
if (!defined("SWIFT_NO_POST_CONNECT")) define("SWIFT_NO_POST_CONNECT", 16);

//Load all event types and listeners
Swift::loadEvents();

/**
 * Swift is the central component in the Swift library.
 * @package Swift
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @version 3.0.0
 */
class Swift
{
	/**
	 * Constant to flag Swift not to try and connect upon instantiation
	 */
	var $NO_START = SWIFT_NO_START;
	/**
	 * Constant to tell Swift not to perform the standard SMTP handshake upon connect
	 */
	var $NO_HANDSHAKE = SWIFT_NO_HANDSHAKE;
	/**
	 * Constant to ask Swift to start logging
	 */
	var $ENABLE_LOGGING = SWIFT_ENABLE_LOGGING;
	/**
	 * Constant to prevent postConnect() being run in the connection
	 */
	var $NO_POST_CONNECT = SWIFT_NO_POST_CONNECT;
	/**
	 * The connection object currently active
	 * @var Swift_Connection
	 */
	var $connection = null;
	/**
	 * The logger object
	 * @var Swift_Log
	 */
	var $log = null;
	/**
	 * The domain name of this server (should technically be a FQDN)
	 * @var string
	 */
	var $domain = null;
	/**
	 * Flags to change the behaviour of Swift
	 * @var int
	 */
	var $options;
	/**
	 * Loaded plugins, separated into containers according to roles
	 * @var array
	 */
	var $listeners = array();
	
	/**
	 * Constructor
	 * @param Swift_Connection The connection object to deal with I/O
	 * @param string The domain name of this server (the client) as a FQDN
	 * @param int Optional flags
	 * @throws Swift_Connection_Exception If a connection cannot be established or the connection is behaving incorrectly
	 */
	function Swift(&$conn, $domain=false, $options=null)
	{
		//Do I really have to check for simpletest stuff here??
		if (!is_a($conn, "Swift_Connection") && !is_a($conn, "SimpleMock"))
		{
			trigger_error("Swift requires constructor parameter 1 to be instance of Swift_Connection.");
			return;
		}
		$this->initializeEventListenerContainer();
		$this->setOptions($options);
		$this->setLogger(new Swift_Log_DefaultLog());
		
		if ($this->hasOption($this->ENABLE_LOGGING)) $this->log->enable();
		
		if (!$domain) $domain = !empty($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "SwiftUser";
		
		$this->setDomain($domain);
		$this->connection =& $conn;
		
		if ($conn && !$this->hasOption($this->NO_START))
		{
			if ($this->log->isEnabled()) $this->log->add("Trying to connect...", SWIFT_LOG_NORMAL);
			$this->connect();
		}
	}
	/**
	 * Populate the listeners array with the defined listeners ready for plugins
	 */
	function initializeEventListenerContainer()
	{
		foreach (Swift_Events_ListenerMapper::getMap() as $listener => $interface)
		{
			if (!isset($this->listeners[$listener]))
				$this->listeners[$listener] = array();
		}
	}
	/**
	 * Load in all defined events and event listeners from the events directory
	 */
	function loadEvents()
	{
		foreach (glob(dirname(__FILE__) . "/Swift/Events/*.php") as $file)
		{
			require_once $file;
		}
	}
	/**
	 * Add a new plugin to Swift
	 * Plugins must implement one or more event listeners
	 * @param Swift_Events_Listener The plugin to load
	 */
	function attachPlugin(&$plugin, $id)
	{
		if (!is_a($plugin, "Swift_Events_Listener"))
		{
			trigger_error("Swift::attachPlugin requires parameter 1 to be instance of Swift_Plugin.");
			return;
		}
		foreach (array_keys($this->listeners) as $key)
		{
			if ($plugin->implementing($key)) $this->listeners[$key][$id] =& $plugin;
		}
	}
	/**
	 * Get an attached plugin if it exists
	 * @param string The id of the plugin
	 * @return Swift_Event_Listener
	 */
	function &getPlugin($id)
	{
		$null = null;
		foreach ($this->listeners as $type => $arr)
		{
			if (isset($arr[$id])) return $this->listeners[$type][$id];
		}
		return $null;
	}
	/**
	 * Remove a plugin attached under the ID of $id
	 * @param string The ID of the plugin
	 */
	function removePlugin($id)
	{
		foreach ($this->listeners as $type => $arr)
		{
			if (isset($arr[$id]))
			{
				unset($this->listeners[$type][$id]);
			}
		}
	}
	/**
	 * Send a new type of event to all objects which are listening for it
	 * @param Swift_Events The event to send
	 * @param string The type of event
	 */
	function notifyListeners(&$e, $type)
	{
		if (!empty($this->listeners[$type]) && $notifyMethod = Swift_Events_ListenerMapper::getNotifyMethod($type))
		{
			$e->setSwift($this);
			foreach ($this->listeners[$type] as $k => $listener)
			{
				$this->listeners[$type][$k]->$notifyMethod($e);
			}
		}
	}
	/**
	 * Set the logger to use
	 * @param Swift_Log The instantiated logger
	 */
	function setLogger(&$logger)
	{
		if (!is_a($logger, "Swift_Log"))
		{
			trigger_error("Swift::setLogger requires parameter 1 to be instance of Swift_Log.");
			return;
		}
		$this->log =& $logger;
	}
	/**
	 * Check if an option flag has been set
	 * @param string Option name
	 * @return boolean
	 */
	function hasOption($option)
	{
		return ($this->options & $option);
	}
	/**
	 * Adjust the options flags
	 * E.g. $obj->setOptions(Swift::NO_START | Swift::NO_HANDSHAKE)
	 * @param int The bits to set
	 */
	function setOptions($options)
	{
		$this->options = (int) $options;
	}
	/**
	 * Get the current options set (as bits)
	 * @return int
	 */
	function getOptions()
	{
		return (int) $this->options;
	}
	/**
	 * Set the FQDN of this server as it will identify itself
	 * @param string The FQDN of the server
	 */
	function setDomain($name)
	{
		$this->domain = (string) $name;
	}
	/**
	 * Attempt to establish a connection with the service
	 * @throws Swift_Connection_Exception If the connection cannot be established or behaves oddly
	 */
	function connect()
	{
		$this->connection->start();
		$greeting =& $this->command("", 220);
		if (!$this->hasOption($this->NO_HANDSHAKE))
		{
			$this->handshake($greeting);
		}
		$this->notifyListeners(new Swift_Events_ConnectEvent($this->connection), "ConnectListener");
	}
	/**
	 * Disconnect from the MTA
	 * @throws Swift_Connection_Exception If the connection will not stop
	 */
	function disconnect()
	{
		$this->command("QUIT");
		$this->connection->stop();
		$this->notifyListeners(new Swift_Events_DisconnectEvent($this->connection), "DisconnectListener");
	}
	/**
	 * Throws an exception if the response code wanted does not match the one returned
	 * @param Swift_Event_ResponseEvent The full response from the service
	 * @param int The 3 digit response code wanted
	 * @throws Swift_Connection_Exception If the code does not match
	 */
	function assertCorrectResponse(&$response, $codes)
	{
		if (!is_a($response, "Swift_Events_ResponseEvent"))
		{
			trigger_error("Swift::assertCorrectResponse expects parameter 1 to be of type Swift_Events_ResponseEvent.");
			return;
		}
		$codes = (array)$codes;
		if (!in_array($response->getCode(), $codes))
		{
			$error = "Expected response code(s) [" . implode(", ", $codes) . "] but got response " . $response->getCode();
			if ($this->log->isEnabled()) $this->log->add($error, SWIFT_LOG_ERROR);
			Swift_Errors::trigger(new Swift_Connection_Exception($error));
			return false;
		}
		return true;
	}
	/**
	 * Have a polite greeting with the server and work out what it's capable of
	 * @param Swift_Events_ResponseEvent The initial service line respoonse
	 * @throws Swift_Connection_Exception If conversation is not going very well
	 */
	function handshake(&$greeting)
	{
		if (!is_a($greeting, "Swift_Events_ResponseEvent"))
		{
			trigger_error("Swift::handshake expects parameter 1 to be of type Swift_Events_ResponseEvent.");
			return;
		}
		if (strpos($greeting->getString(), "ESMTP")) $this->setConnectionExtensions($this->command("EHLO " . $this->domain, 250));
		else $this->command("HELO " . $this->domain, 250);
		//Connection might want to do something like authenticate now
		if (!$this->hasOption($this->NO_POST_CONNECT)) $this->connection->postConnect($this);
	}
	/**
	 * Set the extensions which the service reports in the connection object
	 * @param Swift_Events_ResponseEvent The list of extensions as reported by the service
	 */
	function setConnectionExtensions(&$list)
	{
		if (!is_a($list, "Swift_Events_ResponseEvent"))
		{
			trigger_error("Swift::setConnectionExtensions expects parameter 1 to be of type Swift_Events_ResponseEvent.");
			return;
		}
		$le = (strpos($list->getString(), "\r\n") !== false) ? "\r\n" : "\n";
		$list = explode($le, $list->getString());
		for ($i = 1, $len = count($list); $i < $len; $i++)
		{
			$extension = substr($list[$i], 4);
			$attributes = split("[ =]", $extension);
			$this->connection->setExtension($attributes[0], (isset($attributes[1]) ? array_slice($attributes, 1) : array()));
		}
	}
	/**
	 * Execute a command against the service and get the response
	 * @param string The command to execute (leave off any CRLF!!!)
	 * @param int The code to check for in the response, if any
	 * @return Swift_Events_ResponseEvent The server's response (could be multiple lines)
	 * @throws Swift_Connection_Exception If a code was expected but does not match the one returned
	 */
	function &command($command, $code=null)
	{
		$null = null;
		if (Swift_Errors::halted()) return $null;
		
		if ($command !== "")
		{
			$command_event =& new Swift_Events_CommandEvent($command, $code);
			$command = null; //For memory reasons
			$this->notifyListeners($command_event, "BeforeCommandListener");
			if ($this->log->isEnabled()) $this->log->add($command_event->getString(), SWIFT_LOG_COMMAND);
			$this->connection->write($command_event->getString());
			$this->notifyListeners($command_event, "CommandListener");
		}
		$response_event =& new Swift_Events_ResponseEvent($this->connection->read());
		$this->notifyListeners($response_event, "ResponseListener");
		if ($this->log->isEnabled()) $this->log->add($response_event->getString(), SWIFT_LOG_RESPONSE);
		if ($command !== "" && $command_event->getCode() !== null)
		{
			$this->assertCorrectResponse($response_event, $command_event->getCode());
		}
		return $response_event;
	}
	/**
	 * Reset a conversation which has gone badly
	 * @throws Swift_Connection_Exception If the service refuses to reset
	 */
	function reset()
	{
		$this->command("RSET", 250);
	}
	/**
	 * Send a message to any number of recipients
	 * @param Swift_Message The message to send.  This does not need to (and shouldn't really) have any of the recipient headers set.
	 * @param mixed The recipients to send to.  Can be a string, Swift_Address or Swift_RecipientList. Note that all addresses apart from Bcc recipients will appear in the message headers
	 * @param mixed The address to send the message from.  Can either be a string or an instance of Swift_Address.
	 * @return int The number of successful recipients
	 * @throws Swift_Connection_Exception If sending fails for any reason.
	 */
	function send(&$message, $recipients, $from)
	{
		$encoder =& Swift_Message_Encoder::instance();
		
		if (!is_a($message, "Swift_Message"))
		{
			trigger_error("Swift::send expects parameter 1 to be instance of Swift_Message.");
			return;
		}
		
		if (is_string($recipients) && preg_match("/^" . $encoder->CHEAP_ADDRESS_RE . "\$/", $recipients))
		{
			$recipients =& new Swift_Address($recipients);
		}
		elseif (!is_a($recipients, "Swift_AddressContainer"))
		{
			trigger_error("The recipients parameter must either be a valid string email address, ".
				"an instance of Swift_RecipientList or an instance of Swift_Address.");
			return;
		}
			
		if (is_string($from) && preg_match("/^" . $encoder->CHEAP_ADDRESS_RE . "\$/", $from))
		{
			$from =& new Swift_Address($from);
		}
		elseif (!is_a($from, "Swift_Address"))
		{
			trigger_error("The sender parameter must either be a valid string email address or ".
				"an instance of Swift_Address.");
			return;
		}
		
		$log_active = $this->log->isEnabled();
		
		if (!$message->getEncoding() && !$this->connection->hasExtension("8BITMIME"))
		{
			$message->setEncoding("QP", true);
		}
		
		$list =& $recipients;
		if (is_a($recipients, "Swift_Address"))
		{
			$list =& new Swift_RecipientList();
			$list->addTo($recipients);
		}
		
		$send_event =& new Swift_Events_SendEvent($message, $list, $from, 0);
		
		$this->notifyListeners($send_event, "BeforeSendListener");
		
		$to = $cc = array();
		if (!($has_from = $message->getFrom())) $message->setFrom($from);
		if (!($has_return_path = $message->getReturnPath())) $message->setReturnPath($from->build(true));
		if (!($has_reply_to = $message->getReplyTo())) $message->setReplyTo($from);
		
		$this->command("MAIL FROM: " . $message->getReturnPath(true), 250);
		
		$failed = 0;
		$sent = 0;
		foreach ($list->getTo() as $address)
		{
			$to[] = $address->build();
			Swift_Errors::expect($e, "Swift_Connection_Exception");
				$this->command("RCPT TO: " . $address->build(true), 250);
			if (!$e) {
				$sent++;
				Swift_Errors::clear("Swift_Connection_Exception");
			} else {
				$failed++;
				if ($log_active) $this->log->addfailedRecipient($address->getAddress());
			}
			$e = null;
		}
		foreach ($list->getCc() as $address)
		{
			$cc[] = $address->build();
			Swift_Errors::expect($e, "Swift_Connection_Exception");
				$this->command("RCPT TO: " . $address->build(true), 250);
			if (!$e) {
				$sent++;
				Swift_Errors::clear("Swift_Connection_Exception");
			} else {
				$failed++;
				if ($log_active) $this->log->addfailedRecipient($address->getAddress());
			}
			$e = null;
		}
		
		if ($failed == (count($to) + count($cc)))
		{
			$this->reset();
			return 0;
		}
		
		if (!($has_to = $message->getTo()) && !empty($to)) $message->setTo($to);
		if (!($has_cc = $message->getCc()) && !empty($cc)) $message->setCc($cc);
		
		$this->command("DATA", 354);
		$this->command($message->build() . "\r\n.", 250);
		
		$has_bcc = $list->getBcc();
		foreach ($list->getBcc() as $address)
		{
			if (!$has_bcc) $swift->setBcc($address->build());
			
			Swift_Errors::expect($e, "Swift_Connection_Exception");
				if (!$e) $this->command("MAIL FROM: " . $message->getReturnPath(true), 250);
				if (!$e) $this->command("RCPT TO: " . $address->build(true), 250);
				if (!$e) $this->command("DATA", 354);
				if (!$e) $this->command($message->build() . "\r\n.", 250);
			if (!$e) {
				$sent++;
				Swift_Errors::clear("Swift_Connection_Exception");
			} else {
				$failed++;
				if ($log_active) $this->log->addfailedRecipient($address->getAddress());
				$this->reset();
			}
			$e = null;
		}
		
		$total = count($to) + count($cc) + count($list->getBcc());
		
		$send_event->setNumSent($sent);
		$this->notifyListeners($send_event, "SendListener");
		
		if (!$has_return_path) $message->setReturnPath("");
		if (!$has_from) $message->setFrom("");
		if (!$has_to) $message->setTo("");
		if (!$has_reply_to) $message->setReplyTo(null);
		if (!$has_cc) $message->setCc(null);
		if (!$has_bcc) $message->setBcc(null);
		
		if ($this->log->isEnabled()) $this->log->add("Message sent to " . $sent . "/" . $total . " recipients", SWIFT_LOG_NORMAL);
		
		return $sent;
	}
	/**
	 * Send a message to a batch of recipients.
	 * Unlike send() this method ignores Cc and Bcc recipients and does not reveal every recipients' address in the headers
	 * @param Swift_Message The message to send (leave out the recipient headers unless you are deliberately overriding them)
	 * @param Swift_RecipientList The addresses to send to
	 * @param Swift_Address The address the mail is from (sender)
	 * @return int The number of successful recipients
	 * @throws Swift_Connection_Exception If the entire batch fails
	 */
	function batchSend(&$message, &$to, $from)
	{
		if (!is_a($message, "Swift_Message"))
		{
			trigger_error("Swift::batchSend expects parameter 1 to be instance of Swift_Message.");
			return;
		}
		if (!is_a($to, "Swift_RecipientList"))
		{
			trigger_error("Swift::batchSend expects parameter 2 to be instance of Swift_RecipientList.");
			return;
		}
		$failed = 0;
		$needs = count($to->getTo());
		foreach ($to->getTo() as $address)
		{
			if (!$this->send($message, $address, $from)) {
				$failed++;
			}
		}
		return ($needs - $failed);
	}
}
