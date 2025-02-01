<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Überprüfe Admin-Login
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

// Hole Statistiken
$stmt = $db->query("
    SELECT p.name, COUNT(o.id) as order_count
    FROM products p
    LEFT JOIN order_items o ON p.id = o.product_id
    GROUP BY p.id
    ORDER BY order_count DESC
    LIMIT 5
");
$topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <h1>Admin Dashboard</h1>
    <nav>
        <a href="products.php">Produkte verwalten</a>
        <a href="orders.php">Bestellungen</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<main>
    <h2>Top 5 Produkte</h2>
    <ul>
        <?php foreach($topProducts as $product): ?>
            <li>
                <?php echo htmlspecialchars($product['name']); ?>
                (<?php echo $product['order_count']; ?> Bestellungen)
            </li>
        <?php endforeach; ?>
    </ul>
</main>
</body>
</html>
