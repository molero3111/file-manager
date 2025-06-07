<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.socket.io/4.8.1/socket.io.min.js" integrity="sha384-mkQ3/7FUtcGyoppY6bz/PORYoGqOl7/aSUMn2ymDOJcapfS6PHqxhRTMh1RR0Q6+" crossorigin="anonymous"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const token = localStorage.getItem("token");
            const navLinks = document.getElementById("nav-links");

            if (token) {
                navLinks.innerHTML = `
                    <li class="nav-item">
                        <a href="#" class="nav-link text-danger" onclick="logout()">Logout</a>
                    </li>`;
            } else {
                navLinks.innerHTML = `
                    <li class="nav-item">
                        <a href="/login" class="nav-link">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="/register" class="nav-link">Register</a>
                    </li>`;
            }
        });

        function logout() {
            const token = localStorage.getItem("token");
            if (!token) return;
            localStorage.removeItem("token");
            fetch("/api/logout", {
                method: "POST",
                headers: { "Authorization": `Bearer ${token}`, "Content-Type": "application/json" },
            }).then(() => {
                window.location.href = "/login";
            }).catch(error => console.error("Logout failed", error));
        }

        function showToast(message, timeout = 0, type = 0,) {
            const toastContainer = document.getElementById("toast-container");

            const toastElement = document.createElement("div");
            toastElement.classList.add("toast");
            toastElement.setAttribute("role", "alert");
            toastElement.setAttribute("aria-live", "assertive");
            toastElement.setAttribute("aria-atomic", "true");

            // Set the toast style based on the type (1 = success, 0 = error)
            if (type === 1) {
                toastElement.classList.add("toast-success");
            } else {
                toastElement.classList.add("toast-error");
            }

            toastElement.innerHTML = `
                <div class="toast-header">
                    <strong class="me-auto">${type === 1 ? "Success" : "Error"}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">${message}</div>
            `;

            toastContainer.appendChild(toastElement);

            const toast = new bootstrap.Toast(toastElement);
            toast.show();

            if (timeout > 0) {
                setTimeout(() => {
                    toastElement.remove();
                }, timeout);
            }
        }
    </script>
</head>

<body>

    <!-- Bootstrap Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">File Manager</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto" id="nav-links">
                    <!-- Dynamic nav links will be inserted here -->
                </ul>
            </div>
        </div>
    </nav>


    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Toast Notifications -->
        <div class="toast-container position-fixed top-0 end-0 p-3" id="toast-container"></div>
        @yield('content')
    </div>

</body>

</html>