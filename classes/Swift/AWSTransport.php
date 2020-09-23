<?php
	/*
	* This file requires SwiftMailer.
	* (c) 2011 John Hobbs
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	use Aws\Credentials\Credentials;
	use Aws\SesV2\SesV2Client;
	use Aws\SesV2\Exception\SesV2Exception;

	/**
	* Sends Messages over AWS.
	* @package Swift
	* @subpackage Transport
	* @author John Hobbs
	*/
	class Swift_AWSTransport extends Swift_Transport_AWSTransport {
		/** aws-sdk-php client */
		private $client;
		/** the service endpoint */
		private $endpoint;
		/** be persistent? **/
		private $persistent;
		/**
		 * Debugging helper.
		 *
		 * If false, no debugging will be done.
		 * If true, debugging will be done with error_log.
		 * Otherwise, this should be a callable, and will recieve the debug message as the first argument.
		 *
		 * @seealso Swift_AWSTransport::setDebug()
		 */
		private $debug;
		/** the response */
		private $response;
		/** the raw socket */
		private $fp;

		/**
		* Create a new AWSTransport.
		* @param string $AWSAccessKeyId Your access key.
		* @param string $AWSSecretKey Your secret key.
		* @param boolean $debug Set to true to enable debug messages in error log.
		* @param string $endpoint The AWS endpoint to use.
		* @param boolean $persistent DEPRECATED
		* @param string $region The AWS region to use.
		*/
		public function __construct($AWSAccessKeyId = null , $AWSSecretKey = null, $debug = false, $endpoint = null, $persistent = false, $region = "us-east-1") {
			call_user_func_array(
				array($this, 'Swift_Transport_AWSTransport::__construct'),
				Swift_DependencyContainer::getInstance()
					->createDependenciesFor('transport.aws')
				);

			$clientConfig = [
				'credentials' => new Aws\Credentials\Credentials($AWSAccessKeyId, $AWSSecretKey),
				'region' => $region,
				'version' => '2019-09-27',
			];

			if(!is_null($endpoint)) {
				$clientConfig['endpoint'] = $endpoint;
			}

			$this->client = new Aws\SesV2\SesV2Client($clientConfig);

			$this->endpoint = $endpoint;
			$this->debug = $debug;
			$this->persistent = $persistent;
		}

		public function __destruct() {
			if( $this->fp ) {
				@fclose( $this->fp );
			}
		}

		/**
		* Create a new AWSTransport.
		* @param string $AWSAccessKeyId Your access key.
		* @param string $AWSSecretKey Your secret key.
		*/
		public static function newInstance( $AWSAccessKeyId , $AWSSecretKey ) {
			return new Swift_AWSTransport( $AWSAccessKeyId , $AWSSecretKey );
		}

		public function setAccessKeyId($val) {
			$this->AWSAccessKeyId = $val;
		}

		public function setSecretKey($val) {
			$this->AWSSecretKey = $val;
		}

		public function setDebug($val) {
			$this->debug = $val;
		}

		// DEPRECATED
		public function setEndpoint($val) {
			throw new Exception("Deprecated: Please set endpoint when constructing the transport.");
		}

		// DEPRECATED
		public function setPersistent($val) {
			throw new Exception("Deprecated: Persistent connections no longer possible.");
		}

		public function getResponse() {
			return $this->response;
		}

		protected function _debug ( $message ) {
			if ( true === $this->debug ) {
				error_log( $message );
			} elseif ( is_callable($this->debug) ) {
				call_user_func( $this->debug, $message );
			}
		}

		/**
		* Send the given Message.
		*
		* Recipient/sender data will be retreived from the Message API.
		* The return value is the number of recipients who were accepted for delivery.
		*
		* @param Swift_Mime_SimpleMessage $message
		* @param string[] &$failedRecipients to collect failures by-reference
		* @return int
		* @throws AWSConnectionError
		*/
		public function send( Swift_Mime_SimpleMessage $message, &$failedRecipients = null ) {

			if ($evt = $this->_eventDispatcher->createSendEvent($this, $message))
			{
				$this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
				if ($evt->bubbleCancelled())
				{
					return 0;
				}
			}

			$success = true;
			$exception = null;
			$message_id = null;
			try
			{
				$this->response= $this->client->sendEmail([
					'Content' => [
						'Raw' => [
							'Data' => $message->toString(),
						]
					]
				]);
				$this->_debug("=== AWS Message ID: {$this->response['MessageId']}");
				$message_id = $this->response['MessageId'];
			}
			catch( Aws\SesV2\Exception\SesV2Exception $e) {
				$this->_debug("=== Sending Exception ===");
				$this->_debug("$e");
				$this->_debug("=== / Sending Exception ===");
				$success = false;
				$exception = $e;
			}

			if ($respEvent = $this->_eventDispatcher->createResponseEvent($this, new Swift_Response_AWSResponse( $message, null, $success, $exception, $message_id ), $success))
				$this->_eventDispatcher->dispatchEvent($respEvent, 'responseReceived');

			if ($evt)
			{
				$evt->setResult($success ? Swift_Events_SendEvent::RESULT_SUCCESS : Swift_Events_SendEvent::RESULT_FAILED);
				$this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
			}

			if( $success ) {
				return count((array) $message->getTo());
			}
			else {
				return 0;
			}
		}

		public function ping() {
			return true;
		}

		public function isStarted() {}
		public function start() {}
		public function stop() {}

		/**
		 * Register a plugin.
		 *
		 * @param Swift_Events_EventListener $plugin
		 */
		public function registerPlugin(Swift_Events_EventListener $plugin)
		{
			$this->_eventDispatcher->bindEventListener($plugin);
		}

	} // AWSTransport
