<?php
require_once 'BudgetPlanning.php';
session_start();

$budgetPlanning = new BudgetPlanning();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_revision') {
    $planId = $_POST['plan_id'];
    $allocationId = $_POST['allocation_id'];
    $previousAmount = $_POST['previous_amount'];
    $revisedAmount = $_POST['revised_amount'];
    $reason = $_POST['reason'];
    $budgetPlanning->createBudgetRevision($planId, $allocationId, $previousAmount, $revisedAmount, $reason);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Revision Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <li><a href="budget_planning.php" class="nav-link">Annual Budget Planning</a></li>
                <li><a href="allocation.php" class="nav-link">Departmental Budget Allocation</a></li>
                <li><a href="tracking.php" class="nav-link">Budget Revision Tracking</a></li>
                <li><a href="tracking.php" class="nav-link">Multi-Year Budget Forecasting</a></li>
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

    <div class="main-content">
        <h2 class="mb-4">Budget Revision Tracking</h2>

        <div class="card mb-4">
            <div class="card-header">
                <h4>Create Budget Revision</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="create_revision">
                    <div class="mb-3">
                        <label for="revision_plan_id" class="form-label">Budget Plan</label>
                        <select class="form-control" id="revision_plan_id" name="plan_id" required>
                            <!-- Populate with actual plans -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="allocation_id" class="form-label">Department Allocation</label>
                        <select class="form-control" id="allocation_id" name="allocation_id" required>
                            <!-- Populate with actual allocations -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="previous_amount" class="form-label">Previous Amount</label>
                        <input type="number" step="0.01" class="form-control" id="previous_amount" name="previous_amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="revised_amount" class="form-label">Revised Amount</label>
                        <input type="number" step="0.01" class="form-control" id="revised_amount" name="revised_amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Revision Reason</label>
                        <textarea class="form-control" id="reason" name="reason" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Revision</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/navigation.js"></script>
</body>
</html>