const Sequelize = require('sequelize')
const Conn = require('../connection')
const format = require('date-fns/format')
const addMinutes = require('date-fns/add_minutes')

module.exports = Conn.define(
	'messages',
	{
		content: {
			type: Sequelize.STRING,
			allowNull: false,
		},
		from_id: {
			type: Sequelize.INTEGER,
			allowNull: false,
		},
		channel_id: {
			type: Sequelize.INTEGER,
			allowNull: false,
		},
		read: {
			type: Sequelize.INTEGER,
			default: 0,
		},
		notified: {
			type: Sequelize.INTEGER,
			default: 0,
		},
		created_at: {
			type: Sequelize.DATE,
			default: format(addMinutes(new Date(), new Date().getTimezoneOffset()), 'YYYY-MM-DD HH:mm:ss'),
		},
	},
	{
		paranoid: true,
		underscored: true,
		timestamps: false,
	}
)
