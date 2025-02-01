<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    $db = getDB();

    // SQL statements to create tables
    $sql = "
           PRAGMA foreign_keys = ON;
        
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            image_url TEXT,
            stock INTEGER NOT NULL DEFAULT 0
        );
        
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            email TEXT NOT NULL,
            is_admin INTEGER DEFAULT 0
        );
        
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            total_amount REAL NOT NULL,
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ";

    // Execute the SQL statements
    $db->exec($sql);
    echo "Database tables created successfully.";
} catch (PDOException $e) {
    echo "Error creating database tables: " . $e->getMessage();
}
?>