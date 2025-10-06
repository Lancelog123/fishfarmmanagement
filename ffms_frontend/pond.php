    <?php
    session_start();

    // Only allow admin
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }

    // Database connection
    include "dbconnection.php";

    // Fetch approved workers directly from DB (no need for API here)
    $workersStmt = $conn->prepare("SELECT id, fullname FROM users WHERE role = 'worker' AND status = 'approved'");
    $workersStmt->execute();
    $workersList = $workersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle Add Pond
    if (isset($_POST['add_pond'])) {
        $name = $_POST['name'];
        $type = $_POST['type'];
        $location = $_POST['location'] ?? '';
        $workers = $_POST['workers'] ?? [];

        try {
            // Insert pond
            $stmt = $conn->prepare("INSERT INTO ponds (name, type, location, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $stmt->execute([$name, $type, $location]);
            $pond_id = $conn->lastInsertId();

            // Assign workers
            if (!empty($workers)) {
                $assignStmt = $conn->prepare("INSERT INTO assignments (pond_id, user_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                foreach ($workers as $worker_id) {
                    $assignStmt->execute([$pond_id, $worker_id]);
                }
            }

            // ✅ Redirect to prevent duplicate on refresh
            header("Location: pond.php?success=1");
            exit();
        } catch (PDOException $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    }

    // Handle Delete Pond
    if (isset($_GET['delete'])) {
        $pond_id = (int)$_GET['delete'];
        try {
            // Delete assignments first
            $conn->prepare("DELETE FROM assignments WHERE pond_id = ?")->execute([$pond_id]);
            // Delete pond
            $conn->prepare("DELETE FROM ponds WHERE id = ?")->execute([$pond_id]);

            header("Location: pond.php?deleted=1");
            exit();
        } catch (PDOException $e) {
            $error = "❌ Error deleting: " . $e->getMessage();
        }
    }

    // Fetch all ponds with assigned workers
    $pondsStmt = $conn->prepare("
        SELECT p.*, 
            GROUP_CONCAT(u.fullname SEPARATOR ', ') AS assigned_workers
        FROM ponds p
        LEFT JOIN assignments a ON p.id = a.pond_id
        LEFT JOIN users u ON a.user_id = u.id
        GROUP BY p.id
        ORDER BY p.id ASC
    ");
    $pondsStmt->execute();
    $ponds = $pondsStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Admin - Pond Management</title>
    <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #333; padding: 8px; text-align: left; }
    th { background-color: #555; color: #fff; }
    input, select, button { padding: 6px; margin: 4px 0; width: 250px; }
    select[multiple] { height: 120px; }
    a { text-decoration: none; margin-right: 5px; }
    .success { color: green; }
    .error { color: red; }
    </style>
    </head>
    <body>

    <h2>Add New Pond & Assign Workers</h2>
    <?php if(isset($_GET['success'])) echo "<p class='success'>✅ Pond added successfully!</p>"; ?>
    <?php if(isset($_GET['deleted'])) echo "<p class='success'>🗑 Pond deleted successfully!</p>"; ?>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label>Pond Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Pond Type:</label><br>
        <label>Type:</label>
    <select name="type" required>
        <option value="">-- Select Type --</option>
        <option value="grow_out">Grow Out</option>
        <option value="fingerlings">Fingerlings</option>
        <option value="breeders">Breeders</option>
    </select>

    <label>Category:</label>
    <select name="category" required>
        <option value="">-- Select Category --</option>
        <option value="mud">Mud</option>
        <option value="concrete">Concrete</option>
    </select>
        </select><br><br>

        <label>Location (Optional):</label><br>
        <input type="text" name="location"><br><br>

        <label>Assign Workers:</label><br>
        <select name="workers[]" multiple>
            <?php if (!empty($workersList)): ?>
                <?php foreach($workersList as $worker): ?>
                    <option value="<?= $worker['id'] ?>"><?= htmlspecialchars($worker['fullname']) ?></option>
                <?php endforeach; ?>
            <?php else: ?>
                <option disabled>No approved workers found</option>
            <?php endif; ?>
        </select><br><br>

        <button type="submit" name="add_pond">Add Pond & Assign Workers</button>
    </form>

    <h2>Existing Ponds</h2>
    <table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Type</th>
        <th>Location</th>
        <th>Created At</th>
        <th>Assigned Workers</th>
        <th>Actions</th>
    </tr>
    <?php foreach($ponds as $pond): ?>
    <tr>
        <td><?= $pond['id'] ?></td>
        <td><?= htmlspecialchars($pond['name']) ?></td>
        <td><?= $pond['type'] ?></td>
        <td><?= $pond['category'] ?></td>
        <td><?= htmlspecialchars($pond['location'] ?? '') ?></td>
        <td><?= $pond['created_at'] ?></td>
        <td><?= $pond['assigned_workers'] ?: "—" ?></td>
        <td>
            <a href="viewpond.php?id=<?= $pond['id'] ?>">View</a> | 
            <a href="edit_pond.php?id=<?= $pond['id'] ?>">Edit</a> | 
            <a href="?delete=<?= $pond['id'] ?>" onclick="return confirm('Delete this pond?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </table>

    </body>
    </html>
