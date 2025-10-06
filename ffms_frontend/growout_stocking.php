<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role']!=='worker'){
    header("Location: login.php"); exit();
}

$apiBase = "http://127.0.0.1:8000/api";
$pond_id = $_GET['pond_id'] ?? die("❌ No pond selected.");

function apiGet($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp,true);
}

// Fetch available stocking
$stockings = apiGet("$apiBase/stocking-logs?pond_id=$pond_id")['data'] ?? [];
$harvests = apiGet("$apiBase/harvest-logs?pond_id=$pond_id")['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Grow-Out Pond <?= $pond_id ?></title>
<style>
body{font-family:Arial;padding:20px;background:#f4f4f4;}
table{border-collapse:collapse;width:100%;background:#fff;margin-top:10px;}
th,td{border:1px solid #333;padding:8px;text-align:left;}
th{background:#555;color:#fff;}
button{padding:5px 10px;cursor:pointer;border:none;border-radius:4px;}
.harvest-btn{background:#3c8dbc;color:#fff;}
.delete-btn{background:#f44336;color:#fff;}
.add-btn{background:#4CAF50;color:#fff;margin-top:10px;}
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);justify-content:center;align-items:center;}
.modal-content{background:#fff;padding:20px;border-radius:5px;min-width:300px;}
label{display:block;margin-top:10px;}
input{width:100%;padding:5px;margin-top:5px;}
.message{margin-top:10px;padding:10px;border-radius:5px;}
.success{background:#c8e6c9;color:#256029;}
.error{background:#ffcdd2;color:#c62828;}
</style>
</head>
<body>

<h2>Grow-Out Pond <?= $pond_id ?></h2>

<h3>Stocking (Available)</h3>
<?php if(empty($stockings)): ?>
<p>No available stocking.</p>
<?php else: ?>
<table>
<tr><th>ID</th><th>Species</th><th>Qty</th><th>Date Stocked</th><th>Actions</th></tr>
<?php foreach($stockings as $s): ?>
<tr>
<td><?= $s['id'] ?></td>
<td><?= htmlspecialchars($s['species']) ?></td>
<td><?= $s['quantity'] ?></td>
<td><?= htmlspecialchars($s['action_date']) ?></td>
<td>
<button class="harvest-btn" onclick="openHarvestModal(<?= $s['id'] ?>,'<?= htmlspecialchars($s['species']) ?>',<?= $s['quantity'] ?>)">Harvest</button>
<button class="delete-btn" onclick="deleteStocking(<?= $s['id'] ?>)">Delete</button>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<button class="add-btn" onclick="openAddStockModal()">➕ Add Stock</button>

<h3>Harvested Logs</h3>
<?php if(empty($harvests)): ?>
<p>No harvests yet.</p>
<?php else: ?>
<table>
<tr><th>ID</th><th>Species</th><th>Size</th><th>Qty</th><th>Price/unit</th><th>Total</th><th>Buyer</th><th>Date</th></tr>
<?php foreach($harvests as $h): ?>
<tr>
<td><?= $h['id'] ?></td>
<td><?= htmlspecialchars($h['species']) ?></td>
<td><?= $h['size_inch'] ?></td>
<td><?= $h['fish_qty'] ?></td>
<td><?= $h['price_per_unit'] ?></td>
<td><?= $h['total_amount'] ?></td>
<td><?= htmlspecialchars($h['buyer_name']??'-') ?></td>
<td><?= htmlspecialchars($h['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<!-- Harvest Modal -->
<div class="modal" id="harvestModal">
<div class="modal-content">
<h3>Harvest</h3>
<form id="harvestForm">
<input type="hidden" name="pond_id" value="<?= $pond_id ?>">
<input type="hidden" name="type" value="grow_out">
<input type="hidden" name="stocking_id">
<label>Species:</label><input type="text" name="species" readonly>
<label>Qty (max):</label><input type="number" name="fish_qty" min="1" required>
<label>Size (inch):</label><input type="number" step="0.1" name="size_inch" required>
<label>Price/unit:</label><input type="number" step="0.01" name="price_per_unit" min="0" required>
<label>Buyer (optional):</label><input type="text" name="buyer_name">
<div>Total Amount: <span id="totalAmount">0</span></div>
<button type="submit">Save Harvest</button>
<button type="button" id="closeHarvest">Cancel</button>
</form>
<div id="harvestMessage"></div>
</div>
</div>

<!-- Add Stock Modal -->
<div class="modal" id="addStockModal">
<div class="modal-content">
<h3>Add Stock</h3>
<form id="addStockForm">
<input type="hidden" name="pond_id" value="<?= $pond_id ?>">
<label>Species:</label><input type="text" name="species" required>
<label>Qty:</label><input type="number" name="quantity" min="1" required>
<label>Date:</label><input type="date" name="action_date" required>
<button type="submit">Save Stock</button>
<button type="button" id="closeAddStock">Cancel</button>
</form>
<div id="addStockMessage"></div>
</div>
</div>

<script>
const harvestModal = document.getElementById('harvestModal');
const harvestForm = document.getElementById('harvestForm');
const harvestMsg = document.getElementById('harvestMessage');
const closeHarvest = document.getElementById('closeHarvest');
const totalAmountSpan = document.getElementById('totalAmount');

function openHarvestModal(stockId,species,maxQty){
    harvestModal.style.display='flex';
    harvestMsg.innerHTML='';
    harvestForm.stocking_id.value = stockId;
    harvestForm.species.value = species;
    harvestForm.fish_qty.max = maxQty;
    harvestForm.fish_qty.value = maxQty;
    harvestForm.price_per_unit.value = 0;
    totalAmountSpan.innerText = '0';
}
closeHarvest.addEventListener('click',()=>harvestModal.style.display='none');

harvestForm.fish_qty.addEventListener('input',updateTotal);
harvestForm.price_per_unit.addEventListener('input',updateTotal);
function updateTotal(){
    const qty = parseFloat(harvestForm.fish_qty.value)||0;
    const price = parseFloat(harvestForm.price_per_unit.value)||0;
    totalAmountSpan.innerText = (qty*price).toFixed(2);
}
harvestForm.addEventListener('submit',(e)=>{
    e.preventDefault();
    const formData = new FormData(harvestForm);
    fetch('http://127.0.0.1:8000/api/harvest-logs',{
        method:'POST', body:formData
    }).then(r=>r.json())
    .then(d=>{
        if(d.harvest){
            harvestMsg.innerHTML=`<div class="message success">✅ Harvest recorded: ${d.harvest.total_amount}</div>`;
            setTimeout(()=>window.location.reload(),1000);
        } else if(d.errors){
            let errs=''; for(let k in d.errors) errs+=d.errors[k].join(', ')+'<br>';
            harvestMsg.innerHTML=`<div class="message error">❌ ${errs}</div>`;
        } else harvestMsg.innerHTML=`<div class="message error">❌ Failed.</div>`;
    }).catch(err=>harvestMsg.innerHTML=`<div class="message error">❌ ${err}</div>`);
});

// Delete stocking
function deleteStocking(id){
    if(!confirm('Delete this stocking?')) return;
    fetch(`http://127.0.0.1:8000/api/stocking-logs/${id}`,{method:'DELETE'})
    .then(r=>r.json())
    .then(d=>{ alert(d.message); window.location.reload(); })
    .catch(err=>alert('Error: '+err));
}

// Add stock modal
const addStockModal = document.getElementById('addStockModal');
const addStockForm = document.getElementById('addStockForm');
const addStockMsg = document.getElementById('addStockMessage');
const closeAddStock = document.getElementById('closeAddStock');
function openAddStockModal(){ addStockModal.style.display='flex'; addStockMsg.innerHTML=''; }
closeAddStock.addEventListener('click',()=>addStockModal.style.display='none');
addStockForm.addEventListener('submit',(e)=>{
    e.preventDefault();
    const formData = new FormData(addStockForm);
    formData.append('user_id',<?= $_SESSION['user_id'] ?>);
    formData.append('action_type','grow_out');
    fetch('http://127.0.0.1:8000/api/stocking-logs',{
        method:'POST', body:formData
    }).then(r=>r.json())
    .then(d=>{
        if(d.log) { addStockMsg.innerHTML=`<div class="message success">✅ Stock added</div>`; setTimeout(()=>window.location.reload(),800); }
        else if(d.errors){ let errs=''; for(let k in d.errors) errs+=d.errors[k].join(', ')+'<br>'; addStockMsg.innerHTML=`<div class="message error">${errs}</div>`;}
        else addStockMsg.innerHTML=`<div class="message error">❌ Failed</div>`;
    }).catch(err=>addStockMsg.innerHTML=`<div class="message error">❌ ${err}</div>`);
});
</script>
</body>
</html>
