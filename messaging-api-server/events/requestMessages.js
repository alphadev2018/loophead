const Op = require('sequelize').Op
const { Message } = require('../db')
const { log } = require('../fns')

module.exports = (_, socket) => async event => {
	log('requestMessages', event, 'cyan')

	let messages = []

	if (event.to_id) {
		messages = await Message.findAll({
			where: {
				from_id: { [Op.or]: [event.user_id, event.to_id] },
				to_id: { [Op.or]: [event.user_id, event.to_id] },
			},
			order: [['created_at', 'desc'], ['id', 'desc']],
			limit: 20,
			offset: event.offset,
		})
	}

	socket.emit('receiveMessages', { messages: messages.reverse() })
}
