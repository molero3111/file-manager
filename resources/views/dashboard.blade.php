@extends('layout')
@section('content')
    <input type="file" id="fileInput" class="d-none" onchange="uploadFile()" />
    <button id="upload-btn" class="btn btn-primary mb-3"
        onclick="document.getElementById('fileInput').click()">Upload</button>
    <button id="record-btn" class="btn btn-success mb-3" onclick="openRecorderModal()">Record Video</button>
    <div id="progress-bar-ontainer" class="progress mb-3" style="display: none;">
        <div id="uploadProgressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0"
            aria-valuemin="0" aria-valuemax="100">0%</div>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Size</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="filesTableBody">
            <!-- Files will be populated here -->
        </tbody>
    </table>
    <!-- Video Recorder Modal -->
    <div id="recorderModal"
        style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:20px; border-radius:8px; max-width:400px; margin:auto; text-align:center;">
            <video id="preview" width="320" height="240" autoplay muted style="background:#000"></video>
            <div style="margin:10px 0;">
                <button id="startRecBtn" class="btn btn-primary" onclick="startRecording()">Start</button>
                <button id="stopRecBtn" class="btn btn-danger" onclick="stopRecording()" disabled>Stop</button>
                <button class="btn btn-secondary" onclick="closeRecorderModal()">Close</button>
            </div>
            <div id="recStatus" style="margin-top:10px; color:green;"></div>
        </div>
    </div>
    <script>
        const progressBarContainer = document.getElementById('progress-bar-ontainer');
        const progressBar = document.getElementById('uploadProgressBar');
        const uploadBtn = document.getElementById('upload-btn');
        document.addEventListener('DOMContentLoaded', function () {
            if (!localStorage.getItem('token')) {
                window.location.href = '/login';
            } else {
                getFiles();
                setupWebSocket();
            }
        });

        function getFiles() {
            const token = localStorage.getItem('token');
            fetch("/api/files", {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                }
            }).then(res => res.json()).then(data => {
                if (data) {
                    const tableBody = document.getElementById('filesTableBody');
                    tableBody.innerHTML = '';
                    data.forEach(file => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                                        <td>${file.id}</td>
                                        <td>${file.name}</td>
                                        <td>${formatFileSize(file.size)}</td>
                                        <td>${file.created_at}</td>
                                        <td>
                                            <button class="btn btn-primary" onclick="downloadFile(${file.id})">Download</button>
                                            <button class="btn btn-danger" onclick="deleteFile(${file.id})">Delete</button>
                                        </td>
                                    `;
                        tableBody.appendChild(row);
                    });
                }
            });
        }

        function formatFileSize(size) {
            const gb = 1024 * 1024 * 1024;
            const mb = 1024 * 1024;
            const kb = 1024;
            if (size >= gb) {
                return (size / gb).toFixed(2) + ' GB';
            } else if (size >= mb) {
                return (size / mb).toFixed(2) + ' MB';
            } else if (size >= kb) {
                return (size / kb) + ' KB';
            } else {
                return size + ' bytes';
            }
        }

        function deleteFile(fileId) {
            const token = localStorage.getItem('token');
            fetch(`/api/files/${fileId}`, {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                }
            }).then(res => {
                if (res.ok) {
                    getFiles();
                }
            });
        }

        function downloadFile(fileId) {
            const token = localStorage.getItem('token');
            fetch(`/api/files/${fileId}/download`, {
                method: "GET",
                headers: {
                    "Authorization": `Bearer ${token}`
                }
            }).then(res => {
                if (res.ok) {
                    return res.blob();
                } else {
                    throw new Error('Download failed');
                }
            }).then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = ''; // The filename will be set by the server
                document.body.appendChild(a);
                a.click();
                a.remove();
            }).catch(error => {
                console.error(error);
            });
        }

        async function uploadFile() {
            const input = document.getElementById('fileInput');
            const file = input.files[0];
            const chunkSize = {{ config('app.file_chunk_size') }} * 1024 * 1024;
            const totalChunks = Math.ceil(file.size / chunkSize);
            const fileId = `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`; // Unique file ID for chunked uploads
            const fileName = file.name;
            const token = localStorage.getItem('token');
            uploadBtn.disabled = true;
            progressBarContainer.style.display = 'block';
            for (let i = 0; i < totalChunks; i++) {
                console.log(`Uploading chunk ${i + 1} of ${totalChunks}`);
                const start = i * chunkSize;
                const end = Math.min(start + chunkSize, file.size);
                const chunk = file.slice(start, end);

                const formData = new FormData();
                formData.append('file', chunk);
                formData.append('fileId', fileId);
                formData.append('totalChunks', totalChunks);
                formData.append('chunkIndex', i);

                await fetch('/api/files', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'X-File-ID': fileId,
                        'X-Total-Chunks': totalChunks,
                        'X-Chunk-Index': i,
                        'X-File-Name': fileName
                    }
                }).then(res => {
                    if (!res.ok) {
                        throw new Error('Chunk upload failed');
                    }
                    console.log(`Chunk ${i + 1} uploaded successfully`);
                });
            }
        }

        let socket, mediaRecorder, previewStream;
        function setupWebSocket() {
            socket = io({
                path: '/ws/socket.io',
                transports: ['websocket'],
                auth: {
                    token: localStorage.getItem('token')
                }
            });

            socket.on('connect', () => {
                console.log('WebSocket connected:', socket.id);
                // Send a healthcheck message
                socket.emit('healthcheck', { message: 'ping' });
            });

            socket.on('healthcheck-response', (data) => {
                console.log('Healthcheck response:', data);

            });

            socket.on('upload-progress', function (data) {
                console.info('Upload progress:', data);
                if (data.progress > progressBar.getAttribute('aria-valuenow')) {
                    progressBar.style.width = `${data.progress}%`;
                    progressBar.setAttribute('aria-valuenow', data.progress);
                    progressBar.innerText = `${data.progress.toFixed(2)}%`;
                    if (data.progress == 100) {
                        setTimeout(() => {
                            progressBar.style.width = `0%`;
                            progressBar.setAttribute('aria-valuenow', 0);
                            progressBar.innerText = `0%`;
                            uploadBtn.disabled = false;
                            progressBarContainer.style.display = 'none';
                            getFiles();
                        }, 2000);
                    }
                }
            });

            socket.on('disconnect', () => {
                console.log('WebSocket disconnected');
            });
        }

        function openRecorderModal() {
            document.getElementById('recorderModal').style.display = 'flex';
            document.getElementById('recStatus').innerText = '';
            document.getElementById('startRecBtn').disabled = false;
            document.getElementById('stopRecBtn').disabled = true;
            // Start camera preview
            navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then(stream => {
                    previewStream = stream;
                    document.getElementById('preview').srcObject = stream;
                });
        }

        function closeRecorderModal() {
            document.getElementById('recorderModal').style.display = 'none';
            // Stop preview stream
            if (previewStream) {
                previewStream.getTracks().forEach(track => track.stop());
                previewStream = null;
            }
        }

        function startRecording() {
            document.getElementById('startRecBtn').disabled = true;
            document.getElementById('stopRecBtn').disabled = false;
            document.getElementById('recStatus').innerText = 'Recording...';

            // Use the preview stream for recording
            mediaRecorder = new MediaRecorder(document.getElementById('preview').srcObject, { mimeType: 'video/webm' });

            mediaRecorder.ondataavailable = function (e) {
                if (e.data.size > 0) {
                    socket.emit('video-chunk', e.data);
                }
            };

            // Send "start" message with meta info
            socket.emit('video-stream', {
                action: 'start',
                file_name: 'recorded_video',
                file_type: 'video/webm',
                type: 'welcome',
                width: 320,
                height: 240
            });

            mediaRecorder.start(1000); // 1 second per chunk
        }

        function stopRecording() {
            document.getElementById('stopRecBtn').disabled = true;
            document.getElementById('recStatus').innerText = 'Finalizing...';
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
            socket.emit('video-stream', { action: 'end' });
            setTimeout(() => {
                closeRecorderModal();
            }, 1500);
        }
    </script>
@endsection