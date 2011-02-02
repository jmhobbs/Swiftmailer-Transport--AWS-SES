# What is it?

It's a simple transport for use with Swiftmailer to send mail over AWS SES.

# Where do I put it?

    [swift library root]/classes/Swift/AWSTransport.php

# How do I use it?

Like any other Swiftmailer transport:

    //Create the Transport
    $transport = Swift_AWSTransport::newInstance( 'AWS_ACCESS_KEY', 'AWS_SECRET_KEY' );
    
    //Create the Mailer using your created Transport
    $mailer = Swift_Mailer::newInstance($transport);
    
    $mailer->send($message);
