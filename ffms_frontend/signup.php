<?php
if (isset($_POST['signup'])) {
    $fullname = $_POST['fullname'] ?? '';
    $username = $_POST['username'] ?? '';
    $email    = $_POST['email'] ?? '';
    $phone    = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    $apiUrl = "http://127.0.0.1:8000/api/register"; // Laravel API endpoint

    $data = [
        "fullname" => $fullname,
        "username" => $username,
        "email"    => $email,
        "phone"    => $phone,
        "password" => $password,
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['message'])) {
        echo "<p style='color:green;'>✅ " . htmlspecialchars($result['message']) . "</p>";
    } else {
        echo "<p style='color:red;'>❌ Signup failed.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Worker Signup</title>
</head>
<body>
    <h2>Worker Signup</h2>
    <form method="POST">
        <input type="text" name="fullname" placeholder="Full Name" required><br><br>
        <input type="text" name="username" placeholder="Username" required autocomplete="username"><br><br>
        <input type="email" name="email" placeholder="Email Address" required><br><br>
        <input type="text" name="phone" placeholder="Phone Number"><br><br>
        <input type="password" name="password" placeholder="Password" required autocomplete="new-password"><br><br>
        <button type="submit" name="signup">Sign Up</button>
    </form>
</body>
</html>
