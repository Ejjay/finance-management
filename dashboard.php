<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Finance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="logo.png" alt="Logo">
            <h5 class="mt-3">Finance System</h5>
        </div>
        <nav class="mt-4">
            <div class="nav-link active">
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            </div>

            <div class="nav-link">
                <div><i class="fas fa-money-check-alt"></i>Payroll</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="modules/payroll/Disbursement.php" class="nav-link">Disbursement</a></li>
                <li><a href="modules/payroll/index.php" class="nav-link">Payroll</a></li>
                <li><a href="modules/payroll/reports.php" class="nav-link">Staff Benefits Management</a></li>
                <li><a href="modules/payroll/reports.php" class="nav-link">Attendance Integration</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-chart-pie"></i> Budget Management</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="modules/budget/budget_planning.php" class="nav-link">Annual Budget Planning</a></li>
                <li><a href="modules/budget/allocation.php" class="nav-link">Departmental Budget Allocation</a></li>
                <li><a href="modules/budget/tracking.php" class="nav-link">Budget Revision Tracking</a></li>
                <li><a href="modules/budget/tracking.php" class="nav-link">Multi-Year Budget Forecasting</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-hand-holding-usd"></i> Collection</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="modules/collection/fees.php" class="nav-link">Fee Collection</a></li>
                <li><a href="modules/collection/dues.php" class="nav-link">Due Management</a></li>
                <li><a href="modules/collection/reports.php" class="nav-link">Collection Reports</a></li>
                <li><a href="modules/collection/receipt.php" class="nav-link">Receipt Generationt</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-book"></i> General Ledger</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="modules/ledger/entries.php" class="nav-link">Journal Entry Management</a></li>
                <li><a href="modules/ledger/accounts.php" class="nav-link">Chart of Accounts</a></li>
                <li><a href="modules/ledger/tracking.php" class="nav-link">Fund Transfer Tracking</a></li>
                <li><a href="modules/ledger/reconcile.php" class="nav-link">Ledger Reconciliation</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-file-invoice-dollar"></i> Accounts Payable</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="modules/payable/reports.php" class="nav-link">AP Aging Reports</a></li>
                <li><a href="modules/payable/vendor.php" class="nav-link">Vendor Management</a></li>
                <li><a href="modules/payable/invoice.php" class="nav-link">Invoice Processing</a></li>
                <li><a href="modules/payable/tax.php" class="nav-link">Tax & Compliance Checks</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-file-invoice"></i> Accounts Receivable</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="modules/receivable/reports.php" class="nav-link">AR Aging Reports</a></li>
                <li><a href="modules/receivable/payment.php" class="nav-link">Payment Posting</a></li>
                <li><a href="modules/receivable/billing.php" class="nav-link">Student Billing & Invoicing</a></li>
                <li><a href="modules/receivable/collection.php" class="nav-link">Collection Follow-ups</a></li>
            </ul>
            <a href="logout.php" class="nav-link mt-4">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>
    <div class="main-content">
        <h2 class="mb-4">Dashboard</h2>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/navigation.js"></script>
</body>
</html>