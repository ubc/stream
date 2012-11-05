
var app = require('express')()
  , server = require('http').createServer(app)
  , io = require('socket.io').listen(server);

server.listen(3000);

app.get('/', function (req, res) {
  res.sendfile(__dirname + '/index.html'); // nothing to see here
});


app.post('/blog/:key', function(req, res){
	
  console.log('blog with key just submitted stuff to me ' + req.params.key);

  // send the response to the rest of the other users  
});

var user_logged_on = 0;
io.sockets.on('connection', function (socket) {
  user_logged_on +=1;
  
  console.log('someone connected | number of users connected: ' + user_logged_on);
  
  socket.broadcast.emit({clients:user_logged_on}); // this doesn't seem to do anything
  
  socket.on('message', function () { return {
        that: 'only'
      , '/chat': 'will get'
    };
    });
  
  socket.on('disconnect', function(){clientDisconnect(socket)});

  // this gets passed when a person visuts the iste
  /*
  var tweets = setInterval(function () {
      
      socket.emit('stream', tweet);
		tweet++;
  }, 1000); // count up every second
  */
  // socket.emit('stream', { hello: 'world' });
  /*socket.on('my other event', function (data) {
  	
    console.log(data);
  });
  */
});

function clientDisconnect(socket){
  user_logged_on -=1;
  socket.broadcast.emit({clients:user_logged_on})

}