const Sequelize = require('sequelize')
const Conn = require('../connection')

module.exports = Conn.define(
	'message_groups',
	{
		name: {
			type: Sequelize.STRING,
			allowNull: false,
		},
		building_id: {
			type: Sequelize.INTEGER,
			allowNull: false,
		},
		tenants: {
			type: Sequelize.BOOLEAN,
			allowNull: false,
		},
		owners: {
			type: Sequelize.BOOLEAN,
			allowNull: false,
		},
		managers: {
			type: Sequelize.BOOLEAN,
			allowNull: false,
		},
	},
	{
		paranoid: true,
		underscored: true,
	}
)
