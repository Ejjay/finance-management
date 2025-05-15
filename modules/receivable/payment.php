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

// Handle payment posting
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'post_payment') {
        $billing_id = $_POST['billing_id'];
        $payment_amount = $_POST['payment_amount'];
        $payment_method = $_POST['payment_method'];
        $reference_number = $_POST['reference_number'];
        $notes = $_POST['notes'];
        $payment_date = $_POST['payment_date'];

        try {
            $conn->beginTransaction();

            // Insert payment record
            $sql = "INSERT INTO payment_records (billing_id, payment_date, payment_amount, payment_method, reference_number, notes) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$billing_id, $payment_date, $payment_amount, $payment_method, $reference_number, $notes]);

            // Update billing balance and status
            $sql = "UPDATE student_billing 
                    SET balance_amount = balance_amount - ?, 
                        status = CASE 
                            WHEN balance_amount - ? <= 0 THEN 'paid'
                            WHEN balance_amount - ? < total_amount THEN 'partial'
                            ELSE status
                        END
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$payment_amount, $payment_amount, $payment_amount, $billing_id]);

            $conn->commit();
            $msg = "Payment posted successfully.";
        } catch (Exception $e) {
            $conn->rollBack();
            $msg = "Error posting payment: " . $e->getMessage();
        }
    }
}

// Handle search and filtering
$search = $_GET['search'] ?? '';
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
$sql = "SELECT * FROM student_billing WHERE $where_clause ORDER BY billing_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$billings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Posting - Finance System</title>
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
        <h2>Payment Posting</h2>

        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Search and Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
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
                            <option value="partial" <?php echo $status === 'partial' ? 'selected' : ''; ?>>Partial</option>
                            <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="payment.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Billings Table -->
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
                                <th>Total Amount</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($billings as $billing): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($billing['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($billing['student_name']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($billing['billing_date'])); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($billing['due_date'])); ?></td>
                                <td>₱<?php echo number_format($billing['total_amount'], 2); ?></td>
                                <td>₱<?php echo number_format($billing['balance_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $billing['status'] === 'paid' ? 'success' : ($billing['status'] === 'partial' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($billing['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($billing['status'] !== 'paid'): ?>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo $billing['id']; ?>">
                                        <i class="fas fa-money-bill"></i> Post Payment
                                    </button>

                                    <!-- Payment Modal -->
                                    <div class="modal fade" id="paymentModal<?php echo $billing['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Post Payment</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="post_payment">
                                                        <input type="hidden" name="billing_id" value="<?php echo $billing['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Payment Date</label>
                                                            <input type="date" name="payment_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Payment Amount</label>
                                                            <input type="number" name="payment_amount" class="form-control" required step="0.01" max="<?php echo $billing['balance_amount']; ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Payment Method</label>
                                                            <select name="payment_method" class="form-select" required>
                                                                <option value="cash">Cash</option>
                                                                <option value="check">Check</option>
                                                                <option value="online">Online Transfer</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Reference Number</label>
                                                            <input type="text" name="reference_number" class="form-control">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Notes</label>
                                                            <textarea name="notes" class="form-control" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Post Payment</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
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