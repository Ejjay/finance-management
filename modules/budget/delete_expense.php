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

    $expenseId = $_POST['expense_id'];

    // Delete the expense
    $sql = "DELETE FROM expenses WHERE expense_id = ?";
    $stmt = $budgetConn->prepare($sql);
    $success = $stmt->execute([$expenseId]);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete expense']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}