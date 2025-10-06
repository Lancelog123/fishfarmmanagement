<?php
session_start();

// --- Only allow worker access ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'worker') {
    header("Location: login.php");
    exit();
}

$worker_id = $_SESSION['user_id'];
$apiBase = "http://127.0.0.1:8000/api"; // Laravel API base

// --- Helper for API GET ---
function apiGet($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp, true);
}

// Fetch assigned ponds
$assignedPonds = apiGet("$apiBase/assignments/worker/$worker_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Worker Dashboard</title>
<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    background: #f5f7fa;
}
h2 {
    color: #2c3e50;
}
table {
    border-collapse: collapse;
    width: 100%;
    margin-top: 20px;
    background: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}
th {
    background-color: #3c8dbc;
    color: #fff;
}
button, a {
    padding: 6px 10px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    border-radius: 5px;
    transition: 0.2s;
}
button:hover, a:hover {
    opacity: 0.8;
}
.add-btn {
    background-color: #4CAF50;
    color: white;
}
.transfer-btn {
    background-color: #2196F3;
    color: white;
}
.harvest-btn {
    background-color: #FF9800;
    color: white;
}
.logout {
    display: inline-block;
    margin-top: 25px;
    color: #c00;
    font-weight: bold;
}
tr:hover {
    background-color: #f9f9f9;
}
</style>
</head>
<body>

<h2>👷 Worker Dashboard</h2>
<p>Here are the ponds assigned to you:</p>

<?php if (!empty($assignedPonds) && !isset($assignedPonds['message'])): ?>
<table>
<tr>
    <th>Pond ID</th>
    <th>Name</th>
    <th>Type</th>
    <th>Category</th>
    <th>Location</th>
    <th>Created At</th>
    <th>Actions</th>
</tr>

<?php foreach ($assignedPonds as $assign): ?>
<?php $pond = $assign['pond']; ?>
<tr>
    <td><?= $pond['id'] ?></td>
    <td><?= htmlspecialchars($pond['name']) ?></td>
    <td><?= htmlspecialchars(ucfirst($pond['type'])) ?></td>
    <td><?= htmlspecialchars(ucfirst($pond['category'] ?? 'N/A')) ?></td>
    <td><?= htmlspecialchars($pond['location'] ?? '') ?></td>
    <td><?= $assign['created_at'] ?></td>
    <td>
        <?php if (strtolower($pond['type']) === 'grow_out'): ?>
            <!-- Grow-out pond: go to growout_stocking.php -->
            <a href="growout_stocking.php?pond_id=<?= $pond['id'] ?>" class="add-btn">Add Fish</a>
            <a href="harvest.php?pond_id=<?= $pond['id'] ?>" class="harvest-btn">Harvest</a>
        <?php elseif (strtolower($pond['type']) === 'breeders'): ?>
            <!-- Breeders pond: normal stocking + transfer -->
            <a href="add_stocking.php?pond_id=<?= $pond['id'] ?>" class="add-btn">Add Fish</a>
            <a href="transfer.php?from_pond_id=<?= $pond['id'] ?>" class="transfer-btn">Transfer Fingerlings</a>
        <?php else: ?>
            <!-- Default option for other types -->
            <a href="add_stocking.php?pond_id=<?= $pond['id'] ?>" class="add-btn">Add Fish</a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>

</table>
<?php else: ?>
<p>⚠️ No ponds are assigned to you yet. Please contact your admin.</p>
<?php endif; ?>

<a href="logout.php" class="logout">Logout</a>

</body>
</html>
