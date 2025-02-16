@extends('layout')

@section('content')
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg" style="width: 350px;">
            <h2 class="text-center">Register</h2>
            <form onsubmit="event.preventDefault(); register();">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" class="form-control" placeholder="Enter your name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Register</button>
            </form>
        </div>
    </div>

    <script>
        function register() {
            fetch("/api/register", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    name: document.getElementById("name").value,
                    email: document.getElementById("email").value,
                    password: document.getElementById("password").value
                })
            }).then(res => res.json()).then(data => {
                console.log(data);
                if (data.access_token) {
                    localStorage.setItem("token", data.access_token);
                    showToast("Registration successful! Redirecting...", 0, 1); // Success
                    setTimeout(() => {
                        window.location.href = "/dashboard";
                    }, 2000);
                } else {
                    showToast("Registration failed! Please try again."); // Error
                }
            }).catch(() => showToast("An error occurred! Please try again.")); // Error
        }
    </script>
@endsection