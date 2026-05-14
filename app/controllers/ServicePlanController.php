<?php
/**
 * app/controllers/ServicePlanController.php — Service Plan Controller
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Handles HTTP input for service plan management:
 * listing all plans, creating a new plan, and deleting a plan.
 */
class ServicePlanController {
    /** @var ServicePlan The service plan model instance */
    private $plan;

    /** Constructor — receives the ServicePlan model via dependency injection */
    public function __construct($planModel) {
        $this->plan = $planModel;
    }

    /**
     * Return all service plans for the plans view table and dropdowns.
     * Results are ordered by price ascending (cheapest first).
     */
    public function showPlans() {
        return $this->plan->getAll();
    }

    /**
     * Handle the add-plan form submission.
     * Reads POST fields, casts price to float, then saves via the model.
     * Redirects with a success message after creation.
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->plan->create(
                trim($_POST['name']),
                trim($_POST['speed']),
                (float)$_POST['price'],       // Cast to float for decimal accuracy
                trim($_POST['description'])
            );
            header('Location: index.php?page=plans&msg=created');
            exit;
        }
    }

    /**
     * Delete a service plan by the ?id= URL parameter.
     * Note: if the plan is referenced by a subscription, MySQL will block
     * the deletion due to the FOREIGN KEY constraint.
     */
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $this->plan->delete($id);
        header('Location: index.php?page=plans&msg=deleted');
        exit;
    }
}
