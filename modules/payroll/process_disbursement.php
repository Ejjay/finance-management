<?php
session_start();
require_once '../../config/database.php';

// Initialize database connections
$db = new Database();
$conn = $db->getConnection('hr');
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

$payroll_id = isset($_POST['payroll_id']) ? intval($_POST['payroll_id']) : 0;
if (!$payroll_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payroll ID']);
    exit();
}

try {
    // Check if payroll exists and is in correct status
    $sql = "SELECT p.*, ap.status as payment_status, ap.id as payable_id 
            FROM payroll p 
            LEFT JOIN accounts_payable ap ON p.id = ap.payroll_id 
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$payroll_id]);
    $payroll = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payroll) {
        throw new Exception('Payroll record not found');
    }

    if ($payroll['payment_status'] !== 'pending') {
        throw new Exception('Invalid payment status. Only pending payments can be disbursed.');
    }

    if (!$payroll['payable_id']) {
        throw new Exception('No associated accounts payable record found.');
    }

    $disbursement_date = date('Y-m-d H:i:s');

    // Begin transactions for both connections
    $conn->beginTransaction();
    $finance_conn->beginTransaction();

    // Update accounts payable status
    $sql = "UPDATE accounts_payable SET status = 'disbursed', disbursement_date = ? WHERE id = ?";
    $stmt = $finance_conn->prepare($sql);
    $stmt->execute([$disbursement_date, $payroll['payable_id']]);

    // Update payroll status
    $sql = "UPDATE payroll SET payment_status = 'disbursed', disbursement_date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$disbursement_date, $payroll_id]);

    // Commit both transactions
    $finance_conn->commit();
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Payment disbursed successfully'
    ]);
} catch (Exception $e) {
    // Rollback both transactions on error
    if ($finance_conn->inTransaction()) {
        $finance_conn->rollBack();
    }
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to process disbursement',
        'message' => $e->getMessage()
    ]);
}

try {
    // Verify payroll exists and is in pending status
    $verify_sql = "SELECT id, total_salary, payment_status FROM payroll WHERE id = ? FOR UPDATE";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->execute([$payroll_id]);
    $payroll_data = $verify_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payroll_data) {
        throw new Exception("Payroll record not found");
    }
    
    if ($payroll_data['payment_status'] !== 'pending') {
        throw new Exception("Invalid payment status. Expected 'pending', got '" . $payroll_data['payment_status'] . "'");
    }
    
    // Verify corresponding accounts payable record
    $verify_ap_sql = "SELECT id FROM accounts_payable WHERE payroll_id = ? AND status = 'pending'";
    $verify_ap_stmt = $finance_conn->prepare($verify_ap_sql);
    $verify_ap_stmt->execute([$payroll_id]);
    $ap_data = $verify_ap_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ap_data) {
        throw new Exception("No pending accounts payable record found for this payroll");
    }
    
    if (!$payroll_data) {
        throw new Exception("Invalid payroll ID or payment already processed");
    }
    
    // Update payroll status
    $update_sql = "UPDATE payroll SET payment_status = 'paid', disbursement_date = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->execute([$disbursement_date, $payroll_id]);
    
    // Update accounts payable status
    $update_ap_sql = "UPDATE accounts_payable SET status = 'paid', payment_date = ? WHERE payroll_id = ?";
    $update_ap_stmt = $finance_conn->prepare($update_ap_sql);
    $update_ap_stmt->execute([$disbursement_date, $payroll_id]);
    
    // Record disbursement in finance table
    $finance_sql = "INSERT INTO disbursements (payroll_id, amount, disbursement_date, status) VALUES (?, ?, ?, 'completed')";
    $finance_stmt = $finance_conn->prepare($finance_sql);
    $finance_stmt->execute([$payroll_id, $payroll_data['total_salary'], $disbursement_date]);
    
    // Commit both transactions
    $conn->commit();
    $finance_conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Disbursement processed successfully']);
} catch (Exception $e) {
    // Rollback both transactions
    $conn->rollBack();
    $finance_conn->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error processing disbursement: ' . $e->getMessage()]);
}