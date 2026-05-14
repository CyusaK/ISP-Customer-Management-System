<?php
/**
 * app/models/Customer.php — Customer Model
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Handles all database operations for the `customers` table.
 * All queries use prepared statements to prevent SQL injection.
 */
class Customer {
    /** @var mysqli Shared database connection injected via constructor */
    private $conn;

    /**
     * Constructor — receives the shared mysqli connection.
     * Using dependency injection keeps the model testable and decoupled.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Register a new customer without a password (admin-created accounts).
     * Returns true on success, false on failure (e.g. duplicate email).
     */
    public function register($name, $email, $phone, $address) {
        $stmt = $this->conn->prepare(
            "INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $name, $email, $phone, $address);
        return $stmt->execute();
    }

    /**
     * Register a new customer with a hashed password (self-registration via portal).
     * password_hash() uses bcrypt by default — never store plain-text passwords.
     */
    public function registerWithPassword($name, $email, $phone, $address, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT); // bcrypt hash
        $stmt = $this->conn->prepare(
            "INSERT INTO customers (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $name, $email, $phone, $address, $hash);
        return $stmt->execute();
    }

    /**
     * Find a single customer by email address.
     * Used during portal login to retrieve the record for password verification.
     * Returns an associative array or null if not found.
     */
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Retrieve all customers ordered by most recently registered first.
     * Returns a mysqli_result object that views iterate with fetch_assoc().
     */
    public function getAll() {
        return $this->conn->query("SELECT * FROM customers ORDER BY created_at DESC");
    }

    /**
     * Retrieve a single customer by their primary key ID.
     * Used to pre-populate the edit form.
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update an existing customer's details and status.
     * Status can be: Active, Suspended, or Inactive.
     */
    public function update($id, $name, $email, $phone, $address, $status) {
        $stmt = $this->conn->prepare(
            "UPDATE customers SET name=?, email=?, phone=?, address=?, status=? WHERE id=?"
        );
        $stmt->bind_param("sssssi", $name, $email, $phone, $address, $status, $id);
        return $stmt->execute();
    }

    /**
     * Delete a customer by ID.
     * The ON DELETE CASCADE on subscriptions/invoices/tickets ensures
     * all related records are also removed automatically.
     */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Return the total number of customers in the system.
     * Used by the dashboard stat card.
     */
    public function count() {
        return $this->conn->query(
            "SELECT COUNT(*) as total FROM customers"
        )->fetch_assoc()['total'];
    }
}
