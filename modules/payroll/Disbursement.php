<?php
session_start();
require_once '../../config/database.php';

// Initialize database connections
$db = new Database();
$conn = $db->getConnection('hr');
$finance_conn = $db->getConnection('finance');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Initialize message variable
$msg = '';

// Handle disbursement processing is now done via AJAX
// See process_disbursement.php for the processing logic

// Fetch pending disbursements
$pending_sql = "SELECT * FROM payroll WHERE payment_status = 'unpaid' ORDER BY payroll_date DESC";
$pending_result = $conn->query($pending_sql);
$pending_disbursements = $pending_result->fetchAll(PDO::FETCH_ASSOC);

// Fetch completed disbursements
$completed_sql = "SELECT p.*, d.disbursement_date 
                 FROM payroll p 
                 JOIN disbursements d ON p.id = d.payroll_id 
                 WHERE p.payment_status = 'paid' 
                 ORDER BY d.disbursement_date DESC";
$completed_result = $conn->query($completed_sql);
$completed_disbursements = $completed_result->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Disbursement - Finance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="logo.png" alt="Logo">
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
                <li><a href="Disbursement.php" class="nav-link">Disbursement</a></li>
                <li><a href="index.php" class="nav-link">Payroll</a></li>
                <li><a href="benefits.php" class="nav-link">Staff Benefits Management</a></li>
                <li><a href="attendance.php" class="nav-link">Attendance Integration</a></li>
                <li><a href="archive.php" class="nav-link">Archive</a></li>
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
                <li><a href="../collection/fees.php" class="nav-link">Fee Collection</a></li>
                <li><a href="../collection/dues.php" class="nav-link">Due Management</a></li>
                <li><a href="../collection/reports.php" class="nav-link">Collection Report</a></li>
                <li><a href="../collection/receipt.php" class="nav-link">Receipt Generation</a></li>
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
<body class="bg-light">
<div class="main-content">
        <h2>Payroll Disbursement Management</h2>
        
        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Pending Disbursements -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h4>Pending Disbursements</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Employee Name</th>
                                <th>Payroll Date</th>
                                <th>Total Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_disbursements as $disbursement): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($disbursement['employee_id']); ?></td>
                                <td><?php echo htmlspecialchars($disbursement['employee_name']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($disbursement['payroll_date'])); ?></td>
                                <td>₱<?php echo number_format($disbursement['total_salary'], 2); ?></td>
                                <td>
                                    <button onclick="processDisbursement(<?php echo $disbursement['id']; ?>)" class="btn btn-success btn-sm process-btn" data-payroll-id="<?php echo $disbursement['id']; ?>">
                                        <i class="fas fa-money-bill-wave"></i> <span class="btn-text">Process</span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Completed Disbursements -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4>Completed Disbursements</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Employee Name</th>
                                <th>Payroll Date</th>
                                <th>Disbursement Date</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completed_disbursements as $disbursement): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($disbursement['employee_id']); ?></td>
                                <td><?php echo htmlspecialchars($disbursement['employee_name']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($disbursement['payroll_date'])); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($disbursement['disbursement_date'])); ?></td>
                                <td>₱<?php echo number_format($disbursement['total_salary'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function processDisbursement(payrollId) {
        const btn = document.querySelector(`button[data-payroll-id="${payrollId}"]`);
        const spinner = btn.querySelector('.spinner-border');
        const btnText = btn.querySelector('.btn-text');

        // Disable button and show spinner
        btn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Processing...';

        $.ajax({
            url: 'process_disbursement.php',
            type: 'POST',
            data: { payroll_id: payrollId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    alert('Disbursement processed successfully!');
                    // Reload the page to update the tables
                    window.location.reload();
                } else {
                    alert(response.error || 'An error occurred while processing the disbursement');
                    // Reset button state
                    btn.disabled = false;
                    spinner.classList.add('d-none');
                    btnText.textContent = 'Process';
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred while processing the disbursement');
                // Reset button state
                btn.disabled = false;
                spinner.classList.add('d-none');
                btnText.textContent = 'Process';
            },
            timeout: 30000 // Set timeout to 30 seconds
        });
    }
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>