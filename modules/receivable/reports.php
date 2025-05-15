<?php
session_start();
require_once '../../config/database.php';

// Initialize database connection
$db = new Database();
$conn = $db->getConnection('receivable');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Initialize message variable
$msg = '';

// Handle search and filtering
$search = $_GET['search'] ?? '';
$aging_bracket = $_GET['aging_bracket'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$status = $_GET['status'] ?? '';

// Build the query
$where_conditions = ['1=1'];
$params = [];

if ($search) {
    $where_conditions[] = '(student_id LIKE ? OR student_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($aging_bracket) {
    $bracket = $conn->query("SELECT days_from, days_to FROM aging_brackets WHERE id = $aging_bracket")->fetch(PDO::FETCH_ASSOC);
    if ($bracket) {
        $where_conditions[] = "DATEDIFF(CURRENT_DATE, due_date) BETWEEN ? AND ?";
        $params[] = $bracket['days_from'];
        $params[] = $bracket['days_to'];
    }
}

if ($date_from) {
    $where_conditions[] = 'billing_date >= ?';
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = 'billing_date <= ?';
    $params[] = $date_to;
}

if ($status) {
    $where_conditions[] = 'status = ?';
    $params[] = $status;
}

$where_clause = implode(' AND ', $where_conditions);
$sql = "SELECT *, 
        DATEDIFF(CURRENT_DATE, due_date) as days_overdue,
        CASE 
            WHEN DATEDIFF(CURRENT_DATE, due_date) <= 30 THEN 'Current'
            WHEN DATEDIFF(CURRENT_DATE, due_date) BETWEEN 31 AND 60 THEN '31-60 Days'
            WHEN DATEDIFF(CURRENT_DATE, due_date) BETWEEN 61 AND 90 THEN '61-90 Days'
            ELSE 'Over 90 Days'
        END as aging_category
        FROM student_billing 
        WHERE $where_clause 
        ORDER BY due_date ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$receivables = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate aging summary
$aging_summary = [
    'Current' => ['count' => 0, 'amount' => 0],
    '31-60 Days' => ['count' => 0, 'amount' => 0],
    '61-90 Days' => ['count' => 0, 'amount' => 0],
    'Over 90 Days' => ['count' => 0, 'amount' => 0]
];

foreach ($receivables as $receivable) {
    $aging_summary[$receivable['aging_category']]['count']++;
    $aging_summary[$receivable['aging_category']]['amount'] += $receivable['balance_amount'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AR Aging Reports - Finance System</title>
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
    <div class="main-content">
        <h2>AR Aging Reports</h2>

        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Aging Summary Cards -->
        <div class="row mb-4">
            <?php foreach ($aging_summary as $category => $data): ?>
            <div class="col-md-3">
                <div class="card aging-card <?php echo $category === 'Current' ? 'border-success' : 'border-danger'; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $category; ?></h5>
                        <p class="card-text mb-1">Count: <?php echo $data['count']; ?></p>
                        <p class="card-text">Amount: ₱<?php echo number_format($data['amount'], 2); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Search and Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search student..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="aging_bracket" class="form-select">
                            <option value="">All Aging Brackets</option>
                            <?php
                            $brackets = $conn->query("SELECT * FROM aging_brackets ORDER BY days_from")->fetchAll();
                            foreach ($brackets as $bracket) {
                                $selected = $aging_bracket == $bracket['id'] ? 'selected' : '';
                                echo "<option value='{$bracket['id']}' $selected>{$bracket['bracket_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="reports.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Receivables Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Billing Date</th>
                                <th>Due Date</th>
                                <th>Days Overdue</th>
                                <th>Total Amount</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Aging Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receivables as $receivable): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($receivable['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($receivable['student_name']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($receivable['billing_date'])); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($receivable['due_date'])); ?></td>
                                <td><?php echo $receivable['days_overdue']; ?></td>
                                <td>₱<?php echo number_format($receivable['total_amount'], 2); ?></td>
                                <td>₱<?php echo number_format($receivable['balance_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $receivable['status'] === 'paid' ? 'success' : ($receivable['status'] === 'partial' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($receivable['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $receivable['aging_category']; ?></td>
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