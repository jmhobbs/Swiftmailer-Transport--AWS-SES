<?php

class Swift_Response_AWSResponse {
	
	protected $message;
	
	protected $response;
	
	public function __construct( Swift_Mime_Message $message, $response = null )
	{
		$this->message = $message;
		$this->response = $response;
	}
	
	function getMessage()
	{
		return $this->message;
	}

	function getResponse()
	{
		return $this->response;
	}

	function setMessage( $message )
	{
		$this->message = $message;
		return $this;
	}

	function setResponse( $response )
	{
		$this->response = $response;
		return $this;
	}
}
