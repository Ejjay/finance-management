<?php
require_once '../../config/database.php';
require_once '../../classes/BudgetManagement.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $budgetManagement = new BudgetManagement();
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $department = $_POST['department'];
            $amount = $_POST['amount'];
            $percentage = $_POST['percentage'];
            $fiscalYear = $_POST['fiscal_year'] ?? date('Y');

            $success = $budgetManagement->addBudgetAllocation($department, $amount, $percentage, $fiscalYear);
            echo json_encode(['success' => $success]);
            break;

        case 'update':
            $allocationId = $_POST['allocation_id'];
            $department = $_POST['department'];
            $amount = $_POST['amount'];
            $percentage = $_POST['percentage'];

            $success = $budgetManagement->updateBudgetAllocation($allocationId, $department, $amount, $percentage);
            echo json_encode(['success' => $success]);
            break;

        case 'delete':
            $allocationId = $_POST['allocation_id'];
            $success = $budgetManagement->deleteBudgetAllocation($allocationId);
            echo json_encode(['success' => $success]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}