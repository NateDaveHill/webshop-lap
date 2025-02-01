<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();
$stmt = $db->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Webshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <nav>
        <a href="index.php">Home</a>
        <a href="cart.php">Warenkorb</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>

<main>
    <div class="products-grid">
        <?php foreach($products as $product): ?>
            <div class="product-card">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p><?php echo htmlspecialchars($product['price']); ?> €</p>
                <button onclick="addToCart(<?php echo $product['id']; ?>)">In den Warenkorb</button>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script src="js/main.js"></script>
</body>
</html>
