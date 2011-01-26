<?php
	/*
	* This file requires SwiftMailer.
	* (c) 2011 John Hobbs
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	/**
	* Sends Messages over AWS.
	* @package Swift
	* @subpackage Transport
	* @author John Hobbs
	*/
	class Swift_AWSTransport implements Swift_Transport {

		/**
		* Create a new AWSTransport.
		* @param string $AWSAccessKeyId Your access key.
		* @param string $AWSSecretKey Your secret key.
		* @param boolean $debug Set to true to enable debug messages.
		* @param string $endpoint The AWS endpoint to use.
		*/
		public function __construct($AWSAccessKeyId , $AWSSecretKey, $debug = false, $endpoint = 'https://email.us-east-1.amazonaws.com/') {
			$this->AccessKey = $AWSAccessKeyId;
			$this->SecretKey = $AWSSecretKey;
			$this->endpoint = $endpoint;
			$this->debug = $debug;
		}

		/**
		* Send the given Message.
		*
		* Recipient/sender data will be retreived from the Message API.
		* The return value is the number of recipients who were accepted for delivery.
		*
		* @param Swift_Mime_Message $message
		* @param string[] &$failedRecipients to collect failures by-reference
		* @return int
		*/
		public function send( Swift_Mime_Message $message, &$failedRecipients = null ) {
			$rendered = strval( $message );
			$encoded = base64_encode( $rendered );
			$date = date( 'D, j F Y H:i:s O' );
			if( function_exists( 'hash_hmac' ) and in_array( 'sha1', hash_algos() ) ) {
				if( $this->debug ) { echo "USING HASH_HMAC\n"; }
				$hmac = base64_encode( hash_hmac( 'sha1', $date, $this->SecretKey, true ) );
			}
			else {
				if( $this->debug ) { echo "USING RFC2104HMAC\n"; }
				$hmac = $this->calculate_RFC2104HMAC( $date, $this->SecretKey );
			}

			$date_header = "Date: " . $date;
			$auth_header = "X-Amzn-Authorization: AWS3-HTTPS AWSAccessKeyId=" . $this->AccessKey . ", Algorithm=HmacSHA1, Signature=" . $hmac;

			// Please forgive me...
			if( $this->debug ) {
				print "--[ RAW ]-----------------------------------------------\n";
				print $rendered;
				print "--[ ENCODED ]-------------------------------------------\n";
				print $encoded;
				print "\n";
				print "--[ DATE ]----------------------------------------------\n";
				print $date . "\n";
				print "--[ SECRET KEY ]----------------------------------------\n";
				print $this->SecretKey . "\n";
				print "--[ HMAC ]----------------------------------------------\n";
				print $hmac . "\n";
				print "--[ HEADER ]--------------------------------------------\n";
				print $date_header . "\n";
				print $auth_header . "\n";
				print "--[ CURL ]----------------------------------------------\n";
			}

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->endpoint);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "Action=SendRawEmail&RawMessage.Data=" . urlencode ( $encoded ) );
			curl_setopt($ch, CURLOPT_HTTPHEADER, array( $date_header, $auth_header ) );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if( $this->debug ) {
				curl_setopt($ch, CURLINFO_HEADER_OUT, 1 );
			}
			$response = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);

			if( $this->debug ) {
				print "--[ REQUEST HEADERS ]-----------------------------------\n";
				print $info['request_header'];
				print "--[ HTTP RESPONSE CODE ]--------------------------------\n";
				print $info['http_code'] . "\n";
				print "--[ RESPONSE CONTENT ]----------------------------------\n";
				print $response;
				print "--[ DONE ]----------------------------------------------\n";
			}

			/**
			* @TODO I'm sure we need code for partial failures, but I can't test
			* multiple addresses until I get production access.
			*/
			if( 200 == $info['http_code'] ) {
				return count((array) $message->getTo());
			}
			else {
				return 0;
			}
		}

		/**
		* Cribbed from php-aws - Thanks!
		* https://github.com/tylerhall/php-aws/blob/master/class.awis.php
		* (c) Tyler Hall
		* MIT License
		*/
		protected function calculate_RFC2104HMAC($data, $key) {
			return base64_encode (
				pack("H*", sha1((str_pad($key, 64, chr(0x00))
				^(str_repeat(chr(0x5c), 64))) .
				pack("H*", sha1((str_pad($key, 64, chr(0x00))
				^(str_repeat(chr(0x36), 64))) . $data))))
			);
		}

		public function isStarted() {}
		public function start() {}
		public function stop() {}
		public function registerPlugin(Swift_Events_EventListener $plugin) {}

	}
