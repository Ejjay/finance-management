<?php
require_once '../../config/database.php';
session_start();

class APAgingReports {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAgingReport($asOfDate = null) {
        if (!$asOfDate) {
            $asOfDate = date('Y-m-d');
        }

        $aging_brackets = [
            'current' => 0,
            '1_30_days' => 0,
            '31_60_days' => 0,
            '61_90_days' => 0,
            'over_90_days' => 0
        ];

        $sql = "SELECT 
                    ap.id as invoice_id,
                    ap.payment_reference as invoice_number,
                    v.name as vendor_name,
                    ap.created_at as invoice_date,
                    ap.due_date,
                    ap.amount as total_amount,
                    CASE WHEN ap.status = 'paid' THEN ap.amount ELSE 0 END as paid_amount,
                    CASE WHEN ap.status = 'paid' THEN 0 ELSE ap.amount END as remaining_amount,
                    DATEDIFF(?, ap.due_date) as days_overdue
                FROM accounts_payable ap
                LEFT JOIN vendors v ON ap.vendor_id = v.id
                WHERE ap.status != 'paid'
                ORDER BY ap.due_date ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $asOfDate);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $aging_details = [];
        foreach ($result as $row) {
            $days_overdue = $row['days_overdue'];
            $remaining = $row['remaining_amount'];

            // Categorize into aging brackets
            if ($days_overdue >= 0) {
                $aging_brackets['current'] += $remaining;
            } elseif ($days_overdue > -30) {
                $aging_brackets['1_30_days'] += $remaining;
            } elseif ($days_overdue > -60) {
                $aging_brackets['31_60_days'] += $remaining;
            } elseif ($days_overdue > -90) {
                $aging_brackets['61_90_days'] += $remaining;
            } else {
                $aging_brackets['over_90_days'] += $remaining;
            }

            $aging_details[] = $row;
        }

        return [
            'summary' => $aging_brackets,
            'details' => $aging_details
        ];
    }
}

$apAging = new APAgingReports();
$aging_data = $apAging->getAgingReport();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AP Aging Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
                <li><a href="reports.php" class="nav-link">AP Aging Reports</a></li>
                <li><a href="vendor.php" class="nav-link">Vendor Management</a></li>
                <li><a href="invoice.php" class="nav-link">Invoice Processing</a></li>
                <li><a href="tax.php" class="nav-link">Tax & Compliance Checks</a></li>
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
            <a href="logout.php" class="nav-link mt-4">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>
    <div class="main-content">
        <h2 class="mb-4">AP Aging Reports</h2>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Current</h6>
                        <h3 class="card-text text-success">$<?= number_format($aging_data['summary']['current'], 2) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title text-muted">1-30 Days</h6>
                        <h3 class="card-text text-warning">$<?= number_format($aging_data['summary']['1_30_days'], 2) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title text-muted">31-60 Days</h6>
                        <h3 class="card-text text-warning">$<?= number_format($aging_data['summary']['31_60_days'], 2) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title text-muted">61-90 Days</h6>
                        <h3 class="card-text text-danger">$<?= number_format($aging_data['summary']['61_90_days'], 2) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Over 90 Days</h6>
                        <h3 class="card-text text-danger">$<?= number_format($aging_data['summary']['over_90_days'], 2) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Report Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detailed Aging Report</h5>
            </div>
            <div class="card-body">
                <table id="aging-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Vendor</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Remaining</th>
                            <th>Days Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aging_data['details'] as $invoice): ?>
                        <tr>
                            <td><?= htmlspecialchars($invoice['invoice_number']) ?></td>
                            <td><?= htmlspecialchars($invoice['vendor_name']) ?></td>
                            <td><?= htmlspecialchars($invoice['invoice_date']) ?></td>
                            <td><?= htmlspecialchars($invoice['due_date']) ?></td>
                            <td>$<?= number_format($invoice['total_amount'], 2) ?></td>
                            <td>$<?= number_format($invoice['paid_amount'], 2) ?></td>
                            <td>$<?= number_format($invoice['remaining_amount'], 2) ?></td>
                            <td><?= abs($invoice['days_overdue']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#aging-table').DataTable({
                order: [[7, 'desc']],
                pageLength: 25
            });
        });
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>