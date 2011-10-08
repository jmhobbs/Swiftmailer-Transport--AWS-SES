<?php

	class AWSInputByteStream implements Swift_InputByteStream {

		public function __construct( $socket ) {
			$this->socket = $socket;
			$this->buffer = '';
			$this->counter = 0;
		}

		/**
		 * Writes $bytes to the end of the stream.
		 *
		 * Writing may not happen immediately if the stream chooses to buffer.  If
		 * you want to write these bytes with immediate effect, call {@link commit()}
		 * after calling write().
		 *
		 * This method returns the sequence ID of the write (i.e. 1 for first, 2 for
		 * second, etc etc).
		 *
		 * @param string $bytes
		 * @return int
		 */
		public function write($bytes) {

			$total_size = strlen( $this->buffer ) + strlen( $bytes );
			$excess = $total_size % 3;

			if( $total_size - $excess == 0 ) { return ++$this->counter; }

			$this->socket->write( urlencode( base64_encode( substr( $this->buffer . $bytes, 0, $total_size - $excess ) ) ) );

			if( $excess != 0 ) {
				$this->buffer = substr( $this->buffer . $bytes, -1 * $excess );
			}
			else {
				$this->buffer = '';
			}

			return ++$this->counter;
		}

		/**
		 * For any bytes that are currently buffered inside the stream, force them
		 * off the buffer.
		 */
		public function commit() {
			// NOP - Since we have a required packet offset (3-bytes), we can't commit arbitrarily.
		}

		public function flushBuffers() {
			if( strlen( $this->buffer ) > 0 ) {
				$this->socket->write( urlencode( base64_encode( $this->buffer ) ) );
			}
			$this->socket->finishWrite();
		}

		/**
		 * Attach $is to this stream.
		 * The stream acts as an observer, receiving all data that is written.
		 * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
		 *
		 * @param Swift_InputByteStream $is
		 */
		public function bind(Swift_InputByteStream $is){}

		/**
		 * Remove an already bound stream.
		 * If $is is not bound, no errors will be raised.
		 * If the stream currently has any buffered data it will be written to $is
		 * before unbinding occurs.
		 *
		 * @param Swift_InputByteStream $is
		 */
		public function unbind(Swift_InputByteStream $is){}

}
