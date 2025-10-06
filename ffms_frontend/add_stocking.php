<?php
session_start();

// Only workers can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'worker') {
    header("Location: login.php");
    exit();
}

$apiBase = "http://127.0.0.1:8000/api";

// Check pond_id
if (!isset($_GET['pond_id'])) die("❌ No pond selected.");
$pond_id = intval($_GET['pond_id']);

// Helper function for API GET
function apiGet($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp, true);
}

// Fetch all nets for this pond
$netsData = apiGet("$apiBase/nets?pond_id=$pond_id");
$nets = $netsData['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Nets for Pond <?= $pond_id ?></title>
<style>
body { font-family: Arial; padding: 20px; background: #f4f4f4; }
table { border-collapse: collapse; width: 100%; background: #fff; margin-top:10px;}
th, td { border: 1px solid #333; padding: 8px; text-align: left; }
th { background: #555; color: #fff; }
button { padding: 5px 10px; cursor: pointer; margin-right: 5px; }
.modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
.modal-content { background: #fff; padding: 20px; border-radius: 5px; min-width: 300px; }
label { display: block; margin-top: 10px; }
input { width: 100%; padding: 5px; margin-top: 5px; }
.message { margin-top: 10px; padding: 10px; border-radius: 5px; }
.success { background: #c8e6c9; color: #256029; }
.error { background: #ffcdd2; color: #c62828; }
</style>
</head>
<body>

<h2>Nets for Pond <?= $pond_id ?></h2>

<table>
<tr>
    <th>ID</th>
    <th>Identifier</th>
    <th>Quantity</th>
    <th>Actions</th>
</tr>
<?php foreach ($nets as $net): ?>
<tr data-net-id="<?= $net['id'] ?>" data-net-identifier="<?= htmlspecialchars($net['identifier']) ?>" data-net-quantity="<?= $net['quantity'] ?>">
    <td><?= $net['id'] ?></td>
    <td><?= htmlspecialchars($net['identifier']) ?></td>
    <td><?= $net['quantity'] ?></td>
    <td>
        <button class="harvestBtn">Harvest</button>
        <button class="editBtn">Edit</button>
    </td>
</tr>
<?php endforeach; ?>
</table>

<!-- Harvest Modal -->
<div class="modal" id="harvestModal">
    <div class="modal-content">
        <h3>Harvest Net</h3>
        <form id="harvestForm">
            <input type="hidden" name="net_id" id="harvestNetId">
            <label>Species:</label>
            <input type="text" name="species" id="harvestSpecies" readonly>
            <label>Quantity (max):</label>
            <input type="number" name="fish_qty" id="harvestQty" min="1" required>
            <label>Size (inch):</label>
            <input type="number" step="0.1" name="size_inch" required>
            <label>Price/unit:</label>
            <input type="number" step="0.01" name="price_per_unit" min="0" required>
            <label>Buyer (optional):</label>
            <input type="text" name="buyer_name">
            <div>Total Amount: <span id="harvestTotal">0</span></div>
            <button type="submit">Save Harvest</button>
            <button type="button" id="closeHarvest">Cancel</button>
        </form>
        <div id="harvestMessage"></div>
    </div>
</div>

<script>
const harvestModal = document.getElementById('harvestModal');
const harvestForm = document.getElementById('harvestForm');
const harvestMessage = document.getElementById('harvestMessage');
const closeHarvest = document.getElementById('closeHarvest');
const harvestTotal = document.getElementById('harvestTotal');
const harvestSpecies = document.getElementById('harvestSpecies');

// Open harvest modal per net
document.querySelectorAll('.harvestBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const row = btn.closest('tr');
        const netId = row.dataset.netId;
        const identifier = row.dataset.netIdentifier;
        const quantity = row.dataset.netQuantity;

        harvestForm.net_id.value = netId;
        harvestSpecies.value = identifier; // species = net identifier
        harvestForm.fish_qty.max = quantity;
        harvestForm.fish_qty.value = quantity;
        harvestForm.price_per_unit.value = 0;
        harvestTotal.innerText = '0';
        harvestMessage.innerHTML = '';
        harvestModal.style.display = 'flex';
    });
});

// Close modal
closeHarvest.addEventListener('click', () => harvestModal.style.display = 'none');

// Update total
harvestForm.fish_qty.addEventListener('input', updateTotal);
harvestForm.price_per_unit.addEventListener('input', updateTotal);

function updateTotal() {
    const qty = parseFloat(harvestForm.fish_qty.value) || 0;
    const price = parseFloat(harvestForm.price_per_unit.value) || 0;
    harvestTotal.innerText = (qty * price).toFixed(2);
}

// Submit harvest
harvestForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(harvestForm);
    formData.append('pond_id', <?= $pond_id ?>);
    formData.append('type','fingerlings'); // per net

    fetch('http://127.0.0.1:8000/api/harvest-logs',{
        method:'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.harvest){
            harvestMessage.innerHTML = `<div class="message success">✅ Harvested ${data.harvest.fish_qty} fish! Total: ${data.harvest.total_amount}</div>`;
            setTimeout(()=>window.location.reload(),1000);
        } else if(data.errors){
            let errs = '';
            for(let k in data.errors) errs+=data.errors[k].join(', ')+'<br>';
            harvestMessage.innerHTML = `<div class="message error">❌ ${errs}</div>`;
        } else {
            harvestMessage.innerHTML = `<div class="message error">❌ Failed to harvest</div>`;
        }
    })
    .catch(err => harvestMessage.innerHTML = `<div class="message error">❌ ${err}</div>`);
});
</script>
</body>
</html>
