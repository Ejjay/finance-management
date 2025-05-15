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

// Handle form submission for managing dues
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_due'])) {
        $student_id = $_POST['student_id'];
        $fee_schedule_id = $_POST['fee_schedule_id'];
        $amount_due = $_POST['amount_due'];
        $due_date = $_POST['due_date'];

        $stmt = $conn->prepare("INSERT INTO due_records (student_id, fee_schedule_id, amount_due, due_date, status) VALUES (?, ?, ?, ?, 'pending')");
        if ($stmt->execute([$student_id, $fee_schedule_id, $amount_due, $due_date])) {
            $msg = "Due record added successfully!";
        } else {
            $msg = "Error adding due record.";
        }
    } elseif (isset($_POST['send_reminder'])) {
        $due_id = $_POST['due_id'];
        $stmt = $conn->prepare("UPDATE due_records SET last_reminder_date = NOW() WHERE id = ?");
        if ($stmt->execute([$due_id])) {
            echo json_encode(['success' => true, 'message' => 'Reminder sent successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error sending reminder']);
        }
        exit;
    } elseif (isset($_POST['mark_paid'])) {
        $due_id = $_POST['due_id'];
        $stmt = $conn->prepare("UPDATE due_records SET status = 'paid' WHERE id = ?");
        if ($stmt->execute([$due_id])) {
            echo json_encode(['success' => true, 'message' => 'Due marked as paid']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating status']);
        }
        exit;
    }
}

// Fetch all due records with fee schedule details
$due_records = $conn->query("
    SELECT dr.*, fs.fee_name 
    FROM due_records dr 
    LEFT JOIN fee_schedules fs ON dr.fee_schedule_id = fs.id 
    ORDER BY dr.due_date
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch fee schedules for dropdown
$fee_schedules = $conn->query("SELECT * FROM fee_schedules")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Due Management - Finance System</title>
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
        <h2>Due Management</h2>
        
        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Add Due Record Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Add New Due Record</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Student ID</label>
                        <input type="text" name="student_id" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fee Schedule</label>
                        <select name="fee_schedule_id" class="form-select" required>
                            <option value="">Select Fee Schedule</option>
                            <?php foreach ($fee_schedules as $fee): ?>
                                <option value="<?php echo $fee['id']; ?>">
                                    <?php echo htmlspecialchars($fee['fee_name']); ?> - ₱<?php echo number_format($fee['amount'], 2); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount Due</label>
                        <input type="number" name="amount_due" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="add_due" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Due Record
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Due Records Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Due Records</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Fee Name</th>
                                <th>Amount Due</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Last Reminder</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($due_records as $due): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($due['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($due['fee_name']); ?></td>
                                <td>₱<?php echo number_format($due['amount_due'], 2); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($due['due_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $due['status'] === 'paid' ? 'success' : ($due['status'] === 'overdue' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($due['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo ($due['last_reminder_date'] ?? null) ? date('Y-m-d', strtotime($due['last_reminder_date'])) : 'N/A'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary send-reminder" title="Send Reminder" data-due-id="<?php echo $due['id']; ?>">
                                        <i class="fas fa-bell"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success mark-paid" title="Mark as Paid" data-due-id="<?php echo $due['id']; ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Send Reminder button clicks
        document.querySelectorAll('.send-reminder').forEach(button => {
            button.addEventListener('click', function() {
                const dueId = this.getAttribute('data-due-id');
                fetch('dues.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'send_reminder=1&due_id=' + dueId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while sending the reminder');
                });
            });
        });

        // Handle Mark as Paid button clicks
        document.querySelectorAll('.mark-paid').forEach(button => {
            button.addEventListener('click', function() {
                const dueId = this.getAttribute('data-due-id');
                fetch('dues.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'mark_paid=1&due_id=' + dueId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the payment status');
                });
            });
        });
    });
    </script>
    </body>
</html>