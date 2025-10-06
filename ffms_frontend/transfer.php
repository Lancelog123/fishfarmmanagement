<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'worker') {
    header("Location: login.php");
    exit();
}

$worker_id = $_SESSION['user_id'];
$apiBase = "http://127.0.0.1:8000/api";

// Get selected breeder pond ID from query string
$from_pond_id = $_GET['from_pond_id'] ?? null;

// Fetch pond name from API
$from_pond_name = 'Unknown Pond';
if ($from_pond_id) {
    $pondData = json_decode(file_get_contents("$apiBase/ponds/$from_pond_id"), true);
    if (isset($pondData['pond']['name'])) {
        $from_pond_name = $pondData['pond']['name'];
    }
}

// Fetch all ponds for dropdown (excluding breeder pond itself)
$assignedPonds = json_decode(file_get_contents("$apiBase/assignments/worker/$worker_id"), true) ?? [];
$targetPonds = [];
foreach ($assignedPonds as $assign) {
    if ($assign['pond']['id'] != $from_pond_id) {
        $targetPonds[] = $assign['pond'];
    }
}

// Handle Transfer Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to_net_id = $_POST['to_net_id'];
    $quantity  = (int)$_POST['quantity'];

    $payload = [
        'user_id'      => $worker_id,
        'from_pond_id' => $from_pond_id, // hidden input
        'from_net_id'  => null,           // no net for breeder pond
        'to_net_id'    => $to_net_id,
        'quantity'     => $quantity
    ];

    $ch = curl_init("$apiBase/transfer-logs");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($result['transfer']['id'])) {
        $success = "✅ Transfer successful!";
    } else {
        $error = "❌ Transfer failed. Response: " . json_encode($result);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Transfer Fingerlings</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; }
label { display: block; margin-top: 10px; }
select, input, button { padding: 8px; width: 300px; margin-top: 4px; }
button { background-color: #3c8dbc; color: #fff; border: none; border-radius: 4px; cursor: pointer; margin-top: 15px; }
.success { color: green; margin-top: 10px; }
.error { color: red; margin-top: 10px; }
a { display: inline-block; margin-top: 20px; text-decoration: none; }
</style>
</head>
<body>

<h2>Transfer Fingerlings</h2>

<?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
<?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

<form method="POST">
    <label>From Pond:</label>
    <input type="text" value="<?= htmlspecialchars($from_pond_name) ?>" readonly>

    <!-- Hidden input to send breeder pond ID -->
    <input type="hidden" name="from_pond_id" value="<?= $from_pond_id ?>">

    <label>To Pond / Net:</label>
    <select name="to_net_id" required>
        <option value="">-- Select Target Net --</option>
        <?php foreach($targetPonds as $pond): ?>
            <?php
                $nets = json_decode(file_get_contents("$apiBase/nets?pond_id={$pond['id']}"), true)['data'] ?? [];
                foreach($nets as $net):
            ?>
            <option value="<?= $net['id'] ?>">
                <?= htmlspecialchars($pond['name']) ?> / <?= htmlspecialchars($net['identifier']) ?> (Qty: <?= $net['quantity'] ?>)
            </option>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </select>

    <label>Quantity to Transfer:</label>
    <input type="number" name="quantity" min="1" required>

    <button type="submit">Confirm Transfer</button>
</form>

<a href="worker_dashboard.php">← Back to Dashboard</a>

</body>
</html>
