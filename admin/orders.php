<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

// Get all orders with user information
$stmt = $db->query("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bestellungen verwalten</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <nav>
        <a href="index.php">Dashboard</a>
        <a href="products.php">Produkte</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<main class="container">
    <h1>Bestellungen</h1>

    <table>
        <thead>
        <tr>
            <th>Bestell-ID</th>
            <th>Kunde</th>
            <th>Email</th>
            <th>Betrag</th>
            <th>Datum</th>
            <th>Details</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($orders as $order): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['id']); ?></td>
                <td><?php echo htmlspecialchars($order['username']); ?></td>
                <td><?php echo htmlspecialchars($order['email']); ?></td>
                <td><?php echo htmlspecialchars($order['total_amount']); ?> €</td>
                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                <td>
                    <button onclick="showOrderDetails(<?php echo $order['id']; ?>)">
                        Details anzeigen
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>

<script>
    function showOrderDetails(orderId) {
        // Here you would typically fetch order details via AJAX
        // For now, we'll just show a simple alert
        alert('Bestelldetails für Bestellung #' + orderId);
    }
</script>
</body>
</html>