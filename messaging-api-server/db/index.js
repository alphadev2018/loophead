const Conn = require('./connection')
const User = require('./models/user')
const Message = require('./models/message')
const MessageGroup = require('./models/messageGroup')

User.hasMany(Message, { foreignKey: 'from_id', as: 'messagesSent' })
Message.belongsTo(User, { foreignKey: 'from_id', as: 'from' })

User.hasMany(Message, { foreignKey: 'to_id', as: 'messagesReceived' })
Message.belongsTo(User, { foreignKey: 'to_id', as: 'to' })

// MessageGroup.hasMany(MessageGroupFloor)

module.exports = {
	Conn,
	User,
	Message,
	MessageGroup,
}
