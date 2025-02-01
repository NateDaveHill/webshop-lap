<?php
function getDB() {
    $dbDir = dirname(DB_PATH);

    // Check if the directory exists
    if (!is_dir($dbDir)) {
        // Create the directory
        if (!mkdir($dbDir, 0755, true)) {
            echo "Error creating database directory.";
            exit;
        }
    }

    // Check if the directory is writable
    if (!is_writable($dbDir)) {
        echo "Database directory is not writable.";
        exit;
    }

    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        echo "Error connecting to the database: " . $e->getMessage();
        exit;
    }
}
?>