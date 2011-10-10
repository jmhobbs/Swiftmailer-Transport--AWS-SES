<?php
	require_once 'lib/swift_required.php';
	require_once 'classes/Swift/Transport/AWSTransport.php';
	require_once 'classes/Swift/AWSTransport.php';
	require_once 'classes/Swift/AWSInputByteStream.php';

	define( 'AWSAccessKeyId', 'YOUR_ACCESS_KEY' );
	define( 'AWSSecretKey', 'YOUR_SECRET_KEY' );

	//Create the Transport
	$transport = Swift_AWSTransport::newInstance( AWSAccessKeyId, AWSSecretKey );
	$transport->setDebug( true ); // Print's the response from AWS for debugging.

	//Create the Mailer using your created Transport
	$mailer = Swift_Mailer::newInstance( $transport );

	//Create the message
	$message = Swift_Message::newInstance()
	->setSubject( 'What up?' )
	->setFrom( array( 'from@domain.com' ) )
	->setTo( array( 'to@domain.com' ) )
	->setBody( "<p>Dude, I'm <b>totally</b> sending you email via AWS.</p>", 'text/html' )
	->addPart( "Dude, I'm _totally_ sending you email via AWS.", 'text/plain' );

	echo "Sent: " . $mailer->send( $message ) . "\n";
