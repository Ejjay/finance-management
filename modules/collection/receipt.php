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

// Handle receipt generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_receipt'])) {
    $payment_id = $_POST['payment_id'];
    
    // Generate unique receipt number
    $receipt_number = 'RCPT-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $conn->prepare("INSERT INTO payment_receipts (payment_id, receipt_number) VALUES (?, ?)");
    if ($stmt->execute([$payment_id, $receipt_number])) {
        $msg = "Receipt generated successfully!";
    } else {
        $msg = "Error generating receipt.";
    }
}

// Fetch payments with receipt information
$payments = $conn->query("
    SELECT 
        sp.*,
        fs.fee_name,
        pr.receipt_number,
        pr.generated_date as receipt_date
    FROM student_payments sp
    LEFT JOIN fee_schedules fs ON sp.fee_schedule_id = fs.id
    LEFT JOIN payment_receipts pr ON sp.id = pr.payment_id
    WHERE sp.status = 'completed'
    ORDER BY sp.payment_date DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt Generation - Finance System</title>
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
        <h2>Receipt Generation</h2>
        
        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Payments and Receipts Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Receipts</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Fee Name</th>
                                <th>Amount Paid</th>
                                <th>Payment Date</th>
                                <th>Payment Method</th>
                                <th>Receipt Number</th>
                                <th>Receipt Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($payment['fee_name']); ?></td>
                                <td>₱<?php echo number_format($payment['amount_paid'], 2); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($payment['payment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                <td>
                                    <?php if ($payment['receipt_number']): ?>
                                        <?php echo htmlspecialchars($payment['receipt_number']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not generated</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($payment['receipt_date']): ?>
                                        <?php echo date('Y-m-d H:i:s', strtotime($payment['receipt_date'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$payment['receipt_number']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                            <button type="submit" name="generate_receipt" class="btn btn-sm btn-primary" title="Generate Receipt">
                                                <i class="fas fa-file-invoice"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-info view-receipt" title="View Receipt" 
                                                data-payment="<?php echo htmlspecialchars(json_encode($payment)); ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" title="Download Receipt">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Receipt Preview Modal -->
        <div class="modal fade" id="receiptModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Receipt Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="receipt-preview">
                            <div class="receipt-header">
                                <h4>Payment Receipt</h4>
                                <p id="receiptNumber"></p>
                            </div>
                            <div class="receipt-details">
                                <div class="row mb-2">
                                    <div class="col-4">Student ID:</div>
                                    <div class="col-8" id="studentId"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4">Fee Name:</div>
                                    <div class="col-8" id="feeName"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4">Amount Paid:</div>
                                    <div class="col-8" id="amountPaid"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4">Payment Date:</div>
                                    <div class="col-8" id="paymentDate"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4">Payment Method:</div>
                                    <div class="col-8" id="paymentMethod"></div>
                                </div>
                            </div>
                            <div class="receipt-footer">
                                <p>Thank you for your payment!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
        
        document.querySelectorAll('.view-receipt').forEach(button => {
            button.addEventListener('click', function() {
                const payment = JSON.parse(this.dataset.payment);
                
                document.getElementById('receiptNumber').textContent = `Receipt #: ${payment.receipt_number}`;
                document.getElementById('studentId').textContent = payment.student_id;
                document.getElementById('feeName').textContent = payment.fee_name;
                document.getElementById('amountPaid').textContent = `₱${parseFloat(payment.amount_paid).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                document.getElementById('paymentDate').textContent = new Date(payment.payment_date).toLocaleDateString();
                document.getElementById('paymentMethod').textContent = payment.payment_method;
                
                receiptModal.show();
            });
        });
    });
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>