const {Message} = require('../db')
const {getConvoRoomName, log} = require('../fns')
const format = require('date-fns/format')
const addMinutes = require('date-fns/add_minutes')

module.exports = io => async event => {
    log('clientMessage  ', event, 'red')

    let now = new Date()
    let formattedDate = format(addMinutes(now, now.getTimezoneOffset()), 'YYYY-MM-DD HH:mm:ss')

    const message = await Message.create({
        content: event.message,
        from_id: event.user_id,
        to_id: event.to_id,
        building_id: event.building_id,
        firebase_token: event.firebase_token,
        created_at: formattedDate,
    })
    io.to(getConvoRoomName(event.user_id, event.to_id)).emit('serverMessage', {
        ...message.dataValues,
        created_at: formattedDate.split(' ').join('T') + 'Z',
    })
    io.to(event.to_id).emit('serverNotification', {
        ...message.dataValues,
        created_at: formattedDate.split(' ').join('T') + 'Z',
    })
}
