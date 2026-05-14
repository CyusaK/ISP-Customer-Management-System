<?php
/**
 * public/index.php — Front Controller (Admin Panel)
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * This is the single entry point for all admin panel requests.
 * It follows the MVC pattern: loads models, instantiates controllers,
 * reads the ?page= query parameter to decide which module to run,
 * then includes the shared layout which renders the correct view.
 */

session_start();

// ── Authentication guard ──────────────────────────────────────────────────────
// If no admin session exists, redirect to the login page immediately.
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Show all PHP errors during development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ── Load dependencies ─────────────────────────────────────────────────────────
// Database connection (defines $conn as a mysqli object)
require_once __DIR__ . '/../config/database.php';

// Models — each class wraps one database table and its queries
require_once __DIR__ . '/../app/models/Customer.php';
require_once __DIR__ . '/../app/models/ServicePlan.php';
require_once __DIR__ . '/../app/models/Subscription.php';
require_once __DIR__ . '/../app/models/Invoice.php';
require_once __DIR__ . '/../app/models/Payment.php';
require_once __DIR__ . '/../app/models/SupportTicket.php';

// Controllers — each class handles HTTP input and calls the appropriate model
require_once __DIR__ . '/../app/controllers/CustomerController.php';
require_once __DIR__ . '/../app/controllers/ServicePlanController.php';
require_once __DIR__ . '/../app/controllers/SubscriptionController.php';
require_once __DIR__ . '/../app/controllers/BillingController.php';
require_once __DIR__ . '/../app/controllers/SupportController.php';

// ── Instantiate models ────────────────────────────────────────────────────────
// Pass the shared $conn to every model so they all use the same DB connection
$customerModel     = new Customer($conn);
$planModel         = new ServicePlan($conn);
$subscriptionModel = new Subscription($conn);
$invoiceModel      = new Invoice($conn);
$paymentModel      = new Payment($conn);

// ── Instantiate controllers ───────────────────────────────────────────────────
$customerCtrl     = new CustomerController($customerModel);
$planCtrl         = new ServicePlanController($planModel);
$subscriptionCtrl = new SubscriptionController($subscriptionModel);
$billingCtrl      = new BillingController($invoiceModel, $paymentModel);
$ticketModel      = new SupportTicket($conn);
$supportCtrl      = new SupportController($ticketModel);

// ── Read routing parameters from the URL ─────────────────────────────────────
// ?page=  determines which module/view to show (default: dashboard)
// ?action= determines the sub-action within that module (e.g. edit, delete)
$page   = $_GET['page']   ?? 'dashboard';
$action = $_GET['action'] ?? '';

// Shared variables that views may need
$data    = null;   // used by the edit form to pre-populate fields
$message = '';     // used to pass validation error text to the view

// ── Route requests to the correct controller method ───────────────────────────
if ($page === 'customers') {
    if ($action === 'delete') {
        // DELETE: remove a customer record by ID
        $customerCtrl->delete();
    } elseif ($action === 'edit') {
        // EDIT: on GET load the customer data; on POST save the changes
        $data = $customerCtrl->edit();
    } else {
        // Default: show the register form; on POST process the registration
        $message = $customerCtrl->register();
    }

} elseif ($page === 'plans') {
    if ($action === 'delete') {
        // DELETE: remove a service plan by ID
        $planCtrl->delete();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // POST: create a new service plan
        $planCtrl->create();
    }

} elseif ($page === 'subscriptions') {
    if ($action === 'status') {
        // Toggle subscription status between Active and Suspended
        $subscriptionCtrl->updateStatus();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // POST: create a new subscription linking a customer to a plan
        $subscriptionCtrl->subscribe();
    }

} elseif ($page === 'billing') {
    if ($action === 'pay') {
        // POST: record a payment against an unpaid invoice
        $billingCtrl->payInvoice();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // POST: generate a new invoice for an active subscription
        $billingCtrl->generateInvoice();
    }

} elseif ($page === 'support') {
    if ($action === 'status') {
        // Toggle ticket status: Open -> In Progress -> Resolved
        $supportCtrl->updateStatus();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // POST: submit a new support ticket
        $supportCtrl->submit();
    }
}

// ── Dashboard statistics ──────────────────────────────────────────────────────
// These counts are used by the dashboard stat cards.
// Wrapped in try/catch so the page still loads if a table is missing.
try {
    $stats = [
        'customers'     => $customerModel->count(),       // total registered customers
        'subscriptions' => $subscriptionModel->count(),   // active subscriptions only
        'unpaid'        => $invoiceModel->countUnpaid(),  // invoices not yet paid
        'revenue'       => $invoiceModel->totalRevenue(), // sum of all paid invoices
        'open_tickets'  => $ticketModel->countOpen(),     // open support tickets
    ];
} catch (Exception $e) {
    // Fallback to zeros if the database tables do not exist yet
    $stats = ['customers' => 0, 'subscriptions' => 0, 'unpaid' => 0, 'revenue' => 0];
}

// ── Render the layout ─────────────────────────────────────────────────────────
// layout.php wraps every page: it draws the sidebar, topbar, flash messages,
// then includes the correct view file based on $page.
include __DIR__ . '/../app/views/layout.php';
