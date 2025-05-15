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

// Handle form submission for adding/updating fee schedules
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_fee'])) {
        $fee_name = $_POST['fee_name'];
        $amount = $_POST['amount'];
        $due_date = $_POST['due_date'];
        $description = $_POST['description'];

        $stmt = $conn->prepare("INSERT INTO fee_schedules (fee_name, amount, due_date, description) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$fee_name, $amount, $due_date, $description])) {
            $msg = "Fee schedule added successfully!";
        } else {
            $msg = "Error adding fee schedule.";
        }
    } elseif (isset($_POST['edit_fee'])) {
        $fee_id = $_POST['fee_id'];
        $fee_name = $_POST['fee_name'];
        $amount = $_POST['amount'];
        $due_date = $_POST['due_date'];
        $description = $_POST['description'];

        $stmt = $conn->prepare("UPDATE fee_schedules SET fee_name = ?, amount = ?, due_date = ?, description = ? WHERE id = ?");
        if ($stmt->execute([$fee_name, $amount, $due_date, $description, $fee_id])) {
            echo json_encode(['success' => true, 'message' => 'Fee schedule updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating fee schedule']);
        }
        exit;
    } elseif (isset($_POST['delete_fee'])) {
        $fee_id = $_POST['fee_id'];
        
        $stmt = $conn->prepare("DELETE FROM fee_schedules WHERE id = ?");
        if ($stmt->execute([$fee_id])) {
            echo json_encode(['success' => true, 'message' => 'Fee schedule deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting fee schedule']);
        }
        exit;
    } elseif (isset($_POST['get_fee'])) {
        $fee_id = $_POST['fee_id'];
        $stmt = $conn->prepare("SELECT * FROM fee_schedules WHERE id = ?");
        $stmt->execute([$fee_id]);
        $fee = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($fee);
        exit;
    }
}

// Fetch all fee schedules
$fee_schedules = $conn->query("SELECT * FROM fee_schedules ORDER BY due_date")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Collection - Finance System</title>
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
        <h2>Fee Collection Management</h2>
        
        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Add Fee Schedule Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Add New Fee Schedule</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Fee Name</label>
                        <input type="text" name="fee_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                    <div class="col-12">
                        <button type="submit" name="add_fee" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Fee Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Fee Schedules Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Fee Schedules</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fee Name</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Description</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fee_schedules as $fee): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fee['fee_name']); ?></td>
                                <td>₱<?php echo number_format($fee['amount'], 2); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($fee['due_date'])); ?></td>
                                <td><?php echo htmlspecialchars($fee['description']); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($fee['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-fee" title="Edit" data-fee-id="<?php echo $fee['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-fee" title="Delete" data-fee-id="<?php echo $fee['id']; ?>">
                                        <i class="fas fa-trash"></i>
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

    <!-- Edit Fee Modal -->
    <div class="modal fade" id="editFeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Fee Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editFeeForm" class="row g-3">
                        <input type="hidden" name="fee_id" id="edit_fee_id">
                        <div class="col-md-6">
                            <label class="form-label">Fee Name</label>
                            <input type="text" name="fee_name" id="edit_fee_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" id="edit_amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" id="edit_due_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" id="edit_description" class="form-control">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveEditFee">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editModal = new bootstrap.Modal(document.getElementById('editFeeModal'));

        // Handle Edit Button Click
        document.querySelectorAll('.edit-fee').forEach(button => {
            button.addEventListener('click', function() {
                const feeId = this.getAttribute('data-fee-id');
                fetch('fees.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'get_fee=1&fee_id=' + feeId
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_fee_id').value = data.id;
                    document.getElementById('edit_fee_name').value = data.fee_name;
                    document.getElementById('edit_amount').value = data.amount;
                    document.getElementById('edit_due_date').value = data.due_date;
                    document.getElementById('edit_description').value = data.description;
                    editModal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching fee details');
                });
            });
        });

        // Handle Save Edit Button Click
        document.getElementById('saveEditFee').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('editFeeForm'));
            formData.append('edit_fee', '1');

            fetch('fees.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    editModal.hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving changes');
            });
        });

        // Handle Delete Button Click
        document.querySelectorAll('.delete-fee').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this fee schedule?')) {
                    const feeId = this.getAttribute('data-fee-id');
                    fetch('fees.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'delete_fee=1&fee_id=' + feeId
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
                        alert('An error occurred while deleting the fee schedule');
                    });
                }
            });
        });
    });
    </script>
</body>
</html>