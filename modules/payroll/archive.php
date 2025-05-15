<?php
session_start();
require_once '../../config/database.php';

// Initialize database connections
$db = new Database();
$conn = $db->getConnection('hr');
$hr_conn = $db->getConnection('hr');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Initialize message variable
$msg = '';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'restore':
                $record_id = $_POST['record_id'];
                $sql = "UPDATE payroll SET is_archived = FALSE, archive_date = NULL WHERE id = ?";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$record_id]);
                    $msg = "Record restored successfully.";
                }
                break;

            case 'delete':
                $record_id = $_POST['record_id'];
                $sql = "DELETE FROM payroll WHERE id = ? AND is_archived = TRUE";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$record_id]);
                    $msg = "Record deleted permanently.";
                }
                break;

            case 'bulk_archive':
                $selected_records = $_POST['selected_records'] ?? [];
                if (!empty($selected_records)) {
                    $archive_date = date('Y-m-d H:i:s');
                    $placeholders = str_repeat('?,', count($selected_records) - 1) . '?';
                    $sql = "UPDATE payroll SET is_archived = TRUE, archive_date = ? WHERE id IN ($placeholders)";
                    
                    if ($stmt = $conn->prepare($sql)) {
                        $params = array_merge([$archive_date], $selected_records);
                        $stmt->execute($params);
                        $msg = "Selected records archived successfully.";
                    }
                }
                break;
        }
    }
}

// Handle search and filtering
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$status = $_GET['status'] ?? '';

// Build the query
$where_conditions = ['is_archived = TRUE'];
$params = [];

if ($search) {
    $where_conditions[] = '(employee_name LIKE ? OR employee_id LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($date_from) {
    $where_conditions[] = 'archive_date >= ?';
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = 'archive_date <= ?';
    $params[] = $date_to;
}

if ($status) {
    $where_conditions[] = 'payment_status = ?';
    $params[] = $status;
}

$where_clause = implode(' AND ', $where_conditions);
$sql = "SELECT * FROM payroll WHERE $where_clause ORDER BY archive_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$archived_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate archive statistics
$stats_sql = "SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN payment_status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_count,
                AVG(total_salary) as avg_salary
             FROM payroll 
             WHERE is_archived = TRUE";
$stats_result = $conn->query($stats_sql);
$archive_stats = $stats_result->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Archives - Finance System</title>
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
    <div class="main-content">
        <h2>Payroll Archives</h2>
        
        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Archive Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>Total Archives</h5>
                        <h3><?php echo $archive_stats['total_records']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Paid Records</h5>
                        <h3><?php echo $archive_stats['paid_count']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5>Unpaid Records</h5>
                        <h3><?php echo $archive_stats['unpaid_count']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5>Average Salary</h5>
                        <h3>₱<?php echo number_format($archive_stats['avg_salary'], 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search employee..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="unpaid" <?php echo $status === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="archive.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Archived Records Table -->
        <div class="card">
            <div class="card-body">
                <form method="POST" id="archiveForm">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Payroll Date</th>
                                    <th>Archive Date</th>
                                    <th>Total Salary</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($archived_records as $record): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_records[]" value="<?php echo $record['id']; ?>">
                                    </td>
                                    <td><?php echo htmlspecialchars($record['employee_id']); ?></td>
                                    <td><?php echo htmlspecialchars($record['employee_name']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($record['payroll_date'])); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($record['archive_date'])); ?></td>
                                    <td>₱<?php echo number_format($record['total_salary'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $record['payment_status'] === 'paid' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($record['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="restore">
                                            <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this record?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle select all checkbox
        document.getElementById('selectAll').addEventListener('change', function() {
            var checkboxes = document.getElementsByName('selected_records[]');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        });
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>