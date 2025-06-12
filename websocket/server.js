const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const fs = require('fs');
const path = require('path');
const { validateSanctumToken } = require('./src/utility');

require('dotenv').config({ path: './.env' });

const activeStreams = {}; // { socket.id: { writeStream, filePath, ... } }
const app = express();
app.use(express.json());
app.post('/api/emit', (req, res) => {
    const { event, userId, data } = req.body;
    if (!event || !userId || !data) {
        return res.status(400).json({ status: 'error', message: 'Missing event, userId, or data' });
    }
    io.to(userId).emit(event, data);
    if (process.env.NODE_ENV !== 'production') {
        console.log(`Emitted event "${event}" to user ${userId} with data:`, data);
    }
    res.json({ status: 'ok' });
});

// Create HTTP server and Socket.IO instance
const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*" //TODO: restrict this using environment variables
    }
});

io.on('connection', (socket) => {
    // Register event handlers
    // socket.onAny((event, ...args) => {
    //     console.log('Received event:', event, args);
    // });

    socket.on('healthcheck', (msg) => {
        console.log('Healthcheck received:', msg);
        socket.emit('healthcheck-response', { status: 'ok', received: msg });
    });

    socket.on('video-stream', (msg) => {
        if (msg.action === 'start') {
            // Set upload directory relative to websocket/server.js
            const uploadDir = path.join(__dirname, './storage');
            // Ensure directory exists
            if (!fs.existsSync(uploadDir)) {
                fs.mkdirSync(uploadDir, { recursive: true });
            }
            const fileId = `${Date.now()}_${socket.id}`;
            const fileExt = msg.file_type.split('/')[1] || 'webm';
            const filePath = path.join(uploadDir, `${fileId}.${fileExt}`);
            const writeStream = fs.createWriteStream(filePath);
            activeStreams[socket.id] = { writeStream, filePath, meta: msg };
        } else if (msg.action === 'end') {
            // Finalize the file
            if (activeStreams[socket.id]) {
                activeStreams[socket.id].writeStream.end();

                // TODO: send file name and extension to laravel API through http request
                
                // Respond with file URL
                socket.emit('video-uploaded', { url: `/uploads/${path.basename(activeStreams[socket.id].filePath)}` });
                // Optionally: trigger conversion job here
                delete activeStreams[socket.id];
            }
        }
    });

    socket.on('video-chunk', (chunk) => {
        // Append chunk to file
        if (activeStreams[socket.id]) {
            activeStreams[socket.id].writeStream.write(Buffer.from(chunk));
            // Optionally: emit progress here
        }
    });

    socket.on('disconnect', () => {
        // Finalize file if needed
        if (activeStreams[socket.id]) {
            activeStreams[socket.id].writeStream.end();
            delete activeStreams[socket.id];
        }
    });

    // Authentication
    validateSanctumToken(socket.handshake.auth.token)
        .then(([isAuthenticated, userId]) => {
            if (!isAuthenticated) {
                console.log('Invalid token, disconnecting:', socket.id, socket.handshake.auth.token);
                return socket.disconnect(true);
            }
            console.log('Valid token for user:', userId);
            if (userId) {
                socket.join(userId);
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
