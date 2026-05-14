<?php
/**
 * app/models/Payment.php — Payment Model
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Handles all database operations for the `payments` table.
 * Recording a payment also marks the linked invoice as Paid,
 * keeping both tables in sync within the same method.
 */
class Payment {
    /** @var mysqli Shared database connection */
    private $conn;

    /** Constructor — injects the shared mysqli connection */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Record a payment for an invoice and mark that invoice as Paid.
     * Supported payment methods: Cash, MoMo, Bank Transfer.
     *
     * Two queries run in sequence:
     *   1. INSERT into payments
     *   2. UPDATE invoices SET status='Paid'
     * Returns false if either query fails.
     */
    public function record($invoiceId, $amount, $method) {
        // Step 1: Insert the payment record
        $stmt = $this->conn->prepare(
            "INSERT INTO payments (invoice_id, amount, method) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("ids", $invoiceId, $amount, $method);
        if (!$stmt->execute()) return false;

        // Step 2: Mark the corresponding invoice as Paid
        $upd = $this->conn->prepare("UPDATE invoices SET status='Paid' WHERE id=?");
        $upd->bind_param("i", $invoiceId);
        return $upd->execute();
    }

    /**
     * Retrieve all payments with customer name and original invoice amount joined.
     * Ordered by most recent payment first.
     */
    public function getAll() {
        return $this->conn->query(
            "SELECT p.*, c.name AS customer_name, i.amount AS invoice_amount
             FROM payments p
             JOIN invoices i ON p.invoice_id = i.id
             JOIN subscriptions s ON i.subscription_id = s.id
             JOIN customers c ON s.customer_id = c.id
             ORDER BY p.paid_at DESC"
        );
    }
}
