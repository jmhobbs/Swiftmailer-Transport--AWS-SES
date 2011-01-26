<?php
	require_once 'lib/swift_required.php';

	//Create the Transport
	$transport = new Swift_AWSTransport( 'AWS_ACCESS_KEY', 'AWS_SECRET_KEY' );

	//Create the Mailer using your created Transport
	$mailer = Swift_Mailer::newInstance($transport);

	//Create the message
	$message = Swift_Message::newInstance()
	->setSubject("What up?")
	->setFrom(array('you@yourdomain.com'))
	->setTo(array('them@theirdomain.com'))
	->setBody("<p>Dude, I'm <b>totally</b> sending you email via AWS.</p>", 'text/html')
	->addPart("Dude, I'm _totally_ sending you email via AWS.", 'text/plain');

	$mailer->send( $message );
