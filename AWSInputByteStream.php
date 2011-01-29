<?php

	/**
	
	write(…) will be invoked repeatedly on the byte stream object until all bytes 
	contained in the message have been written.  The SMTP transport takes this 
	approach.  The biggest hurdle for you here appears to be that you need to 
	base64 encode the MIME data that will be streamed.  That would mean you'd 
	need to encode a number of bytes cleanly divisible by 3, buffering anything 
	left over, otherwise the result would be invalid (there'd be "=" characters in
	the middle of the base64-encoded content, instead of only at the end).  You'd
	have to manually call flushBuffers() on the stream after invoked toByteStream() 
	on the message in order to prevent any buffered bytes from being left in the stream.
	
	**/


	class AWSInputByteStream implements Swift_InputByteStream {
		
		public function __construct( $socket ) {
			$this->socket = $socket;
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
  public function write($bytes);
  
  /**
   * For any bytes that are currently buffered inside the stream, force them
   * off the buffer.
   * 
   * @throws Swift_IoException
   */
  public function commit();
  
  /**
   * Attach $is to this stream.
   * The stream acts as an observer, receiving all data that is written.
   * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
   * 
   * @param Swift_InputByteStream $is
   */
  public function bind(Swift_InputByteStream $is);
  
  /**
   * Remove an already bound stream.
   * If $is is not bound, no errors will be raised.
   * If the stream currently has any buffered data it will be written to $is
   * before unbinding occurs.
   * 
   * @param Swift_InputByteStream $is
   */
  public function unbind(Swift_InputByteStream $is);
  
  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   * @throws Swift_IoException
   */
  public function flushBuffers();

	}