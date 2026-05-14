<?php
/**
 * public/logout.php — Admin Logout
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Destroys the admin session completely and redirects to the login page.
 * This file is reached by clicking "Sign Out" in the admin sidebar.
 */

session_start();

// Remove all session variables (clears $_SESSION array)
session_unset();

// Destroy the session data stored on the server
session_destroy();

// Send the admin back to the login page
header('Location: login.php');
exit;
