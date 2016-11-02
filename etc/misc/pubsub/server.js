var fs = require('fs');
require('dotenv').config();

var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);

if (process.env.MODE == "HTTPS") {
    var andscred = {
        key: fs.readFileSync(process.env.SSL_KEY),
        cert: fs.readFileSync(process.env.SSL_CERT)
    }
    var https = require('https').Server(andscred, app);
    var io = require('socket.io')(https);
}


var redis = require("redis")
    , subscriber = redis.createClient()
    , publisher  = redis.createClient();

subscriber.psubscribe('*');

subscriber.on("pmessage", function(pchannel, channel, message) {
    console.log("Message '" + message + "' on channel '" + channel + "' arrived!")
    io.emit(channel, message);
});

io.on('connection', function(socket){
    console.log('New connection from ' + socket.request.connection.remoteAddress);
    socket.on('disconnect', function(socket) {
        console.log( 'a user disconnected' );
    });
});

if (process.env.MODE == "HTTP") {
    http.listen(process.env.PORT, function(){
      console.log('http on '+process.env.PORT);
    });
} else if (process.env.MODE == "HTTPS") {
    https.listen(process.env.PORT, function() {
        console.log( 'https on '+process.env.PORT );
    });
}

