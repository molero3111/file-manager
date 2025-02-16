@extends('layout')
@section('content')
<h2>FILES</h2>
<input type="file" id="fileInput" class="d-none" onchange="uploadFile()" />
<button class="btn btn-primary mb-3" onclick="document.getElementById('fileInput').click()">Upload</button>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        getFiles();
    });

    function getFiles() {
        fetch("/api/files", {
            method: "GET",
            headers: { "Content-Type": "application/json" }
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
        fetch(`/api/files/${fileId}`, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" }
        }).then(res => {
            if (res.ok) {
                getFiles();
            }
        });
    }

    function uploadFile() {
        const input = document.getElementById('fileInput');
        const file = input.files[0];
        const formData = new FormData();
        formData.append('file', file);
        fetch('/api/files', {
            method: 'POST',
            body: formData
        }).then(res => {
            if (res.ok) {
                getFiles();
            }
        });
    }
</script>
@endsection