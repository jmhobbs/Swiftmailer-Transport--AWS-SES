# Converting To The SES SMTP Interface

The AWS SES Transport has been deprecated, and the recommended change is to use the SMTP interface to SES,
with the SMTP transport that is included with Swift Mailer.

Information about the AWS SES SMTP interface can be found here: https://docs.aws.amazon.com/ses/latest/DeveloperGuide/send-email-smtp.html

## Getting Your SMTP Credentials

To send mail with SMTP, you will need your SES credentials.  These are not the AWS access key and secret, they are an independent set of credentials.

To learn more about them, and how to generate them view the AWS documentation here: https://docs.aws.amazon.com/ses/latest/DeveloperGuide/smtp-credentials.html

## Configuring SwiftMailer for SES SMTP

Once you have your SMTP credentials, you may replace your `Swift_AWSTransport` instances with a `Swift_SmtpTransport` instance.

Again, the `$username` and `$password` here are the SMTP credentials from above, not your AWS access key and secret.

```php
$transport = (new Swift_SmtpTransport('email-smtp.us-east-1.amazonaws.com', 25, 'tls'))
    ->setUsername($username)
		->setPassword($password);

$mailer = new Swift_Mailer($transport);
```

There are alternative hostnames, by region, that you can use, and there are alternative ports if you need them. Refer to the AWS documentation for these details.

More details about the SMTP transport can be found in the Swift Mailer documentation: https://swiftmailer.symfony.com/docs/sending.html#the-smtp-transport
