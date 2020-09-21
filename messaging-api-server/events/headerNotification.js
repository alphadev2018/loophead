const { log } = require('../fns')

module.exports = (io, socket) => async event => {
    log('notification', event, 'blue')

    if (event.id) {
        socket.join(event.id)

    }

}