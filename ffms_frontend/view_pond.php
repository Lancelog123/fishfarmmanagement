<?php
session_start();

// Only admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("No pond selected.");
}

$pond_id = intval($_GET['id']);
$apiBase = "http://127.0.0.1:8000/api"; // Change to your API base URL

// --- Helper functions ---
function apiGet($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp, true);
}

// Fetch pond details
$pondResp = apiGet($apiBase . "/ponds/$pond_id");
$pond = $pondResp['data'] ?? null;
if (!$pond) die("Pond not found.");

// Fetch stocking logs
$logsResp = apiGet($apiBase . "/stocking-logs?pond_id=$pond_id");
$logs = $logsResp['data'] ?? [];

// --- Overall Forecast ---
$totalStocked = 0;
foreach ($logs as $log) {
    if ($log['action_type'] === 'stock') {
        $totalStocked += $log['quantity'];
    } elseif ($log['action_type'] === 'harvest') {
        $totalStocked -= $log['quantity'];
    }
}
$expectedYield = round($totalStocked * 0.9);
$expectedLoss  = $totalStocked - $expectedYield;

// --- Prediction function ---
function getPrediction($species, $date, $qty, $action_type, $category) {
    if ($action_type !== 'stock') {
        return ["-", "-", "-", "-", "-"];
    }

    $harvestDate = "";
    $expectedSize = "";

    if (stripos($category, "fingerling") !== false) {
        $harvestDate = date('Y-m-d', strtotime($date . " +45 days"));
        $expectedSize = "10–15g (fingerlings)";
    } else {
        switch (strtolower($species)) {
            case "tilapia":
                $harvestDate = date('Y-m-d', strtotime($date . " +6 months"));
                $expectedSize = "≈500g";
                break;
            case "hito":
            case "catfish":
                $harvestDate = date('Y-m-d', strtotime($date . " +5 months"));
                $expectedSize = "≈600g";
                break;
            default:
                $harvestDate = date('Y-m-d', strtotime($date . " +6 months"));
                $expectedSize = "≈400g";
                break;
        }
    }

    $expectedYieldEntry = round($qty * 0.9);
    $expectedLossEntry  = $qty - $expectedYieldEntry;

    return [$date, $harvestDate, $expectedSize, $expectedYieldEntry, $expectedLossEntry];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pond Details - <?= htmlspecialchars($pond['name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2, h3 { margin-top: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
<h2>Pond Details: <?= htmlspecialchars($pond['name']) ?></h2>
<p>Category: <?= $pond['pond_category'] ?></p>
<p>Location: <?= htmlspecialchars($pond['location'] ?? '') ?></p>

<h3>Overall Forecast</h3>
<p><b>Total Stocked:</b> <?= $totalStocked ?></p>
<p><b>Expected Yield (90% survival):</b> <?= $expectedYield ?></p>
<p><b>Expected Loss:</b> <?= $expectedLoss ?></p>

<h3>Logs with Predictions</h3>
<?php if (empty($logs)): ?>
    <p>No logs yet for this pond.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Date of Stocking</th>
            <th>Action</th>
            <th>Species</th>
            <th>Qty</th>
            <th>Expected Harvest Date</th>
            <th>Expected Size</th>
            <th>Expected Yield</th>
            <th>Expected Loss</th>
        </tr>
        <?php foreach ($logs as $log): ?>
            <?php
                if ($log['action_type'] === 'stock') {
                    list($stockDate, $harvestDate, $expSize, $expYield, $expLoss) = 
                        getPrediction($log['species'], $log['action_date'], $log['quantity'], $log['action_type'], $pond['pond_category']);
                } elseif ($log['action_type'] === 'harvest') {
                    $stockDate   = $log['action_date'];
                    $harvestDate = "—";
                    $expSize     = "—";
                    $expYield    = "Harvested: " . $log['quantity'];
                    $expLoss     = "—";
                } else {
                    $stockDate   = $log['action_date'];
                    $harvestDate = "—";
                    $expSize     = "—";
                    $expYield    = "-";
                    $expLoss     = "-";
                }
            ?>
            <tr>
                <td><?= $stockDate ?></td>
                <td><?= ucfirst($log['action_type']) ?></td>
                <td><?= htmlspecialchars($log['species']) ?></td>
                <td><?= $log['quantity'] ?></td>
                <td><?= $harvestDate ?></td>
                <td><?= $expSize ?></td>
                <td><?= $expYield ?></td>
                <td><?= $expLoss ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<p><a href="pond_management.php">← Back</a></p>
</body>
</html>
