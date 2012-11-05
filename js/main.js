if( typeof io != "undefined" ) {
	console.log('node is a go', STREAM);
	
	var socket = io.connect( STREAM.url );
	socket.on('connect', function () {
    	socket.emit( 'subscribe', { 'room': STREAM.blog_key } );
  	});
	
  	socket.on('server-push', function (data) {
   	 console.log(data);
  });
} // end of node stuff




 

function update_logged_users(msg) {
	console.log(msg);
}