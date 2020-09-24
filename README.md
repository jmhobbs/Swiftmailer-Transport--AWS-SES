# THIS PACKAGE IS DEPRECATED

This package is deprecated, you should move to the SMTP interface.  Read more in [SMTP.md](https://github.com/jmhobbs/Swiftmailer-Transport--AWS-SES/blob/master/SMTP.md)

There will be no more feature development for this package, and only critical bug fixes will be applied.

# What is it?

It's a simple transport for use with Swiftmailer to send mail over AWS SES.

As of December 2011, Amazon [provides an SMTP interface to SES](http://aws.amazon.com/ses/faqs/#21), so you may prefer to use Swiftmailer's built in SMTP transport.

# Where do I put it?

The best way to use it is through [composer](https://getcomposer.org/).

    $ composer require jmhobbs/swiftmailer-transport-aws-ses

Which will bring in Swiftmailer if you don't already have it installed.

Otherwise Swift can autoload it if you put the files in this directory:

    [swift library root]/classes/Swift/AWSTransport.php

# How do I use it?

Like any other Swiftmailer transport:

    //Create the Transport
    $transport = Swift_AWSTransport::newInstance( 'AWS_ACCESS_KEY', 'AWS_SECRET_KEY' );

    //Create the Mailer using your created Transport
    $mailer = Swift_Mailer::newInstance($transport);

    $mailer->send($message);

# Swiftmailer <= 5.x

The current branch has been update to be compatible with Swiftmailer 6.  If you need the old branch, it is availble under the 0.9.x tags, or the swiftmailer-5.x branch.

# Symfony1.X configuration

    ```yaml
    # app/frontend/config/factories.yml

    all:
      mailer:
        class: sfMailer
        param:
          transport:
            class:          Swift_AWSTransport
            accessKeyId:    your-access-key
            secretKey:      Y0uR-$3cr3t5-k3y
            debug:          false
            endpoint:       'https://email.us-east-1.amazonaws.com/' # make sure to use trailing slash !
    ```

# How do I get the message ID on send?

You need to register the Swift_Events_ResponseReceivedListener plugin with a callback.  See example/responseListener.php for details.

    $transport->registerPlugin(
    	new Swift_Events_ResponseReceivedListener( function ( $message, $body ) {
    		echo sprintf( "Message-ID %s.\n", $body->SendRawEmailResult->MessageId );
    	})
    );

# Swiftmailer Version

Please note that some users [have had issues with older versions of Swiftmailer](https://github.com/jmhobbs/Swiftmailer-Transport--AWS-SES/issues/13).

Versions 4.1.3 and up should work fine.

# Credits

* @jmhobbs - Original development
* @bertrandom - Bug fix
* @themouette - Plugins & Symfony compatible
* @jonatrey & @faz - Debugging and Testing issue #13
* @casconed - Made debug function more robust, issue #21
* @martijngastkemper - Added responseReceived event to get message id from AWS
* @weierophinney - Swiftmailer 6 support
