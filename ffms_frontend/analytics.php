<?php
session_start();

// Only admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Base URL of your Laravel API
$apiBase = "http://127.0.0.1:8000/api"; // <-- adjust this to your API URL

// Helper function to make GET requests
function apiGet($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// --- Total Ponds ---
$pondsData = apiGet($apiBase . "/ponds");
$totalPonds = isset($pondsData['data']) ? count($pondsData['data']) : 0;

// --- Total Workers (approved) ---
$workersData = apiGet($apiBase . "/users/workers/approved");
$totalWorkers = isset($workersData['data']) ? count($workersData['data']) : 0;

// --- Total Stocked Fish ---
$stockLogs = apiGet($apiBase . "/stocking-logs"); // Make sure your API route returns all stocking logs
$totalStocked = 0;
if (isset($stockLogs['data'])) {
    foreach ($stockLogs['data'] as $log) {
        if ($log['action_type'] === 'stock') {
            $totalStocked += $log['quantity'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    h1 { margin-bottom: 20px; }
    .card { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); width: 250px; display: inline-block; margin-right: 20px; }
    .card h2 { margin: 0 0 10px 0; }
    .card p { font-size: 1.5em; margin: 0; }
</style>
</head>
<body>
<h1>Admin Dashboard</h1>

<div class="card">
    <h2>Total Ponds</h2>
    <p><?= $totalPonds ?></p>
</div>

<div class="card">
    <h2>Total Workers</h2>
    <p><?= $totalWorkers ?></p>
</div>

<div class="card">
    <h2>Total Stocked Fish</h2>
    <p><?= $totalStocked ?></p>
</div>

</body>
</html>
