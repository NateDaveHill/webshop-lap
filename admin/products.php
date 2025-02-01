<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Check admin access
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $db->prepare("
                    INSERT INTO products (name, description, price, stock, image_url) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['stock'],
                    $_POST['image_url']
                ]);
                break;

            case 'edit':
                $stmt = $db->prepare("
                    UPDATE products 
                    SET name = ?, description = ?, price = ?, stock = ?, image_url = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['stock'],
                    $_POST['image_url'],
                    $_POST['id']
                ]);
                break;

            case 'delete':
                $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                break;
        }
        header('Location: products.php');
        exit;
    }
}

// Get all products
$stmt = $db->query("SELECT * FROM products ORDER BY name");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Produkte verwalten</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <nav>
        <a href="index.php">Dashboard</a>
        <a href="orders.php">Bestellungen</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<main class="container">
    <h1>Produkte verwalten</h1>

    <!-- Add new product form -->
    <section class="add-product">
        <h2>Neues Produkt hinzufügen</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Beschreibung:</label>
                <textarea name="description" required></textarea>
            </div>
            <div class="form-group">
                <label>Preis:</label>
                <input type="number" step="0.01" name="price" required>
            </div>
            <div class="form-group">
                <label>Lagerbestand:</label>
                <input type="number" name="stock" required>
            </div>
            <div class="form-group">
                <label>Bild-URL:</label>
                <input type="url" name="image_url">
            </div>
            <button type="submit">Produkt hinzufügen</button>
        </form>
    </section>

    <!-- Products list -->
    <section class="products-list">
        <h2>Vorhandene Produkte</h2>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Preis</th>
                <th>Lagerbestand</th>
                <th>Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['price']); ?> €</td>
                    <td><?php echo htmlspecialchars($product['stock']); ?></td>
                    <td>
                        <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                            Bearbeiten
                        </button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                            <button type="submit" onclick="return confirm('Wirklich löschen?')">
                                Löschen
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<script>
    function editProduct(product) {
        // Create and show modal with edit form
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
                <div class="modal-content">
                    <h2>Produkt bearbeiten</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="${product.id}">
                        <div class="form-group">
                            <label>Name:</label>
                            <input type="text" name="name" value="${product.name}" required>
                        </div>
                        <div class="form-group">
                            <label>Beschreibung:</label>
                            <textarea name="description" required>${product.description}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Preis:</label>
                            <input type="number" step="0.01" name="price" value="${product.price}" required>
                        </div>
                        <div class="form-group">
                            <label>Lagerbestand:</label>
                            <input type="number" name="stock" value="${product.stock}" required>
                        </div>
                        <div class="form-group">
                            <label>Bild-URL:</label>
                            <input type="url" name="image_url" value="${product.image_url}">
                        </div>
                        <button type="submit">Speichern</button>
                        <button type="button" onclick="this.closest('.modal').remove()">Abbrechen</button>
                    </form>
                </div>
            `;
        document.body.appendChild(modal);
    }
</script>
</body>
</html>