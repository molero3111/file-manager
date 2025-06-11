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

io.on('connection', (socket) => {
    // Register event handlers
    socket.onAny((event, ...args) => {
        console.log('Received event:', event, args);
    });

    socket.on('healthcheck', (msg) => {
        console.log('Healthcheck received:', msg);
        socket.emit('healthcheck-response', { status: 'ok', received: msg });
    });

    socket.on('disconnect', () => {
        console.log('Client disconnected:', socket.id);
    });

    // Authentication
    validateSanctumToken(socket.handshake.auth.token)
    .then(isAuthenticated => {
        if (!isAuthenticated) {
            console.log('Invalid token, disconnecting:', socket.id, socket.handshake.auth.token);
            return socket.disconnect(true);
        }
        console.log('Authenticated client:', socket.id);
    }).catch(err => {
        console.error('Error validating token:', err);
        socket.disconnect(true);
    });
});

const PORT = process.env.WS_PORT || 3001;
server.listen(PORT, () => {
    console.log(`WebSocket server listening on port ${PORT}`);
});
