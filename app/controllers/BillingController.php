<?php
/**
 * app/controllers/BillingController.php — Billing Controller
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Handles HTTP input for billing operations:
 *   - Generating invoices for active subscriptions
 *   - Recording payments against unpaid invoices
 *   - Listing invoices and payments for the billing view
 */
class BillingController {
    /** @var Invoice The invoice model instance */
    private $invoice;

    /** @var Payment The payment model instance */
    private $payment;

    /**
     * Constructor — receives both models via dependency injection.
     * Billing requires both Invoice and Payment to function.
     */
    public function __construct($invoiceModel, $paymentModel) {
        $this->invoice = $invoiceModel;
        $this->payment = $paymentModel;
    }

    /**
     * Handle the generate-invoice form submission.
     * Reads the subscription ID and amount from POST,
     * then calls Invoice::generate() which sets due_date to +30 days.
     */
    public function generateInvoice() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subId  = (int)$_POST['subscription_id'];   // Must be an existing subscription
            $amount = (float)$_POST['amount'];           // Invoice amount in RWF
            $this->invoice->generate($subId, $amount);
            header('Location: index.php?page=billing&msg=generated');
            exit;
        }
    }

    /**
     * Handle the record-payment form submission.
     * Reads invoice ID, amount, and payment method from POST.
     * Payment::record() inserts the payment AND marks the invoice as Paid.
     */
    public function payInvoice() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $invoiceId = (int)$_POST['invoice_id'];          // Must be an unpaid invoice
            $amount    = (float)$_POST['amount'];
            $method    = $_POST['method'] ?? 'Cash';         // Cash | MoMo | Bank Transfer
            $this->payment->record($invoiceId, $amount, $method);
            header('Location: index.php?page=billing&msg=paid');
            exit;
        }
    }

    /**
     * Return all invoices with customer and plan details joined.
     * Used by the billing view to render the invoices table.
     */
    public function listInvoices() {
        return $this->invoice->getAll();
    }

    /**
     * Return all payment records with customer details joined.
     * Available for future use in a payments history view.
     */
    public function listPayments() {
        return $this->payment->getAll();
    }
}
