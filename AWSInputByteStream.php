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
		 * @throws Swift_IoException
		 */
		public function write($bytes) {
			$tsize = strlen( $this->buffer ) + strlen( $bytes );
			$excess = $tsize % 3;
			 
			if( $tsize - $excess == 0 ) { return ++$this->counter; }
			 
			echo "-------[ WRITE ]-------\n";
			echo "   Bytes Size: " . strlen( $bytes ) . "\n";
			echo "+ Buffer Size: " . strlen( $this->buffer ) . "\n";
			echo "-----------------------\n";
			echo "        Total: $tsize\n";
			echo "-    Overflow: $excess\n";
			echo "-----------------------\n";
			echo "      Writing: " . ( $tsize - $excess ) . "\n";

			$chunk = base64_encode( substr( $this->buffer . $bytes, 0, $tsize - $excess ) );
			fwrite( $this->socket, sprintf( "%x\r\n", strlen( $chunk ) ) );
			fwrite( $this->socket, $chunk . "\r\n" );
			flush( $this->socket );
			unset( $chunk );

			if( $excess != 0 ) {
				$this->buffer = substr( $this->buffer . $bytes, -1 * $excess );
			}
			else {
				$this->buffer = '';
			}
			echo "         Kept: " . strlen( $this->buffer ) . "\n";
			echo "-----------------------\n";
			echo "\n\n\n";
			return ++$this->counter;
		}
  
		/**
		 * For any bytes that are currently buffered inside the stream, force them
		 * off the buffer.
		 * 
		 * @throws Swift_IoException
		 */
		public function commit() {
			// NOP - Since we have a required packet offset (3-bytes), we can't commit arbitrarily.
		}


		public function flushBuffers() {
			echo "-------[ FLUSH ]-------\n";
			echo "Buffer Size: " . strlen( $this->buffer ) . "\n";
			echo "-----------------------\n";
			if( strlen( $this->buffer ) > 0 ) {
				$chunk = urlencode( base64_encode( $this->buffer ) );
				fwrite( $this->socket, sprintf( "%x\r\n", strlen( $chunk ) ) );
				fwrite( $this->socket, $chunk . "\r\n" );
				flush( $this->socket );
			}
			fwrite( $this->socket, "0\r\n" );
			fwrite( $this->socket, "\r\n" );
			flush( $this->socket );
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
