const Sequelize = require('sequelize')

const host = process.env.DB_HOST
const user = process.env.DB_USERNAME
const pass = process.env.DB_PASSWORD === 'null' ? null : process.env.DB_PASSWORD
const db = process.env.DB_DATABASE
const dialect = 'mysql'
const logging = false //console.log
const operatorsAliases = false

module.exports = new Sequelize(db, user, pass, {
	host,
	dialect,
	logging,
	operatorsAliases,
	// timezone: 'Pacific/Auckland',
	timezone: '+00:00',
	dialectOptions: {
		dateStrings: true,
		typeCast: (field, next) => {
			if (field.type === 'TIMESTAMP') {
				var str = field.string()

				if (str) {
					return str.split(' ').join('T') + 'Z'
				}
			}
			return next()
		},
	},
})
