
if ( typeof io != "undefined" ) {
	var CTLT_Stream = io.connect( STREAM.url ); // CTLT_Stream is what stream in socket.io would be
	
	// Subscribe to this blog's stream
	CTLT_Stream.on( 'connect', function () {
    	CTLT_Stream.emit( 'subscribe', { 'room': STREAM.blog_key } );
  	} ); 
} // end of node stuff


	
	

