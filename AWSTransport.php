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
			$this->AWSAccessKeyId = $AWSAccessKeyId;
			$this->AWSSecretKey = $AWSSecretKey;
			$this->endpoint = $endpoint;
			$this->debug = $debug;
		}
		
		/**
		* Create a new AWSTransport.
		* @param string $AWSAccessKeyId Your access key.
		* @param string $AWSSecretKey Your secret key.
		*/
		public static function newInstance( $AWSAccessKeyId , $AWSSecretKey ) {
			return new Swift_AWSTransport( $AWSAccessKeyId , $AWSSecretKey );
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
			$date = date( 'D, j F Y H:i:s O' );
			if( function_exists( 'hash_hmac' ) and in_array( 'sha1', hash_algos() ) ) {
				$hmac = base64_encode( hash_hmac( 'sha1', $date, $this->AWSSecretKey, true ) );
			}
			else {
				$hmac = $this->calculate_RFC2104HMAC( $date, $this->AWSSecretKey );
			}

			$date_header = "Date: " . $date;
			$auth_header = "X-Amzn-Authorization: AWS3-HTTPS AWSAccessKeyId=" . $this->AWSAccessKeyId . ", Algorithm=HmacSHA1, Signature=" . $hmac;

			$this->doFsock( $date_header, $auth_header, $message );

			/**
			* @TODO I'm sure we need code for partial failures, but I can't test
			* multiple addresses until I get production access.
			*/
			/*if( 200 == $info['http_code'] ) {
				return count((array) $message->getTo());
			}
			else {
				return 0;
			}*/
			return 1;
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

		protected function doFsock ( $date_header, $auth_header, $message ) {

			$host = parse_url( $this->endpoint, PHP_URL_HOST );
			$path = parse_url( $this->endpoint, PHP_URL_PATH );

			$fp = fsockopen( 'ssl://' . $host , 443, $errno, $errstr, 30 );
//			$fp = fopen( 'dump.http', 'w' );
			if( ! $fp ) {
		    		echo "$errstr ($errno)\n";
			}
			else {
				echo "=======================================================\n";
				_fwrite( $fp, "POST $path HTTP/1.1\r\n" );
				_fwrite( $fp, "Host: $host\r\n" );
				_fwrite( $fp, "Content-Type: application/x-www-form-urlencoded\r\n" );
//				_fwrite( $fp, "Transfer-Encoding: chunked" );
				_fwrite( $fp, "$date_header\r\n" );
				_fwrite( $fp, "$auth_header\r\n\r\n" );
				flush( $fp );

	//			_fwrite( $fp, sprintf( "%x\r\n", 36 ) );
				_fwrite( $fp, "Action=SendRawEmail" );
				_fwrite( $fp, "&RawMessage.Data=\r\n"  );

				$ais = new AWSInputByteStream($fp);
				$message->toByteStream($ais);
				$ais->flushBuffers();


				echo "=======================================================\n";
				while( ! feof( $fp ) ) {
					echo fgets( $fp, 128 );
				}
				fclose( $fp );
				echo "=======================================================\n";
			}
		}
		
	} // AWSTransport


	function _fwrite ( $fp, $msg ) {
		fwrite( $fp, $msg );
		echo $msg;
	}
