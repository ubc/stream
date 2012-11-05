var events = require('events');
var util = require('util');
var data = "BATMAN"


// The Thing That Emits Event
Eventer = function(){
  events.EventEmitter.call(this);
 
  this.kapow = function(key){
    this.emit('blamo', key);
  }

  this.bam = function(){
     this.emit("boom");
  }
 };
util.inherits(Eventer, events.EventEmitter);

// The thing that listens to, and handles, those events
Listener = function(){
  this.blamoHandler =  function(data){
    console.log("** blamo event handled");
    console.log(data);
  },
  this.boomHandler = function(data){
    console.log("** boom event handled");
  }

};

// The thing that drives the two.
var eventer = new Eventer();
var listener = new Listener(eventer);
eventer.on('blamo', listener.blamoHandler);
eventer.on('boom', listener.boomHandler);

eventer.kapow('123');
eventer.bam();