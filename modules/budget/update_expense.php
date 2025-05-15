<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = new Database();
    $budgetConn = $db->getConnection('budget');

    // Get form data
    $expenseId = $_POST['expense_id'];
    $department = $_POST['department'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    // Get department_budget_id
    $stmt = $budgetConn->prepare("SELECT dept_budget_id FROM department_budget WHERE department_name = ?");
    $stmt->execute([$department]);
    $deptBudgetId = $stmt->fetchColumn();

    // Get category_id
    $stmt = $budgetConn->prepare("SELECT category_id FROM budget_categories WHERE category_name = ?");
    $stmt->execute([$category]);
    $categoryId = $stmt->fetchColumn();

    // Update expense
    $sql = "UPDATE expenses 
            SET dept_budget_id = ?, category_id = ?, amount = ?, description = ? 
            WHERE expense_id = ?";
    $stmt = $budgetConn->prepare($sql);
    $success = $stmt->execute([$deptBudgetId, $categoryId, $amount, $description, $expenseId]);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update expense']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}