const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const { validateSanctumToken } = require('./src/utility');

require('dotenv').config({ path: './.env' });

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*"
    }
});

io.on('connection', async (socket) => {
    // Validate token with Laravel API
    if (!await validateSanctumToken(socket.handshake.auth.token)) {
        console.log('Invalid token, disconnecting:', socket.id, socket.handshake.auth.token);
        socket.disconnect(true);
        return;
    }

    console.log('Authenticated client:', socket.id);

    socket.on('healthcheck', (msg) => {
        console.log('Healthcheck received:', msg);
        socket.emit('healthcheck-response', { status: 'ok', received: msg });
    });

    socket.on('disconnect', () => {
        console.log('Client disconnected:', socket.id);
    });
});

const PORT = process.env.WS_PORT || 3001;
server.listen(PORT, () => {
    console.log(`WebSocket server listening on port ${PORT}`);
});
