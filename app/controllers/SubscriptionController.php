<?php
/**
 * app/controllers/SubscriptionController.php — Subscription Controller
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Handles HTTP input for subscription management:
 * creating a new subscription and toggling its status (Active/Suspended).
 */
class SubscriptionController {
    /** @var Subscription The subscription model instance */
    private $subscription;

    /** Constructor — receives the Subscription model via dependency injection */
    public function __construct($subscriptionModel) {
        $this->subscription = $subscriptionModel;
    }

    /**
     * Handle the create-subscription form submission.
     * Reads the selected customer ID and plan ID from POST,
     * then creates the subscription with an Active status and today's start date.
     */
    public function subscribe() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customerId = (int)$_POST['customer_id']; // Cast to int — IDs must be integers
            $planId     = (int)$_POST['plan_id'];
            $this->subscription->create($customerId, $planId);
            header('Location: index.php?page=subscriptions&msg=created');
            exit;
        }
    }

    /**
     * Return all subscriptions with joined customer and plan data.
     * Used by the subscriptions view to render the table.
     */
    public function list() {
        return $this->subscription->getAll();
    }

    /**
     * Toggle a subscription's status via URL parameters.
     * ?id= identifies the subscription; ?status= is the new status value.
     * Example: ?page=subscriptions&action=status&id=3&status=Suspended
     */
    public function updateStatus() {
        $id     = (int)($_GET['id']     ?? 0);
        $status = $_GET['status']       ?? 'Active'; // Allowed: Active | Suspended
        $this->subscription->updateStatus($id, $status);
        header('Location: index.php?page=subscriptions&msg=updated');
        exit;
    }
}
