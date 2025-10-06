<?php
session_start();

// Only admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$apiBase = "http://127.0.0.1:8000/api/users"; // Laravel API base URL

// --- Approve worker ---
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $ch = curl_init("$apiBase/$id/approve");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    header("Location: approval.php");
    exit();
}

// --- Reject worker ---
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $ch = curl_init("$apiBase/$id/reject");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    header("Location: approval.php");
    exit();
}

// --- Fetch all pending/approved/rejected workers ---
$ch = curl_init("$apiBase/pending"); // API returns array of pending users
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$workers = json_decode($response, true); // directly use the array
if (!is_array($workers)) {
    $workers = []; // fallback if API fails
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Approve Workers</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        h2 { margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #555; color: #fff; }
        a { text-decoration: none; margin-right: 5px; }
        .approved { color: green; font-weight: bold; }
        .rejected { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Pending Worker Approvals</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Username</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php if (count($workers) === 0): ?>
            <tr><td colspan="5">⚠️ No pending workers found.</td></tr>
        <?php else: ?>
            <?php foreach ($workers as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['fullname']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <?php if ($row['status'] === 'pending'): ?>
                        <a href="?approve=<?= $row['id'] ?>">✅ Approve</a> | 
                        <a href="?reject=<?= $row['id'] ?>" onclick="return confirm('Reject this worker?')">❌ Reject</a>
                    <?php elseif ($row['status'] === 'approved'): ?>
                        <span class="approved">Approved</span>
                    <?php else: ?>
                        <span class="rejected">Rejected</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <br>
    <a href="dashboard.php">← Back to Dashboard</a>
</body>
</html>
