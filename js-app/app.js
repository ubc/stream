var express = require('express');
var server 	= require('http').createServer( app );
var io 		= require('socket.io').listen(server);

var app = express();
server.listen(3000); // Create the server on port 3000
app.use(express.bodyParser());

var user_logged_on = 0;
var SALT = '6cf6e971f7c125615a1ee20510c1c70f';

app.post( '/blog/:key', function( req, res ) {
    blog_key = req.params.key; 
    console.log( blog_key );
    // the blog_key is the room id
    io.sockets.in( blog_key ).emit( 'server-push', { 'type':req.body.type, 'data': req.body.data, 'action': req.body.action } );
    
    res.send('success');
} );

/* server status request */
app.post( '/server_status', function( req, res ) {
    res.send('online');
} );

io.sockets.on( 'connection', function( socket ) {
    socket.on('subscribe', function( data ) { 
        var crypto = require('crypto');
        var hash = crypto.createHash('md5').update( data.room ).digest("hex");
        console.log( hash );
        socket.join( hash );
    });
    
    socket.on('unsubscribe', function(data) { 
        var crypto = require('crypto');
        var hash = crypto.createHash('md5').update( data.room ).digest("hex");
        socket.leave( hash );
    });
});
