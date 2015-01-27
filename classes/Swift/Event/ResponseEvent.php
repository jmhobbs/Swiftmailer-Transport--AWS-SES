<?php

use Symfony\Component\EventDispatcher\Event;

class Swift_AWSTransport_ResponseEvent extends Event
{
    protected $message;
	protected $response;

    public function __construct( Swift_Message $message, SimpleXMLElement $response )
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
}