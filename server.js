// server.js
const WebSocket = require('ws');
const wss = new WebSocket.Server({ port: 8080 });

const clients = {}; // { username: ws }

wss.on('connection', (ws) => {
    console.log('A user connected');

    ws.on('message', (message) => {
        let data;
        try {
            data = JSON.parse(message);
        } catch (e) {
            console.error('Invalid JSON:', message);
            return;
        }

        const { type, username, from, to, offer, answer, candidate } = data;

        switch (type) {
            // Register user
            case 'register':
                clients[username] = ws;
                ws.username = username;
                console.log(`Registered: ${username}`);
                break;

            // Caller initiates call
            case 'call':
                if (clients[to]) {
                    clients[to].send(JSON.stringify({
                        type: 'incoming_call',
                        from
                    }));
                    console.log(`${from} is calling ${to}`);
                }
                break;

            // Callee accepts call
            case 'call_accepted':
                if (clients[to]) {
                    clients[to].send(JSON.stringify({
                        type: 'call_accepted',
                        from: ws.username
                    }));
                    console.log(`${ws.username} accepted call from ${to}`);
                }
                break;

            // Callee declines call
            case 'call_declined':
                if (clients[to]) {
                    clients[to].send(JSON.stringify({
                        type: 'call_declined',
                        from: ws.username
                    }));
                    console.log(`${ws.username} declined call from ${to}`);
                }
                break;

            // WebRTC offer
            case 'offer':
                if (clients[to]) {
                    clients[to].send(JSON.stringify({
                        type: 'offer',
                        from: ws.username,
                        offer
                    }));
                    console.log(`${ws.username} sent offer to ${to}`);
                }
                break;

            // WebRTC answer
            case 'answer':
                if (clients[to]) {
                    clients[to].send(JSON.stringify({
                        type: 'answer',
                        from: ws.username,
                        answer
                    }));
                    console.log(`${ws.username} sent answer to ${to}`);
                }
                break;

            // ICE candidate
            case 'ice_candidate':
                if (clients[to]) {
                    clients[to].send(JSON.stringify({
                        type: 'ice_candidate',
                        from: ws.username,
                        candidate
                    }));
                    // console.log(`${ws.username} sent ICE candidate to ${to}`);
                }
                break;
                case 'call_ended':
                if (clients[to]) {
                    clients[to].send(JSON.stringify({
                        type: 'call_ended',
                        from: ws.username
                    }));
                    console.log(`${ws.username} ended the call with ${to}`);
                }
                break;
                        default:
                console.warn('Unknown message type:', type);
        }
    });

    ws.on('close', () => {
        console.log(`${ws.username} disconnected`);
        if (ws.username && clients[ws.username]) {
            delete clients[ws.username];
        }
    });

    ws.on('error', (err) => {
        console.error('WebSocket error:', err);
    });
});

console.log('WebSocket server running on ws://localhost:8080');
