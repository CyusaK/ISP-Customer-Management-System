<?php
/**
 * config/database.php — Database Connection
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Creates a single shared mysqli connection ($conn) used by all models.
 * Connection parameters are read from environment variables first,
 * falling back to XAMPP defaults — this makes the same code work both
 * locally (XAMPP) and inside Docker containers without any changes.
 */

// Read connection settings from environment variables (set in docker-compose.yml)
// or fall back to XAMPP defaults for local development
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');        // XAMPP default: no password
define('DB_NAME', getenv('DB_NAME') ?: 'isp_cms'); // Our database name

// Open the connection — mysqli throws no exception by default, so we check manually
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// If the connection fails, stop execution and show a helpful error message
if ($conn->connect_error) {
    die('<h2 style="font-family:Arial;color:red;padding:30px">
        Database Error: ' . $conn->connect_error . '<br><br>
        Make sure MySQL is running and run
        <a href="/ISP-Customer-Management-System/public/setup.php">setup.php</a> first.
    </h2>');
}

// Set the character encoding to utf8mb4 to support all Unicode characters
// (including emojis and special characters used in names/addresses)
$conn->set_charset('utf8mb4');
