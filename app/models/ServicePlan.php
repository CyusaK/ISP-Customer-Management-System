<?php
/**
 * app/models/ServicePlan.php — Service Plan Model
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Handles all database operations for the `service_plans` table.
 * Service plans define the internet packages KigaliNet offers
 * (e.g. Basic 10 Mbps at 15,000 RWF/month).
 */
class ServicePlan {
    /** @var mysqli Shared database connection */
    private $conn;

    /** Constructor — injects the shared mysqli connection */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Retrieve all service plans ordered by price (cheapest first).
     * Used to populate the plans table and subscription dropdowns.
     */
    public function getAll() {
        return $this->conn->query("SELECT * FROM service_plans ORDER BY price ASC");
    }

    /**
     * Retrieve a single plan by its ID.
     * Used by the customer portal when subscribing to confirm the plan price.
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM service_plans WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Insert a new service plan into the database.
     * Price uses the 'd' bind type (double) to handle decimal values correctly.
     */
    public function create($name, $speed, $price, $description) {
        $stmt = $this->conn->prepare(
            "INSERT INTO service_plans (name, speed, price, description) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssds", $name, $speed, $price, $description);
        return $stmt->execute();
    }

    /**
     * Delete a service plan by ID.
     * Note: plans referenced by active subscriptions cannot be deleted
     * due to the FOREIGN KEY constraint on the subscriptions table.
     */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM service_plans WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
