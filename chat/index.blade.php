<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KoneKT - Networking Chat</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #fcfcfc;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }
        nav {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e5e5;
            padding: 0 40px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            font-size: 26px;
            font-weight: 700;
            color: #1a1a1a;
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .nav-links a {
            color: #666666;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 0;
        }
        .nav-links a.active {
            color: #1a1a1a;
            border-bottom: 2px solid #e45d3a;
            font-weight: 600;
        }
        .nav-links a:hover {
            color: #1a1a1a;
        }
        .back-btn {
            background-color: transparent;
            color: #666666;
            padding: 8px 16px;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .back-btn:hover {
            background-color: #f5f5f5;
            color: #1a1a1a;
        }
        .main-wrapper {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .chat-container {
            display: flex;
            height: 65vh;
            background-color: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            overflow: hidden;
        }
        .sidebar {
            width: 32%;
            border-right: 1px solid #e5e5e5;
            display: flex;
            flex-direction: column;
            background-color: #ffffff;
        }
        .sidebar-header {
            padding: 20px 20px 10px 20px;
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
        }
        .search-container {
            padding: 0 20px 15px 20px;
            border-bottom: 1px solid #e5e5e5;
        }
        .search-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            outline: none;
            font-size: 13px;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
        }
        .search-input:focus {
            border-color: #064e3b;
        }
        .connections-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }
        .user-btn {
            width: 100%;
            text-align: left;
            padding: 14px 16px;
            background: none;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 4px;
            transition: background-color 0.15s ease;
        }
        .user-btn:hover {
            background-color: #f5f5f5;
        }
        .user-name {
            margin: 0;
            font-weight: 600;
            font-size: 14px;
            color: #1a1a1a;
        }
        .user-status {
            font-size: 11px;
            color: #888888;
            display: block;
            margin-top: 2px;
        }
        .chat-window {
            width: 68%;
            display: flex;
            flex-direction: column;
            background-color: #fafafa;
        }
        .chat-header {
            padding: 20px;
            background-color: #ffffff;
            border-bottom: 1px solid #e5e5e5;
            font-size: 15px;
            font-weight: 600;
            color: #666666;
        }
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .empty-state {
            text-align: center;
            color: #888888;
            margin-top: 120px;
            font-size: 14px;
        }
        .message-bubble {
            max-w: 65%;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.5;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .msg-me {
            background-color: #064e3b;
            color: #ffffff;
            align-self: flex-end;
        }
        .msg-them {
            background-color: #ffffff;
            color: #1a1a1a;
            align-self: flex-start;
            border: 1px solid #e5e5e5;
        }
        .input-area {
            padding: 20px;
            background-color: #ffffff;
            border-top: 1px solid #e5e5e5;
            display: flex;
            gap: 12px;
        }
        .chat-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            outline: none;
            font-size: 14px;
            color: #1a1a1a;
            transition: border-color 0.2s ease;
        }
        .chat-input:focus {
            border-color: #064e3b;
        }
        .send-btn {
            background-color: #064e3b;
            color: #ffffff;
            border: none;
            padding: 0 28px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s ease;
        }
        .send-btn:hover {
            background-color: #022c22;
        }
        .send-btn:disabled, .chat-input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>

    <nav>
        <div style="display: flex; align-items: center; gap: 40px;">
            <a href="/dashboard" class="logo">Kone<span style="color: #e45d3a;">K</span>T</a>
            <div class="nav-links">
                <a href="/dashboard">Dashboard</a>
                <a href="/networking-chat" class="active">Network Chat</a>
            </div>
        </div>
        <a href="/dashboard" class="back-btn">Sign out</a>
    </nav>

    <div class="main-wrapper">
        <div class="chat-container">
            <div class="sidebar">
                <div class="sidebar-header">Connections</div>
                <div class="search-container">
                    <input type="text" id="search-connections" placeholder="Search people..." class="search-input" onkeyup="filterConnections()">
                </div>
                <div id="connections-list" class="connections-list"></div>
            </div>

            <div class="chat-window">
                <div id="active-user-name" class="chat-header">Select a connection to start messaging...</div>
                <div id="message-container" class="messages-container">
                    <div class="empty-state">No active conversation.</div>
                </div>
                <div class="input-area">
                    <input type="hidden" id="current-receiver-id">
                    <input type="text" id="chat-message-input" placeholder="Type a message here..." class="chat-input" disabled>
                    <button onclick="sendMessage()" id="send-btn" class="send-btn" disabled>Send</button>
                </div>
            </div>
        </div>
    </div>

<script>
let currentReceiverId = null;
let pollInterval = null;

document.addEventListener("DOMContentLoaded", function() {
    fetch('/messages')
        .then(res => res.json())
        .then(data => {
            const listContainer = document.getElementById('connections-list');
            if (!listContainer) return;

            const messages = data.data || data;
            const uniqueUsers = {};

            messages.forEach(msg => {
                if (msg.sender && msg.sender.id != "{{ Auth::id() }}") {
                    uniqueUsers[msg.sender.id] = msg.sender.name;
                }
                if (msg.receiver && msg.receiver.id != "{{ Auth::id() }}") {
                    uniqueUsers[msg.receiver.id] = msg.receiver.name;
                }
            });

            if (Object.keys(uniqueUsers).length === 0) {
                listContainer.innerHTML = '<div class="empty-state" style="margin-top:20px; font-size:12px;">No active connections found.<br>Accept connection requests to begin chatting.</div>';
                return;
            }

            for (const [id, name] of Object.entries(uniqueUsers)) {
                const btn = document.createElement('button');
                btn.className = "user-btn";
                btn.onclick = () => loadConversation(id, name);
                btn.innerHTML = `
                    <p class="user-name">${name}</p>
                    <span class="user-status">Click to chat</span>
                `;
                listContainer.appendChild(btn);
            }
        });
});

function filterConnections() {
    const query = document.getElementById('search-connections').value.toLowerCase();
    const buttons = document.getElementsByClassName('user-btn');

    for (let i = 0; i < buttons.length; i++) {
        const nameElement = buttons[i].getElementsByClassName('user-name')[0];
        if (nameElement) {
            const nameText = nameElement.innerText.toLowerCase();
            if (nameText.includes(query)) {
                buttons[i].style.display = "";
Gamitin ang code nang may pag-iingat.
} else {
buttons[i].style.display = "none";
}
}
}
}
function loadConversation(userId, userName) {
currentReceiverId = userId;
document.getElementById('current-receiver-id').value = userId;
document.getElementById('active-user-name').innerText = userName;
document.getElementById('chat-message-input').disabled = false;
document.getElementById('send-btn').disabled = false;
fetchMessages();
if (pollInterval) clearInterval(pollInterval);
pollInterval = setInterval(fetchMessages, 3000);
}
function fetchMessages() {
if (!currentReceiverId) return;
fetch(/messages?receiver_id=${currentReceiverId})
.then(res => res.json())
.then(data => {
const container = document.getElementById('message-container');
container.innerHTML = '';
const messages = data.data || data;
if(messages.length === 0) {
container.innerHTML = 'Say hello! Start a secure conversation.';
return;
}
messages.forEach(msg => {
const isMe = msg.sender_id == "{{ Auth::id() }}";
const msgBox = document.createElement('div');
msgBox.style.display = 'flex';
msgBox.style.justifyContent = isMe ? 'flex-end' : 'flex-start';
msgBox.innerHTML = <div class="message-bubble ${isMe ? 'msg-me' : 'msg-them'}"> ${msg.content} </div>;
container.appendChild(msgBox);
});
container.scrollTop = container.scrollHeight;
});
}
function sendMessage() {
const input = document.getElementById('chat-message-input');
const msgText = input.value.trim();
if (!msgText || !currentReceiverId) return;
fetch('/messages', {
method: 'POST',
headers: {
'Content-Type': 'application/json',
'X-CSRF-TOKEN': '{{ csrf_token() }}'
},
body: JSON.stringify({
receiver_id: currentReceiverId,
content: msgText
})
})
.then(res => res.json())
.then(() => {
input.value = '';
fetchMessages();
});
}
