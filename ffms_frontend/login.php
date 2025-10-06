<?php
session_start();

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $apiUrl = "http://127.0.0.1:8000/api/login"; // Laravel login endpoint

    $data = [
        "username" => $username,
        "password" => $password
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    // Check HTTP response
    if (isset($result['user'])) {
        // Save session values from API response
        $_SESSION['user_id']  = $result['user']['id'];
        $_SESSION['role']     = $result['user']['role'];
        $_SESSION['fullname'] = $result['user']['fullname'];

        // Redirect based on role
        if ($result['user']['role'] === 'admin') {
            header("Location: approval.php"); // Admin dashboard
        } else {
            header("Location: worker_dashboard.php"); // Worker dashboard
        }
        exit();
    } else {
        echo "<p style='color:red;'>❌ " . htmlspecialchars($result['message'] ?? 'Login failed.') . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required autocomplete="username"><br><br>
        <input type="password" name="password" placeholder="Password" required autocomplete="current-password"><br><br>
        <button type="submit" name="login">Login</button>
    </form>
</body>
</html>
