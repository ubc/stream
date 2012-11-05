var app 	= require('express')()
  , server 	= require('http').createServer(app)
  , io 		= require('socket.io').listen(server)
  , events 	= require('events')
  , util 	= require('util');

server.listen(3000); // create the server on port 3000

var user_logged_on = 0; // 

var blog_keys; 

app.post('/blog/:key', function(req, res){
  
  var blog_key = req.params.key;
  
  console.log('blog with key just submitted stuff to me ' + req.params.key);
  
  
  /*
  io.sockets.on('connection', function ( socket ) {
  	// send the response to the rest of the other users  
  	socket.emit( blog_key, { hello: 'new_world', users: user_logged_on });
  	
  })
  */
  
});


// 
io.sockets.on('connection', function (socket) {
  
  user_logged_on +=1;
  
  socket.emit('news', { hello: 'world', users: user_logged_on });
  
  socket.on('my other event', function (data) {
    console.log(data);
  });
  
  socket.on('disconnect', function(){ clientDisconnect( socket ) });
  
  /*
  socket.on('disconnect', function () {
    clearInterval(tweets);
  });
  */
  
  
  
  /*
  var tweets = setInterval(function () {
    getBieberTweet(function (tweet) {
      socket.volatile.emit( 'bieber tweet', tweet );
    });
  }, 100);
  */
  
});

function clientDisconnect(socket){
  user_logged_on -=1;
  console.log('user disconnected');
  
  socket.emit('news', { hello: 'user disconnected', users: user_logged_on });

}

function getBieberTweet( callback ){
	callback(user_logged_on);
}

Listener = function(){
  this.blamoHandler =  function(data){
    console.log("** blamo event handled");
    console.log(data);
  },
  this.boomHandler = function(){
    console.log("** boom event handled");
  }
};


// lets try to do some events 
Eventer = function(){
  events.EventEmitter.call(this);
  this.kapow = function(){
    var data = "BATMAN"
    this.emit('blamo', data);
  }

  this.bam = function(){
     this.emit("boom");
  }
 };
util.inherits(Eventer, events.EventEmitter);


var eventer = new Eventer();
var listener = new Listener(eventer);
eventer.on('blamo', listener.blamoHandler);
eventer.on('boom', listener.boomHandler);

eventer.kapow();
eventer.bam();


