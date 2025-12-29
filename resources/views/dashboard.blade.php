<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard Aplikasi 2</h1>
    <p>Login berhasil via SSO</p>

    <p>User: {{ auth()->user()->name }}</p>

    <a href="/logout">Logout</a>
</body>
</html>
