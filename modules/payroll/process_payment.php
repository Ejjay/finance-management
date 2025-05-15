<?php
session_start();
require_once '../../config/database.php';

// Initialize database connections
$db = new Database();
$conn = $db->getConnection('payroll');
$finance_conn = $db->getConnection('finance');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get payroll record
    $payroll_id = $_POST['payroll_id'];
    $sql = "SELECT * FROM payroll WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$payroll_id]);
    $payroll = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payroll) {
        throw new Exception('Payroll record not found');
    }

    // Start transaction
    $finance_conn->beginTransaction();

    // Create accounts payable record
    $sql = "INSERT INTO accounts_payable (payable_type, teacher_id, amount, description, due_date, status, payroll_id) 
            VALUES ('salary', :teacher_id, :amount, :description, :due_date, 'pending', :payroll_id)";
    
    $stmt = $finance_conn->prepare($sql);
    $stmt->execute([
        ':teacher_id' => $payroll['employee_id'],
        ':amount' => $payroll['total_salary'],
        ':description' => "Salary payment for " . $payroll['employee_name'] . " (" . $payroll['payroll_date'] . ")",
        ':due_date' => date('Y-m-d', strtotime('+1 week')),
        ':payroll_id' => $payroll_id
    ]);

    // Update payroll record status
    $sql = "UPDATE payroll SET payment_status = 'pending' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$payroll_id]);

    $finance_conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully'
    ]);

} catch (Exception $e) {
    if ($finance_conn->inTransaction()) {
        $finance_conn->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to process payment',
        'message' => $e->getMessage()
    ]);
}