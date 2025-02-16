@extends('layout')
@section('content')
<h2>FILES</h2>
<!-- <form onsubmit="event.preventDefault(); register();">
    <input type="text" id="name" placeholder="Name" required>
    <input type="email" id="email" placeholder="Email" required>
    <input type="password" id="password" placeholder="Password" required>
    <button type="submit">Register</button>
</form> -->
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
            if (data.token) {
                localStorage.setItem("token", data.token);
                window.location.href = "/";
            }
        });
    }
</script>
@endsection