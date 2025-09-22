<?php
// Step 1: Connect to the database
require_once '../includes/dbconfig.php';
require_once '../includes/functions.php'; // Needed for CSRF token

// Step 2: Get all orders
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $mysqli->query($sql);
$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1><a href="./dashboard.php">Back</a></h1>
        <h2 class="mb-4">Customer Orders</h2>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success">Order status updated successfully.</div>
        <?php endif; ?>

        <table class="table table-bordered bg-white shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Total (GHS)</th>    
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td><?= htmlspecialchars($row['address']) ?></td>
                            <td><?= number_format($row['order_total'], 2) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                            <td>
                                <form action="./adfunc/update-status.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <button type="submit" class="btn btn-sm btn-success">Mark as Completed</button>
                                    <?php else: ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
