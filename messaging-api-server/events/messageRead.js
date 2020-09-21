const { Message } = require('../db');
const { log, getConvoRoomName } = require('../fns');

module.exports = io => async event => {
	log('messageRead    ', event, 'green');

	const message = await Message.findOne({ where: { id: event.id } });

	message.read = true;
	message.save();

	io.to(getConvoRoomName(message.from_id, message.to_id)).emit('messageReadUpdate', { id: message.id, read: true })
}
