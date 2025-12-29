<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard</h1>
    <p>Login berhasil via SSO</p>

    <p>User: {{ auth()->user()->name }}</p>

    <a href="/logout">Logout</a>
</body>
</html>
