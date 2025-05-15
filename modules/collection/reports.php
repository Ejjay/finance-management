<?php
session_start();
require_once '../../config/database.php';

// Initialize database connection
$db = new Database();
$conn = $db->getConnection('collection');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Initialize message variable
$msg = '';

// Handle date range filtering
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-t'); // Last day of current month

// Fetch payment summary
$payment_summary = $conn->query("
    SELECT 
        COUNT(*) as total_payments,
        SUM(amount_paid) as total_amount,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payments,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payments,
        COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments
    FROM student_payments
    WHERE payment_date BETWEEN '$date_from' AND '$date_to'
")->fetch(PDO::FETCH_ASSOC);

// Fetch recent payments
$recent_payments = $conn->query("
    SELECT sp.*, fs.fee_name
    FROM student_payments sp
    LEFT JOIN fee_schedules fs ON sp.fee_schedule_id = fs.id
    WHERE sp.payment_date BETWEEN '$date_from' AND '$date_to'
    ORDER BY sp.payment_date DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch due summary
$due_summary = $conn->query("
    SELECT 
        COUNT(*) as total_dues,
        SUM(amount_due) as total_due_amount,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_dues,
        COUNT(CASE WHEN status = 'overdue' THEN 1 END) as overdue_dues,
        COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_dues
    FROM due_records
    WHERE due_date BETWEEN '$date_from' AND '$date_to'
")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection Reports - Finance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../../logo.png" alt="Logo">
            <h5 class="mt-3">Finance System</h5>
        </div>
        <nav class="mt-4">
            <div class="nav-link active">
            <a href="../../dashboard.php" class="nav-link active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            </div>

            <div class="nav-link">
                <div><i class="fas fa-money-check-alt"></i>Payroll</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="../payroll/Disbursement.php" class="nav-link">Disbursement</a></li>
                <li><a href="../payroll/index.php" class="nav-link">Payroll</a></li>
                <li><a href="../payroll/benefits.php" class="nav-link">Staff Benefits Management</a></li>
                <li><a href="../payroll/attendance.php" class="nav-link">Attendance Integration</a></li>
                <li><a href="../payroll/archive.php" class="nav-link">Archive</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-chart-pie"></i> Budget Management</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="../budget/budget_planning.php" class="nav-link">Annual Budget Planning</a></li>
                <li><a href="../budget/allocation.php" class="nav-link">Departmental Budget Allocation</a></li>
                <li><a href="../budget/tracking.php" class="nav-link">Budget Revision Tracking</a></li>
                <li><a href="../budget/tracking.php" class="nav-link">Multi-Year Budget Forecasting</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-hand-holding-usd"></i> Collection</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="fees.php" class="nav-link">Fee Collection</a></li>
                <li><a href="dues.php" class="nav-link">Due Management</a></li>
                <li><a href="reports.php" class="nav-link">Collection Report</a></li>
                <li><a href="receipt.php" class="nav-link">Receipt Generation</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-book"></i> General Ledger</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="../ledger/entries.php" class="nav-link">Journal Entry Management</a></li>
                <li><a href="../ledger/accounts.php" class="nav-link">Chart of Accounts</a></li>
                <li><a href="../ledger/tracking.php" class="nav-link">Fund Transfer Tracking</a></li>
                <li><a href="../ledger/reconcile.php" class="nav-link">Ledger Reconciliation</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-file-invoice-dollar"></i> Accounts Payable</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="../payable/reports.php" class="nav-link">AP Aging Reports</a></li>
                <li><a href="../payable/vendor.php" class="nav-link">Vendor Management</a></li>
                <li><a href="../payable/invoice.php" class="nav-link">Invoice Processing</a></li>
                <li><a href="../payable/tax.php" class="nav-link">Tax & Compliance Checks</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-file-invoice"></i> Accounts Receivable</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="../receivable/reports.php" class="nav-link">AR Aging Reports</a></li>
                <li><a href="../receivable/payment.php" class="nav-link">Payment Posting</a></li>
                <li><a href="../receivable/billing.php" class="nav-link">Student Billing & Invoicing</a></li>
                <li><a href="../receivable/collection.php" class="nav-link">Collection Follow-ups</a></li>
            </ul>
            <a href="../../logout.php" class="nav-link mt-4">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>
    <div class="main-content">
        <h2>Collection Reports</h2>
        
        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Date Range Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <!-- Payment Summary Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h6>Total Payments</h6>
                                    <h3><?php echo $payment_summary['total_payments']; ?></h3>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h6>Total Amount</h6>
                                    <h3>₱<?php echo number_format($payment_summary['total_amount'], 2); ?></h3>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3 text-success">
                                    <h6>Completed</h6>
                                    <h4><?php echo $payment_summary['completed_payments']; ?></h4>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3 text-warning">
                                    <h6>Pending</h6>
                                    <h4><?php echo $payment_summary['pending_payments']; ?></h4>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3 text-danger">
                                    <h6>Failed</h6>
                                    <h4><?php echo $payment_summary['failed_payments']; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Due Summary Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Due Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h6>Total Dues</h6>
                                    <h3><?php echo $due_summary['total_dues']; ?></h3>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h6>Total Due Amount</h6>
                                    <h3>₱<?php echo number_format($due_summary['total_due_amount'], 2); ?></h3>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3 text-success">
                                    <h6>Paid</h6>
                                    <h4><?php echo $due_summary['paid_dues']; ?></h4>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3 text-warning">
                                    <h6>Pending</h6>
                                    <h4><?php echo $due_summary['pending_dues']; ?></h4>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3 text-danger">
                                    <h6>Overdue</h6>
                                    <h4><?php echo $due_summary['overdue_dues']; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Payments</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Fee Name</th>
                                <th>Amount Paid</th>
                                <th>Payment Date</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($payment['fee_name']); ?></td>
                                <td>₱<?php echo number_format($payment['amount_paid'], 2); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($payment['payment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $payment['status'] === 'completed' ? 'success' : ($payment['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/navigation.js"></script>
</body>
</html>