<?php
/**
 * app/models/Invoice.php — Invoice Model
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Handles all database operations for the `invoices` table.
 * An invoice is generated for a subscription and starts as 'Unpaid'.
 * It becomes 'Paid' once a payment is recorded against it.
 */
class Invoice {
    /** @var mysqli Shared database connection */
    private $conn;

    /** Constructor — injects the shared mysqli connection */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Generate a new invoice for a given subscription.
     * Due date is automatically set to 30 days from today.
     * Status starts as 'Unpaid' by default.
     */
    public function generate($subscriptionId, $amount) {
        $dueDate = date('Y-m-d', strtotime('+30 days')); // 30-day payment window
        $stmt = $this->conn->prepare(
            "INSERT INTO invoices (subscription_id, amount, status, due_date)
             VALUES (?, ?, 'Unpaid', ?)"
        );
        $stmt->bind_param("ids", $subscriptionId, $amount, $dueDate);
        return $stmt->execute();
    }

    /**
     * Retrieve all invoices with customer name and plan name joined.
     * Ordered by most recently created first for the billing view table.
     */
    public function getAll() {
        return $this->conn->query(
            "SELECT i.*, c.name AS customer_name, p.name AS plan_name
             FROM invoices i
             JOIN subscriptions s ON i.subscription_id = s.id
             JOIN customers c ON s.customer_id = c.id
             JOIN service_plans p ON s.plan_id = p.id
             ORDER BY i.created_at DESC"
        );
    }

    /**
     * Mark a specific invoice as Paid.
     * Called by Payment::record() after a payment is successfully saved.
     */
    public function markPaid($id) {
        $stmt = $this->conn->prepare("UPDATE invoices SET status='Paid' WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Count invoices that have not been paid yet.
     * Used by the dashboard stat card to highlight outstanding balances.
     */
    public function countUnpaid() {
        return $this->conn->query(
            "SELECT COUNT(*) as total FROM invoices WHERE status='Unpaid'"
        )->fetch_assoc()['total'];
    }

    /**
     * Calculate total revenue from all paid invoices.
     * Returns 0 if no payments have been recorded yet (null-safe with ?? 0).
     */
    public function totalRevenue() {
        return $this->conn->query(
            "SELECT SUM(amount) as total FROM invoices WHERE status='Paid'"
        )->fetch_assoc()['total'] ?? 0;
    }

    /**
     * Retrieve invoices for a specific customer with optional date range filtering.
     * Used by the customer portal report page.
     * $dateFrom and $dateTo are strings in 'Y-m-d' format, or empty to skip filtering.
     */
    public function getByCustomerFiltered($customerId, $dateFrom = '', $dateTo = '') {
        // Base query — always filters by customer
        $sql = "SELECT i.*, p.name AS plan_name
                FROM invoices i
                JOIN subscriptions s ON i.subscription_id = s.id
                JOIN service_plans p ON s.plan_id = p.id
                WHERE s.customer_id = ?";

        $params     = [$customerId];
        $paramTypes = 'i';

        // Append date filters only when the user provided them
        if ($dateFrom) {
            $sql         .= " AND DATE(i.created_at) >= ?";
            $paramTypes  .= 's';
            $params[]     = $dateFrom;
        }
        if ($dateTo) {
            $sql         .= " AND DATE(i.created_at) <= ?";
            $paramTypes  .= 's';
            $params[]     = $dateTo;
        }

        $sql .= " ORDER BY i.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        // Unpack the params array dynamically using the spread operator
        $stmt->bind_param($paramTypes, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }
}
