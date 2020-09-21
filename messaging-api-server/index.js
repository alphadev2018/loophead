require('dotenv').config({ path: '../.env' })

var fs = require('fs');
var https = require('https');
const app = require('express')();
const http = require('http').Server(app);

// var options = {
// 	hostname: 'api.properteebutler.co.nz',
// 	key: fs.readFileSync('/etc/letsencrypt/live/api.properteebutler.co.nz/privkey.pem'),
// 	cert: fs.readFileSync('/etc/letsencrypt/live/api.properteebutler.co.nz/cert.pem'),
// 	ca: fs.readFileSync('/etc/letsencrypt/live/api.properteebutler.co.nz/chain.pem')
// };

// const server =https.createServer(options, function (req, res) {
// }).listen(8443);
// const server = http.createServer().listen(8889);

const io = require('socket.io')(http);

const { log } = require('./fns')

io.on('connection', socket => {
	//log('connection     ', socket.id, 'yellow')
	
	socket.on('clientConnected', function(data) {
		console.log('connected '+data.user);
		socket.join('user'+data.user);
	})

	socket.on('joinChannel', function(param) {
		socket.join(param.channel_id);
	});
	socket.on('leaveChannel', function(param) {
		if (param.channel) {
			console.log(`leave${param.channel}`)
			socket.leave(param.channel);
		}
		if (param.user) {
			console.log(`leave${param.user}`)
			socket.leave('user'+param.user);
		}
	})

	socket.on('sendMessage', require('./events/sendMessage')(io, socket))

	// socket.on('clientConnected', require('./events/clientConnected')(io, socket))
	// socket.on('requestMessages', require('./events/requestMessages')(io, socket))
	// socket.on('clientMessage', require('./events/clientMessage')(io, socket))
	// socket.on('messageRead', require('./events/messageRead')(io, socket))
	// socket.on('subscribe', require('./events/subscribe')(io, socket))
	// socket.on('headerNotification', require('./events/headerNotification')(io, socket))
})

// server.listen(8443, { pingTimeout: 30000 })
http.listen(8889, { pingTimeout: 30000 })
console.log('Messaging service listening on *:8443')

/**
 * The following is for use in the testing phase only
 */
// const express = require('express')
// const app = express()

// app.use(express.static('./public'))

// app.listen(8889, () => console.log('HTTP server listening on *:8889'))
