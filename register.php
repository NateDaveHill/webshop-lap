<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();

    // Check if username exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    if ($stmt->fetch()) {
        $error = 'Username already exists';
    } else {
        // Create new user
        $stmt = $db->prepare("
            INSERT INTO users (username, password, email) 
            VALUES (?, ?, ?)
        ");

        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

        try {
            $stmt->execute([
                $_POST['username'],
                $hashedPassword,
                $_POST['email']
            ]);
            $success = 'Registration successful! You can now login.';
        } catch(PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-container">
    <h1>Register</h1>

    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="password_confirm">Confirm Password</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>

        <button type="submit">Register</button>
    </form>

    <div class="auth-links">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirm').value;

        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
        }
    });
</script>
</body>
</html>