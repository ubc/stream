// Used by the doT 
var carry_post_template = doT.template(document.getElementById('post-cpt-single').text);

console.log(CTLT_Stream);
/* this part should be in the carry specific file */
if( typeof CTLT_Stream != "undefined" ) {
	console.log('hey there');
	
  	CTLT_Stream.on('server-push', function (data) {
   	if( 'post' == data.type ){
   		
   		console.log( data.data );
   		switch( data.action ) {
   			case 'new':
   				carry_new_post( data.data );
   			break;
   			case 'update':
   				carry_update_post( data.data );
   			break;
   		} // end of stream 
   		
   	} // end of if 
   	 
  }); // end of lisening to the server-push event

}// end of - CTLT_Stream wasn't found

/* this part should be in the carry specific file */
function carry_update_post( data ) {
	
	jQuery( '#post-'+data.id ).replaceWith( carry_post_template( data ) );
	jQuery( '#post-'+data.id ).animate( { backgroundColor:"#ffffcc" } , 10 ).animate( { backgroundColor:"#FFF" } , 1000 ).addClass('updated');
}

function carry_new_post( data ) {
	
	jQuery( carry_post_template(data) ).hide().prependTo( '#content' ).fadeIn("slow");
		
}
 