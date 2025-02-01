<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

// Initialize database connection
$db = getDB();

// Get cart data from localStorage via AJAX
if (isset($_POST['get_product_details'])) {
    $productIds = json_decode($_POST['product_ids']);
    $stmt = $db->prepare("SELECT id, name, price, image_url FROM products WHERE id IN (" . str_repeat('?,', count($productIds) - 1) . "?)");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Additional Cart-specific styles */
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .cart-item {
            display: flex;
            border-bottom: 1px solid #eee;
            padding: 20px 0;
            align-items: center;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right: 20px;
        }

        .item-details {
            flex-grow: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-controls button {
            padding: 5px 10px;
            border: 1px solid #ddd;
            background: #f8f8f8;
            cursor: pointer;
        }

        .total-section {
            margin-top: 20px;
            padding: 20px;
            background: #f8f8f8;
            border-radius: 5px;
        }

        .cart-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                text-align: center;
            }

            .item-details {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
<div class="cart-container">
    <h1>Your Shopping Cart</h1>

    <div id="cart-items">
        <!-- Cart items will be loaded dynamically -->
    </div>

    <div id="cart-summary" class="total-section" style="display: none;">
        <div class="subtotal">
            <span>Subtotal:</span>
            <span id="subtotal-amount">0.00 €</span>
        </div>
        <div class="shipping">
            <span>Shipping:</span>
            <span id="shipping-amount">4.99 €</span>
        </div>
        <div class="total">
            <strong>Total:</strong>
            <strong id="total-amount">0.00 €</strong>
        </div>
    </div>

    <div class="cart-actions">
        <a href="index.php" class="button secondary">Continue Shopping</a>
        <button id="checkout-button" class="button primary" onclick="proceedToCheckout()" disabled>
            Proceed to Checkout
        </button>
    </div>
</div>

<script>
    // Load cart on page load
    document.addEventListener('DOMContentLoaded', loadCart);

    function loadCart() {
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
        const cartItemsContainer = document.getElementById('cart-items');
        const productIds = Object.keys(cart);

        if (productIds.length === 0) {
            showEmptyCart();
            return;
        }

        // Fetch product details from server
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `get_product_details=1&product_ids=${JSON.stringify(productIds)}`
        })
            .then(response => response.json())
            .then(products => {
                let cartHTML = '';
                let subtotal = 0;

                products.forEach(product => {
                    const quantity = cart[product.id];
                    const itemTotal = product.price * quantity;
                    subtotal += itemTotal;

                    cartHTML += `
                        <div class="cart-item" id="cart-item-${product.id}">
                            <img src="${product.image_url}" alt="${product.name}">
                            <div class="item-details">
                                <div class="item-info">
                                    <h3>${product.name}</h3>
                                    <p>${product.price.toFixed(2)} €</p>
                                </div>
                                <div class="quantity-controls">
                                    <button onclick="updateQuantity(${product.id}, ${quantity - 1})">-</button>
                                    <span id="quantity-${product.id}">${quantity}</span>
                                    <button onclick="updateQuantity(${product.id}, ${quantity + 1})">+</button>
                                </div>
                                <div class="item-total">
                                    <strong>${itemTotal.toFixed(2)} €</strong>
                                </div>
                                <button onclick="removeItem(${product.id})" class="remove-button">Remove</button>
                            </div>
                        </div>
                    `;
                });

                cartItemsContainer.innerHTML = cartHTML;
                updateTotals(subtotal);
                document.getElementById('cart-summary').style.display = 'block';
                document.getElementById('checkout-button').disabled = false;
            });
    }

    function updateQuantity(productId, newQuantity) {
        if (newQuantity <= 0) {
            removeItem(productId);
            return;
        }

        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
        cart[productId] = newQuantity;
        localStorage.setItem('cart', JSON.stringify(cart));

        // Reload cart to update display
        loadCart();
    }

    function removeItem(productId) {
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
        delete cart[productId];
        localStorage.setItem('cart', JSON.stringify(cart));

        // Remove item from display
        const itemElement = document.getElementById(`cart-item-${productId}`);
        itemElement.remove();

        // Check if cart is empty
        if (Object.keys(cart).length === 0) {
            showEmptyCart();
        } else {
            loadCart(); // Reload to update totals
        }
    }

    function updateTotals(subtotal) {
        const shipping = 4.99;
        const total = subtotal + shipping;

        document.getElementById('subtotal-amount').textContent = subtotal.toFixed(2) + ' €';
        document.getElementById('shipping-amount').textContent = shipping.toFixed(2) + ' €';
        document.getElementById('total-amount').textContent = total.toFixed(2) + ' €';
    }

    function showEmptyCart() {
        document.getElementById('cart-items').innerHTML = `
                <div class="empty-cart">
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                </div>
            `;
        document.getElementById('cart-summary').style.display = 'none';
        document.getElementById('checkout-button').disabled = true;
    }

    function proceedToCheckout() {
        // Check if user is logged in
        fetch('check_login.php')
            .then(response => response.json())
            .then(data => {
                if (data.logged_in) {
                    window.location.href = 'checkout.php';
                } else {
                    window.location.href = 'login.php?redirect=checkout.php';
                }
            });
    }
</script>
</body>
</html>