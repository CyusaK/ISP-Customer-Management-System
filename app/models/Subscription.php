<?php
/**
 * app/models/Subscription.php — Subscription Model
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Handles all database operations for the `subscriptions` table.
 * A subscription links one customer to one service plan and tracks
 * whether the service is currently Active or Suspended.
 */
class Subscription {
    /** @var mysqli Shared database connection */
    private $conn;

    /** Constructor — injects the shared mysqli connection */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new subscription for a customer.
     * Status defaults to 'Active' and start_date is set to today (CURDATE()).
     */
    public function create($customerId, $planId) {
        $stmt = $this->conn->prepare(
            "INSERT INTO subscriptions (customer_id, plan_id, status, start_date)
             VALUES (?, ?, 'Active', CURDATE())"
        );
        $stmt->bind_param("ii", $customerId, $planId);
        return $stmt->execute();
    }

    /**
     * Retrieve all subscriptions with customer name and plan details joined.
     * The JOIN avoids multiple separate queries in the view.
     */
    public function getAll() {
        return $this->conn->query(
            "SELECT s.*, c.name AS customer_name, p.name AS plan_name, p.price
             FROM subscriptions s
             JOIN customers c ON s.customer_id = c.id
             JOIN service_plans p ON s.plan_id = p.id
             ORDER BY s.id DESC"
        );
    }

    /**
     * Update the status of a subscription (Active ↔ Suspended).
     * Called when the admin clicks Suspend or Activate in the subscriptions view.
     */
    public function updateStatus($id, $status) {
        $stmt = $this->conn->prepare("UPDATE subscriptions SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    /**
     * Count only Active subscriptions.
     * Used by the dashboard stat card to show live service count.
     */
    public function count() {
        return $this->conn->query(
            "SELECT COUNT(*) as total FROM subscriptions WHERE status='Active'"
        )->fetch_assoc()['total'];
    }
}
