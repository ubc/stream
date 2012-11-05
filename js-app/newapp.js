var app 	= require('express')()
  , server 	= require('http').createServer( app)
  , io 		= require('socket.io').listen(server);

server.listen(3000); // create the server on port 3000

var user_logged_on = 0; // 

var SALT = '6cf6e971f7c125615a1ee20510c1c70f';

var blog_keys; 

app.post('/blog/:key', function( req, res ) {
  console.log(req.cookies)
  //var blog_key = ;
  
  var crypto = require('crypto');
  blog_key = req.params.key; //crypto.createHash('md5').update(  ).digest("hex");
  
  //console.log('blog with key just submitted stuff to me ' + req.params.key);
  console.log( blog_key );
  
  // the blog_key is the room id
  io.sockets.in( blog_key ).emit( 'server-push', { post: 'updated' } )
 
});

// 
io.sockets.on('connection', function (socket) {
  
  socket.on('subscribe', function(data) { socket.join(data.room); });
  
  socket.on('unsubscribe', function(data) { socket.leave(data.room); });
  
});
