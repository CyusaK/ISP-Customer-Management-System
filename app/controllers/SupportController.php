<?php
// app/controllers/SupportController.php
class SupportController {
    private $ticket;

    public function __construct($ticketModel) {
        $this->ticket = $ticketModel;
    }

    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customerId = (int)$_POST['customer_id'];
            $issue      = trim($_POST['issue'] ?? '');
            if ($customerId && $issue) {
                $this->ticket->create($customerId, $issue);
                header('Location: index.php?page=support&msg=submitted');
                exit;
            }
        }
    }

    public function list() {
        return $this->ticket->getAll();
    }

    public function updateStatus() {
        $id     = (int)($_GET['id'] ?? 0);
        $status = $_GET['status'] ?? 'Open';
        $this->ticket->updateStatus($id, $status);
        header('Location: index.php?page=support&msg=updated');
        exit;
    }
}
