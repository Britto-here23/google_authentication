

<!DOCTYPE html>
<html>
<head>
    <title>Login with Google</title>
</head>
<body style="width: 100vw;height:100vh; display:flex;justify-content:center;align-items:center">
    <a href="{{ route('google.redirect') }}">
        <img src="{{ asset('images/google.png') }}" alt="Login with Google">
    </a>
</body>
</html>
