<?php
// app/models/SupportTicket.php
class SupportTicket {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($customerId, $issue) {
        $stmt = $this->conn->prepare(
            "INSERT INTO support_tickets (customer_id, issue, status) VALUES (?, ?, 'Open')"
        );
        $stmt->bind_param("is", $customerId, $issue);
        return $stmt->execute();
    }

    public function getAll() {
        return $this->conn->query(
            "SELECT t.*, c.name AS customer_name
             FROM support_tickets t
             JOIN customers c ON t.customer_id = c.id
             ORDER BY t.created_at DESC"
        );
    }

    public function updateStatus($id, $status) {
        $stmt = $this->conn->prepare("UPDATE support_tickets SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function countOpen() {
        return $this->conn->query("SELECT COUNT(*) as total FROM support_tickets WHERE status='Open'")->fetch_assoc()['total'];
    }

    /**
     * Retrieve all tickets submitted by a specific customer.
     * Used by the customer portal to show their own ticket history.
     */
    public function getByCustomer($customerId) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM support_tickets WHERE customer_id = ? ORDER BY created_at DESC"
        );
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        return $stmt->get_result();
    }
}
