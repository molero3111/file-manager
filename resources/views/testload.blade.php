<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test load</title>
</head>

<body>
    <h1>load Test</h1>

    <script>
        const requests = Array.from({ length: 10 }, (_, i) =>
            fetch("http://localhost:8080/api/test-load")
                .then(res => res.json())
                .then(data => console.log(`Response ${i + 1}:`, data))
        );

        Promise.all(requests).then(() => console.log("All requests sent!"));
    </script>
</body>

</html>