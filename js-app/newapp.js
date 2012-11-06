var express = require('express');
var app = express();
var server 	= require('http').createServer( app );
var io 		= require('socket.io').listen(server);

/*
var app = require('express')()
  , server = require('http').createServer(app)
  , io = require('socket.io').listen(server);

server.listen(80);
*/
server.listen(3000); // create the server on port 3000

app.use(express.bodyParser());

var user_logged_on = 0; // 

var SALT = '6cf6e971f7c125615a1ee20510c1c70f';

var blog_keys; 

app.post('/blog/:key', function( req, res ) {
  
  var crypto = require('crypto');
  blog_key = req.params.key; //crypto.createHash('md5').update(  ).digest("hex");
 
  // the blog_key is the room id
  io.sockets.in( blog_key ).emit( 'server-push', { 'type':req.body.type, 'data': req.body.data } )
  res.send('success');
});

// 
io.sockets.on('connection', function (socket) {
  
  socket.on('subscribe', function(data) { socket.join(data.room); });
  
  socket.on('unsubscribe', function(data) { socket.leave(data.room); });
  
});
