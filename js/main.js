var post_template = doT.template(document.getElementById('post-cpt-single').text);

if( typeof io != "undefined" ) {
	console.log('node is a go', STREAM);
	
	var socket = io.connect( STREAM.url );
	socket.on('connect', function () {
    	socket.emit( 'subscribe', { 'room': STREAM.blog_key } );
  	});
	
	/* this part should be in the carry specific file */
  	socket.on('server-push', function (data) {
   		console.log(data);
   	if( 'post' == data.type ){
   		
   		switch( data.action ) {
   			case 'new':
   				carry_new_post( data.data );
   			break;
   			case 'update':
   				carry_update_post( data.data );
   			break;
   		}
   		
   	}
   	 
   	 
  });
} // end of node stuff

/* this part should be in the carry specific file */
function carry_update_post( data ) {
	
	jQuery( '#post-'+data.id ).replaceWith( post_template(data) ).animate( { backgroundColor:"#ffffcc" } , 10 ).animate( { backgroundColor:"#FFF" } , 1000 );
}

function carry_new_post( data ) {
	jQuery( '#content' ).prepend( post_template(data) );
	jQuery( '#post-'+data.id ).animate( { backgroundColor:"#ffffcc" } , 10 ).animate( { backgroundColor:"#FFF" } , 1000 );
}
 
	
	

