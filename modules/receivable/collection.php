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

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_followup':
                $sql = "INSERT INTO collection_followups (billing_id, followup_date, followup_type, contact_person, response, next_followup_date) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $_POST['billing_id'],
                    $_POST['followup_date'],
                    $_POST['followup_type'],
                    $_POST['contact_person'],
                    $_POST['response'],
                    $_POST['next_followup_date']
                ]);
                $msg = "Follow-up record created successfully.";
                break;

            case 'update_status':
                $sql = "UPDATE collection_followups SET status = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$_POST['status'], $_POST['followup_id']]);
                $msg = "Follow-up status updated successfully.";
                break;
        }
    }
}

// Handle search and filtering
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$status = $_GET['status'] ?? '';
$followup_type = $_GET['followup_type'] ?? '';

// Build the query for overdue billings
$where_conditions = ['sb.balance_amount > 0', 'DATEDIFF(CURRENT_DATE, sb.due_date) > 0'];
$params = [];

if ($search) {
    $where_conditions[] = '(sb.student_id LIKE ? OR sb.student_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($date_from) {
    $where_conditions[] = 'sb.due_date >= ?';
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = 'sb.due_date <= ?';
    $params[] = $date_to;
}

if ($status) {
    $where_conditions[] = 'COALESCE(cf.status, "pending") = ?';
    $params[] = $status;
}

if ($followup_type) {
    $where_conditions[] = 'cf.followup_type = ?';
    $params[] = $followup_type;
}

$where_clause = implode(' AND ', $where_conditions);
$sql = "SELECT sb.*, 
        DATEDIFF(CURRENT_DATE, sb.due_date) as days_overdue,
        cf.id as followup_id,
        cf.followup_date,
        cf.followup_type,
        cf.contact_person,
        cf.response,
        cf.next_followup_date,
        cf.status as followup_status
        FROM student_billing sb
        LEFT JOIN collection_followups cf ON sb.id = cf.billing_id
        WHERE $where_clause
        ORDER BY days_overdue DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$overdue_billings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate collection statistics
$stats_sql = "SELECT 
                COUNT(DISTINCT sb.id) as total_overdue,
                SUM(sb.balance_amount) as total_overdue_amount,
                COUNT(DISTINCT CASE WHEN cf.status = 'responded' THEN sb.id END) as responded_count,
                COUNT(DISTINCT CASE WHEN cf.status = 'resolved' THEN sb.id END) as resolved_count
             FROM student_billing sb
             LEFT JOIN collection_followups cf ON sb.id = cf.billing_id
             WHERE sb.balance_amount > 0 AND DATEDIFF(CURRENT_DATE, sb.due_date) > 0";
$stats_result = $conn->query($stats_sql);
$collection_stats = $stats_result->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection Follow-ups - Finance System</title>
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
        <h2>Collection Follow-ups</h2>

        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Collection Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card followup-card bg-danger text-white">
                    <div class="card-body">
                        <h5>Total Overdue</h5>
                        <h3><?php echo $collection_stats['total_overdue']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card followup-card bg-warning text-dark">
                    <div class="card-body">
                        <h5>Total Overdue Amount</h5>
                        <h3>₱<?php echo number_format($collection_stats['total_overdue_amount'], 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card followup-card bg-info text-white">
                    <div class="card-body">
                        <h5>Responded Cases</h5>
                        <h3><?php echo $collection_stats['responded_count']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card followup-card bg-success text-white">
                    <div class="card-body">
                        <h5>Resolved Cases</h5>
                        <h3><?php echo $collection_stats['resolved_count']; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <input type="text" name="search" class="form-control" placeholder="Search student..." value="<?php echo htmlspecialchars($search); ?>">
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
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="responded" <?php echo $status === 'responded' ? 'selected' : ''; ?>>Responded</option>
                            <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="followup_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="email" <?php echo $followup_type === 'email' ? 'selected' : ''; ?>>Email</option>
                            <option value="phone" <?php echo $followup_type === 'phone' ? 'selected' : ''; ?>>Phone</option>
                            <option value="letter" <?php echo $followup_type === 'letter' ? 'selected' : ''; ?>>Letter</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="collection.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Overdue Billings Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Days Overdue</th>
                                <th>Balance</th>
                                <th>Last Follow-up</th>
                                <th>Next Follow-up</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($overdue_billings as $billing): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($billing['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($billing['student_name']); ?></td>
                                <td><?php echo $billing['days_overdue']; ?> days</td>
                                <td>₱<?php echo number_format($billing['balance_amount'], 2); ?></td>
                                <td>
                                    <?php if ($billing['followup_date']): ?>
                                        <?php echo date('Y-m-d', strtotime($billing['followup_date'])); ?>
                                        (<?php echo ucfirst($billing['followup_type']); ?>)
                                    <?php else: ?>
                                        No follow-up yet
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $billing['next_followup_date'] ? date('Y-m-d', strtotime($billing['next_followup_date'])) : '-'; ?></td>
                                <td>
                                    <?php if ($billing['followup_status']): ?>
                                        <span class="badge bg-<?php echo $billing['followup_status'] === 'resolved' ? 'success' : ($billing['followup_status'] === 'responded' ? 'info' : 'warning'); ?>">
                                            <?php echo ucfirst($billing['followup_status']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Status</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#followupModal<?php echo $billing['id']; ?>">
                                        <i class="fas fa-plus"></i> Follow-up
                                    </button>

                                    <?php if ($billing['followup_id']): ?>
                                    <button type="button" class="btn btn-info btn-sm" onclick="viewFollowupHistory(<?php echo $billing['id']; ?>)">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <?php endif; ?>

                                    <!-- Follow-up Modal -->
                                    <div class="modal fade" id="followupModal<?php echo $billing['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Create Follow-up</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="create_followup">
                                                        <input type="hidden" name="billing_id" value="<?php echo $billing['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Follow-up Date</label>
                                                            <input type="date" name="followup_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Follow-up Type</label>
                                                            <select name="followup_type" class="form-select" required>
                                                                <option value="email">Email</option>
                                                                <option value="phone">Phone</option>
                                                                <option value="letter">Letter</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Contact Person</label>
                                                            <input type="text" name="contact_person" class="form-control" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Response</label>
                                                            <textarea name="response" class="form-control" rows="3" required></textarea>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Next Follow-up Date</label>
                                                            <input type="date" name="next_followup_date" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Create Follow-up</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow-up History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Follow-up History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Contact Person</th>
                                    <th>Response</th>
                                    <th>Next Follow-up</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewFollowupHistory(billingId) {
            fetch(`get_followup_history.php?billing_id=${billingId}`)
                .then(response => response.json())
                .then(history => {
                    const tbody = document.getElementById('historyTableBody');
                    tbody.innerHTML = '';
                    history.forEach(record => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${record.followup_date}</td>
                                <td>${record.followup_type}</td>
                                <td>${record.contact_person}</td>
                                <td>${record.response}</td>
                                <td>${record.next_followup_date || '-'}</td>
                                <td>
                                    <span class="badge bg-${record.status === 'resolved' ? 'success' : (record.status === 'responded' ? 'info' : 'warning')}">
                                        ${record.status}
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="followup_id" value="${record.id}">
                                        <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="pending" ${record.status === 'pending' ? 'selected' : ''}>Pending</option>
                                            <option value="responded" ${record.status === 'responded' ? 'selected' : ''}>Responded</option>
                                            <option value="resolved" ${record.status === 'resolved' ? 'selected' : ''}>Resolved</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        `;
                    });
                    new bootstrap.Modal(document.getElementById('historyModal')).show();
                });
        }
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>