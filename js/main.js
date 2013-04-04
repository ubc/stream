
if ( typeof io != "undefined" ) {
	var CTLT_Stream = io.connect( stream_plugin.url ); // CTLT_Stream is what stream in socket.io would be
	
	// subscribe to this blogs stream
	CTLT_Stream.on( 'connect', function () {
    	CTLT_Stream.emit( 'subscribe', { 'room': stream_plugin.blog_key } );
  	} ); 
} // end of node stuff


	
	

