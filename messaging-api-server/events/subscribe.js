const Op = require('sequelize').Op
const { Message } = require('../db')
const { log, getConvoRoomName } = require('../fns')

module.exports = (io, socket) => async event => {
	log('subscribe      ', event, 'blue')

	event.user_ids.forEach(user_id => {
		let roomname = getConvoRoomName(event.id, user_id)
		socket.join(roomname)
	})
}
