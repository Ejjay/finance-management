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
            case 'record_payment':
                try {
                    $conn->beginTransaction();
                    
                    // Get billing details
                    $sql = "SELECT total_amount, balance_amount FROM student_billing WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$_POST['billing_id']]);
                    $billing = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$billing) {
                        throw new Exception("Billing record not found.");
                    }
                    
                    $payment_amount = floatval($_POST['payment_amount']);
                    $new_balance = $billing['balance_amount'] - $payment_amount;
                    
                    if ($payment_amount <= 0 || $payment_amount > $billing['balance_amount']) {
                        throw new Exception("Invalid payment amount.");
                    }
                    
                    // Insert payment record
                    $sql = "INSERT INTO payment_records (billing_id, payment_date, payment_amount, payment_method, reference_number) 
                            VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        $_POST['billing_id'],
                        $_POST['payment_date'],
                        $payment_amount,
                        $_POST['payment_method'],
                        $_POST['reference_number']
                    ]);
                    
                    // Update billing balance and status
                    $status = $new_balance <= 0 ? 'paid' : ($new_balance < $billing['total_amount'] ? 'partial' : 'pending');
                    $sql = "UPDATE student_billing SET balance_amount = ?, status = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$new_balance, $status, $_POST['billing_id']]);
                    
                    $conn->commit();
                    $msg = "Payment recorded successfully.";
                } catch (Exception $e) {
                    $conn->rollBack();
                    $msg = "Error recording payment: " . $e->getMessage();
                }
                break;
                
            case 'create_billing':
                try {
                    $conn->beginTransaction();

                    // Insert main billing record
                    $sql = "INSERT INTO student_billing (student_id, student_name, billing_date, due_date, total_amount, balance_amount) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        $_POST['student_id'],
                        $_POST['student_name'],
                        $_POST['billing_date'],
                        $_POST['due_date'],
                        $_POST['total_amount'],
                        $_POST['total_amount']
                    ]);

                    $billing_id = $conn->lastInsertId();

                    // Insert billing items
                    $items = $_POST['items'];
                    $amounts = $_POST['amounts'];
                    $sql = "INSERT INTO billing_items (billing_id, item_description, amount) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);

                    for ($i = 0; $i < count($items); $i++) {
                        if (!empty($items[$i]) && !empty($amounts[$i])) {
                            $stmt->execute([$billing_id, $items[$i], $amounts[$i]]);
                        }
                    }

                    $conn->commit();
                    $msg = "Billing created successfully.";
                } catch (Exception $e) {
                    $conn->rollBack();
                    $msg = "Error creating billing: " . $e->getMessage();
                }
                break;

            case 'delete_billing':
                try {
                    $billing_id = $_POST['billing_id'];
                    
                    // Check if there are any payments
                    $sql = "SELECT COUNT(*) FROM payment_records WHERE billing_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$billing_id]);
                    $payment_count = $stmt->fetchColumn();

                    if ($payment_count > 0) {
                        throw new Exception("Cannot delete billing with existing payments.");
                    }

                    $conn->beginTransaction();

                    // Delete billing items
                    $sql = "DELETE FROM billing_items WHERE billing_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$billing_id]);

                    // Delete main billing
                    $sql = "DELETE FROM student_billing WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$billing_id]);

                    $conn->commit();
                    $msg = "Billing deleted successfully.";
                } catch (Exception $e) {
                    $conn->rollBack();
                    $msg = "Error deleting billing: " . $e->getMessage();
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
    <title>Student Billing & Invoicing - Finance System</title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Student Billing & Invoicing</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBillingModal">
                <i class="fas fa-plus"></i> Create New Billing
            </button>
        </div>

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
                        <a href="billing.php" class="btn btn-secondary">
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
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm" onclick="viewBillingItems(<?php echo $billing['id']; ?>)" title="View Items">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($billing['status'] !== 'paid'): ?>
                                        <button type="button" class="btn btn-success btn-sm" onclick="recordPayment(<?php echo $billing['id']; ?>, '<?php echo htmlspecialchars($billing['student_name']); ?>', <?php echo $billing['balance_amount']; ?>)" title="Record Payment">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($billing['status'] === 'pending'): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this billing?');">
                                            <input type="hidden" name="action" value="delete_billing">
                                            <input type="hidden" name="billing_id" value="<?php echo $billing['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete Billing">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

    <!-- Create Billing Modal -->
    <div class="modal fade" id="createBillingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Billing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="billingForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_billing">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Student ID</label>
                                <input type="text" name="student_id" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Student Name</label>
                                <input type="text" name="student_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Billing Date</label>
                                <input type="date" name="billing_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Billing Items</label>
                            <div id="billingItems" class="billing-items">
                                <div class="row mb-2">
                                    <div class="col-md-8">
                                        <input type="text" name="items[]" class="form-control" placeholder="Item Description" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" name="amounts[]" class="form-control amount-input" placeholder="Amount" step="0.01" required>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addBillingItem()">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" name="total_amount" id="totalAmount" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Billing</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <div class="modal fade" id="recordPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="paymentForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="record_payment">
                        <input type="hidden" name="billing_id" id="paymentBillingId">
                        
                        <div class="mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" id="paymentStudentName" class="form-control" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Outstanding Balance</label>
                            <input type="number" id="paymentBalance" class="form-control" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Amount</label>
                            <input type="number" name="payment_amount" class="form-control" required step="0.01" min="0">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                                <option value="online_payment">Online Payment</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Optional">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Billing Items Modal -->
    <div class="modal fade" id="viewItemsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Billing Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addBillingItem() {
            const itemsDiv = document.getElementById('billingItems');
            const newItem = document.createElement('div');
            newItem.className = 'row mb-2';
            newItem.innerHTML = `
                <div class="col-md-8">
                    <input type="text" name="items[]" class="form-control" placeholder="Item Description" required>
                </div>
                <div class="col-md-4">
                    <input type="number" name="amounts[]" class="form-control amount-input" placeholder="Amount" step="0.01" required>
                </div>
            `;
            itemsDiv.appendChild(newItem);
        }

        function recordPayment(billingId, studentName, balance) {
            document.getElementById('paymentBillingId').value = billingId;
            document.getElementById('paymentStudentName').value = studentName;
            document.getElementById('paymentBalance').value = balance;
            
            const paymentModal = new bootstrap.Modal(document.getElementById('recordPaymentModal'));
            paymentModal.show();
        }

        // Calculate total amount
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('amount-input')) {
                let total = 0;
                document.querySelectorAll('.amount-input').forEach(input => {
                    total += parseFloat(input.value || 0);
                });
                document.getElementById('totalAmount').value = total.toFixed(2);
            }
        });

        function viewBillingItems(billingId) {
            fetch(`get_billing_items.php?billing_id=${billingId}`)
                .then(response => response.json())
                .then(items => {
                    const tbody = document.getElementById('itemsTableBody');
                    tbody.innerHTML = '';
                    items.forEach(item => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${item.item_description}</td>
                                <td>₱${parseFloat(item.amount).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    new bootstrap.Modal(document.getElementById('viewItemsModal')).show();
                });
        }
    </script>
        <script src="../../js/navigation.js"></script>
</body>
</html>