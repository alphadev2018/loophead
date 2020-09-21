const {Message} = require('../db')
const format = require('date-fns/format')
const addMinutes = require('date-fns/add_minutes')

module.exports = io => async param => {
    
    let now = new Date()
    let formattedDate = format(addMinutes(now, now.getTimezoneOffset()), 'YYYY-MM-DD HH:mm:ss')

    const message = await Message.create({
        content: param.message,
        from_id: param.user,
        channel_id: param.channel.id,
        // to_id: event.to_id,
        // building_id: event.building_id,
        // firebase_token: event.firebase_token,
        created_at: formattedDate,
    })

    console.log('send message -> '+param.channel.id+' '+param.user)

    io.to(param.channel.channel_id).emit('messages', { 
        channel: param.channel.id,
        from_id: param.user,
        content: param.message
    });
    param.channel.users.map(user => io.to(`user${user.id}`).emit('notification', {}));
    
    // io.to(event.to_id).emit('serverNotification', {
    //     ...message.dataValues,
    //     created_at: formattedDate.split(' ').join('T') + 'Z',
    // })
}
