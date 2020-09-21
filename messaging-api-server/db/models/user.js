const Sequelize = require('sequelize')
const Conn = require('../connection')

module.exports = Conn.define(
	'user',
	{
		username: {
			type: Sequelize.STRING,
			allowNull: false,
		},
		firstname: {
			type: Sequelize.STRING,
			allowNull: false,
		},
		lastname: {
			type: Sequelize.STRING,
			allowNull: false,
		},
		email: {
			type: Sequelize.STRING,
			allowNull: false,
		},
		api_token: {
			type: Sequelize.STRING,
			allowNull: false,
		},
		avatar: {
			type: Sequelize.STRING,
			allowNull: false,
		},
	},
	{
		paranoid: true,
		underscored: true,
	}
)
