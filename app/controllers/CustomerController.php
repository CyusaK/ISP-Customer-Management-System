<?php
/**
 * app/controllers/CustomerController.php — Customer Controller
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Handles HTTP input for all customer-related actions:
 * registering, editing, and deleting customers.
 * Each method reads from $_POST / $_GET, calls the Customer model,
 * then either redirects or returns data for the view.
 */
class CustomerController {
    /** @var Customer The customer model instance */
    private $customer;

    /** Constructor — receives the Customer model via dependency injection */
    public function __construct($customerModel) {
        $this->customer = $customerModel;
    }

    /**
     * Handle the customer registration form.
     * On GET: returns an empty string (view shows the blank form).
     * On POST: validates input, calls model to save, then redirects.
     * Returns an error message string if validation fails.
     */
    public function register() {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize all text inputs by trimming whitespace
            $name    = trim($_POST['name']    ?? '');
            $email   = trim($_POST['email']   ?? '');
            $phone   = trim($_POST['phone']   ?? '');
            $address = trim($_POST['address'] ?? '');

            // Name and email are the minimum required fields
            if ($name && $email) {
                $this->customer->register($name, $email, $phone, $address);
                // Redirect with a query param so the view can show a success banner
                header('Location: index.php?page=customers&msg=registered');
                exit;
            }
            $message = 'Name and Email are required.';
        }
        return $message; // Passed to the view to display inline
    }

    /**
     * Return all customers for the list table in the customers view.
     * Delegates directly to the model's getAll() method.
     */
    public function list() {
        return $this->customer->getAll();
    }

    /**
     * Handle the edit customer form.
     * On GET: loads the customer record by ?id= and returns it for the form.
     * On POST: saves the updated fields and redirects with a success message.
     */
    public function edit() {
        $id = (int)($_GET['id'] ?? 0); // Cast to int to prevent injection via URL
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->customer->update(
                $id,
                trim($_POST['name']),
                trim($_POST['email']),
                trim($_POST['phone']),
                trim($_POST['address']),
                $_POST['status'] // Enum: Active | Suspended | Inactive
            );
            header('Location: index.php?page=customers&msg=updated');
            exit;
        }
        // GET: return the customer row so the view can pre-fill the form fields
        return $this->customer->getById($id);
    }

    /**
     * Delete a customer by the ?id= URL parameter.
     * Redirects back to the customers list after deletion.
     * The ON DELETE CASCADE in the DB removes related subscriptions/invoices too.
     */
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $this->customer->delete($id);
        header('Location: index.php?page=customers&msg=deleted');
        exit;
    }
}
