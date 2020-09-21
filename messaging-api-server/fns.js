const chalk = require('chalk')
const fs = require('fs')

const getConvoRoomName = (id1, id2) => {
	let ids = [id1, id2].sort()
	return `u:${ids[0]}->u:${ids[1]}`
}

const log = (label, message, color) => {
	console.log(chalk.magenta(new Date().toISOString()), chalk[color](label), message)
	fs.appendFileSync('./chat.log', `${new Date().toISOString()} ${label} ${JSON.stringify(message)}\n`, {
		encoding: 'utf8',
	})
}

module.exports = {
	getConvoRoomName,
	log,
}
