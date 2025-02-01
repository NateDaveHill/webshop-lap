<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();

    // Erstelle neue Bestellung
    $stmt = $db->prepare("
        INSERT INTO orders (user_id, total_amount)
        VALUES (?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $_POST['total_amount']]);

    // Leere den Warenkorb
    echo "<script>localStorage.removeItem('cart');</script>";
    header('Location: thank-you.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<form method="POST" id="checkout-form">
    <h2>Lieferadresse</h2>
    <input type="text" name="street" required placeholder="Straße">
    <input type="text" name="city" required placeholder="Stadt">
    <input type="text" name="zip" required placeholder="PLZ">

    <h2>Zahlungsmethode</h2>
    <select name="payment_method" required>
        <option value="invoice">Rechnung</option>
        <option value="credit_card">Kreditkarte</option>
    </select>

    <input type="hidden" name="total_amount" id="total-amount">
    <button type="submit">Bestellen</button>
</form>

<script>
    // Berechne Gesamtbetrag aus dem Warenkorb
    const cart = JSON.parse(localStorage.getItem('cart') || '{}');
    document.getElementById('total-amount').value = Object.values(cart)
        .reduce((sum, count) => sum + count, 0);
</script>
</body>
</html>
