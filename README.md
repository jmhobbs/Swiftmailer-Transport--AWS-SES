# What is it?

It's a simple transport for use with Swiftmailer to send mail over AWS SES.

# Where do I put it?

Whereever you want, so long as you include it in your code.

Otherwise Swift can autoload it if you put the files in this directory:

    [swift library root]/classes/Swift/AWSTransport.php

# How do I use it?

Like any other Swiftmailer transport:

    //Create the Transport
    $transport = Swift_AWSTransport::newInstance( 'AWS_ACCESS_KEY', 'AWS_SECRET_KEY' );
    
    //Create the Mailer using your created Transport
    $mailer = Swift_Mailer::newInstance($transport);
    
    $mailer->send($message);

# Symfony1.X configuration

    ```yml
    # app/frontend/config/factories.yml

    all:
      mailer:
        class: sfMailer
        param:
          transport:
            accessKeyId:    your-access-key
            secretKey:      Y0uR-$3cr3t5-k3y
            debug:          false
            endpoint:       'https://email.us-east-1.amazonaws.com/' # make sure to use trailing slash !
    ```

# Credits

* @jmhobbs - Original development
* @bertrandom - Bug fix
