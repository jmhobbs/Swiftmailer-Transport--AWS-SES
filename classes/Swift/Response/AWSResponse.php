<?php

class Swift_Response_AWSResponse {
	/**
	 * @var Swift_Mime_SimpleMessage
	 */
	protected $message;

	/**
	 * @var null|SimpleXMLElement
	 */
	protected $body;

	/**
	 * @var bool
	 */
	protected $success;

	/**
	 * @var null|Aws\SesV2\Exception\SesV2Exception
	 */
	protected $exception;

	/**
	 * @var null|string
	 */
	protected $message_id;

	/**
	 * Swift_Response_AWSResponse constructor.
	 *
	 * @param Swift_Mime_SimpleMessage $message
	 * @param null $body DEPRECATED
	 * @param bool $success
	 * @param null $exception Exception returned from SES
	 */
	public function __construct( Swift_Mime_SimpleMessage $message, $body = null, $success = false, $exception = null, $message_id = null )
	{
		$this->message = $message;
		$this->body = null;
		$this->success = $success;
		$this->exception = $exception;
		$this->message_id = $message_id;
	}

	/**
	 * @return string
	 */
	function __toString()
    	{
		if(!$this->getBody())
			return "No response body available.";

		//success
		if($this->getBody()->ResponseMetadata)
			return "Success! RequestId: " . $this->getBody()->ResponseMetadata->RequestId;

		//failure
		if($this->getBody()->Error && $this->getBody()->Error->Message)
			return (string) $this->getBody()->Error->Message;

		return "Unknown Response";
    	}

	/**
	 * @return Swift_Mime_SimpleMessage
	 */
	function getMessage()
	{
		return $this->message;
	}

	/**
	 * @return null|SimpleXMLElement
	 */
	function getBody()
	{
		return $this->body;
	}

	/**
	 * @return null|Aws\SesV2\Exception\SesV2Exception
	 */
	public function getException() {
		return $this->exception;
	}

	/**
	 * @return null|string
	 */
	public function getMessageID() {
		return $this->message_id;
	}

	/**
	 * @return bool
	 */
	public function isSuccess()
	{
		return $this->success;
	}

	/**
	 * @param $message
	 *
	 * @return $this
	 */
	function setMessage( $message )
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * @param $body
	 *
	 * @return $this
	 */
	function setBody( $body )
	{
		$this->body = $body;
		return $this;
	}

	/**
	 * @param bool $success
	 *
	 * @return Swift_Response_AWSResponse
	 */
	public function setSuccess( $success )
	{
		$this->success = $success;
		return $this;
	}

}
