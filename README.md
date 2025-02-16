# File Manager

This project is a file manager that allows users to upload files. Among its features, it includes job dispatching and Pusher events for real-time updates as files are uploaded.

## Features

- **Chunked File Uploads**: Files are sent in chunks of 200MB, allowing large file uploads and processing.
- **Job Dispatching**: Uses Laravel's job dispatching to handle file uploads and processing in the background.
- **Real-Time Updates**: Utilizes Pusher for real-time message updates during file uploads.
- **Container Replication**: Implements container replication for load balancing, using the least busy instance to handle requests.

## Running Locally

To run this project locally, follow these steps:

1. **Clone the Repository**:
    ```bash
    git clone https://github.com/molero3111/file-manager.git
    cd file-manager
    ```

2. **Copy [.env.example](http://_vscodecontentref_/1) to [.env](http://_vscodecontentref_/2)**:
    ```bash
    cp .env.example .env
    ```

3. **Build the Docker Containers**:
    ```bash
    docker-compose build
    ```

4. **Start the Docker Containers**:
    ```bash
    docker-compose up -d
    ```

5. **Fix Permissions (if needed)**:
    If you encounter errors related to permissions on the [storage](http://_vscodecontentref_/3) and [bootstrap](http://_vscodecontentref_/4) folders, run the following commands within the container:
    ```bash
    docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
    ```

6. **Restart the Containers**:
    ```bash
    docker-compose down
    docker-compose up --build -d
    ```

## Basic Usage

1. **Sign Up or Login**: Create an account or log in with your existing credentials.
2. **Upload a File**: Click on the "Upload" button, select a file, and the upload will start. You will see real-time updates on the upload progress.
3. **Delete Files**: You can delete files by clicking the "Delete" button next to the file you want to remove.

That's it! You now have a fully functional file manager with real-time updates and background processing.
