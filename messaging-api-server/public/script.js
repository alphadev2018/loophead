const form = document.querySelector('#form')
const messageList = document.querySelector('#messageList')
const textInput = document.querySelector('#textInput')
const sendBtn = document.querySelector('#sendBtn')
const title = document.querySelector('#title')

const socket = io(`https://${location.hostname}:8443`)

const mainData = JSON.parse(decodeURIComponent(location.hash.replace('#', '')))

let messages = []
let freezeScroll = false
let previousTop = 0

title.innerHTML =
	'u:' +
	mainData.from_id +
	' → ' +
	(mainData.to_id ? 'u:' + mainData.to_id : 'g:' + mainData.group_id)

const receiveMessage = message => {
	messages.push(message)

	if (!message.read && +message.to_id === +mainData.from_id) {
		socket.emit('messageRead', { id: message.id })
	}

	// this nifty piece of code determines whether we should scroll the window down or not
	let scrollDown =
		+message.from_id === +mainData.from_id ||
		messageList.scrollHeight - messageList.clientHeight === 0 ||
		messageList.scrollHeight - messageList.clientHeight <
			messageList.scrollTop + 20

	messageList.innerHTML += `
	<div class="message ${
		+message.from_id === +mainData.from_id ? 'us' : 'them'
	}" id="m-${message.id}">
		${message.is_group_message ? '<i class="fa fa-bullhorn"></i>' : ''}
		${message.content}
	</div>
	`

	let prevTopEl = document.querySelector(`#m-${previousTop}`)
	if (freezeScroll && prevTopEl) {
		messageList.scrollTop = prevTopEl.offsetTop - 40 - 20
	} else if (scrollDown) {
		messageList.scrollTop = messageList.scrollHeight
	}
}

const requestMessages = () => {
	let data = { user_id: mainData.from_id, offset: messages.length }

	if (mainData.to_id) data.to_id = mainData.to_id
	if (mainData.group_id) data.group_id = mainData.group_id

	socket.emit('requestMessages', data)
}

socket.on('connect', () => {
	console.log('connect', socket.id)
	let data = { user_id: mainData.from_id }

	if (mainData.to_id) data.to_id = mainData.to_id
	if (mainData.group_id) data.group_id = mainData.group_id

	socket.emit('clientConnected', data)
})

socket.on('serverConnected', data => {
	console.log('serverConnected', data)
	messageList.innerHTML = ''
	messages = []
	data.messages.forEach(receiveMessage)
})

socket.on('receiveMessages', data => {
	console.log('receiveMessages', data)

	previousTop = messages[0].id
	let messagesFromServer = [...data.messages, ...messages]
	messageList.innerHTML = ''
	messages = []
	freezeScroll = true
	messagesFromServer.forEach(receiveMessage)
	freezeScroll = false
})

socket.on('serverMessage', receiveMessage)

form.addEventListener('submit', e => {
	e.preventDefault()

	let data = { user_id: mainData.from_id, message: textInput.value }

	if (mainData.to_id) data.to_id = mainData.to_id
	if (mainData.group_id) data.group_id = mainData.group_id

	socket.emit('clientMessage', data)

	textInput.value = ''
})

messageList.addEventListener('scroll', e => {
	if (messageList.scrollTop === 0) {
		requestMessages()
	}
})
