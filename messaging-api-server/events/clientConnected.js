const Op = require('sequelize').Op
const { Message } = require('../db')
const { getConvoRoomName, log } = require('../fns')

module.exports = (_, socket) => async event => {
	log('clientConnected', event, 'yellow')

	// socket.leaveAll()

	if (event.to_id) {
		let roomname = getConvoRoomName(event.user_id, event.to_id)
		socket.join(roomname)
	}

	let messages = []

	if (event.to_id) {
		messages = await Message.findAll({
			where: {
				from_id: { [Op.or]: [event.user_id, event.to_id] },
				to_id: { [Op.or]: [event.user_id, event.to_id] },
			},
			order: [['created_at', 'desc'], ['id', 'desc']],
			limit: 20,
		})
	}

	socket.emit('serverConnected', { messages: messages.reverse() })
}
