function addToCart(productId) {
    let cart = JSON.parse(localStorage.getItem('cart') || '{}');
    cart[productId] = (cart[productId] || 0) + 1;
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartDisplay();
}

function updateCartDisplay() {
    // Aktualisiere den Warenkorb-Counter im Header
    const cart = JSON.parse(localStorage.getItem('cart') || '{}');
    const total = Object.values(cart).reduce((sum, count) => sum + count, 0);
    document.getElementById('cart-count').textContent = total;
}

// Initialisiere Cart Display
document.addEventListener('DOMContentLoaded', updateCartDisplay);
