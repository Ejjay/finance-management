<?php
require_once '../../config/database.php';
session_start();

class InvoiceProcessing {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createInvoice($data) {
        $sql = "INSERT INTO invoices (vendor_id, invoice_number, invoice_date, due_date, total_amount, description) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $data['vendor_id'], PDO::PARAM_INT);
        $stmt->bindParam(2, $data['invoice_number']);
        $stmt->bindParam(3, $data['invoice_date']);
        $stmt->bindParam(4, $data['due_date']);
        $stmt->bindParam(5, $data['total_amount']);
        $stmt->bindParam(6, $data['description']);
        return $stmt->execute();
    }

    public function recordPayment($data) {
        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Insert payment record
            $sql = "INSERT INTO payment_history (invoice_id, payment_date, amount, payment_method, reference_number, notes) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(1, $data['invoice_id'], PDO::PARAM_INT);
            $stmt->bindParam(2, $data['payment_date']);
            $stmt->bindParam(3, $data['payment_amount']);
            $stmt->bindParam(4, $data['payment_method']);
            $stmt->bindParam(5, $data['reference_number']);
            $stmt->bindParam(6, $data['notes']);
            $stmt->execute();

            // Update invoice paid amount and status
            $sql = "UPDATE invoices 
                    SET paid_amount = paid_amount + ?,
                        status = CASE 
                            WHEN paid_amount + ? >= total_amount THEN 'paid'
                            WHEN paid_amount + ? > 0 THEN 'partially_paid'
                            ELSE status
                        END
                    WHERE invoice_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(1, $data['payment_amount']);
            $stmt->bindParam(2, $data['payment_amount']);
            $stmt->bindParam(3, $data['payment_amount']);
            $stmt->bindParam(4, $data['invoice_id'], PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function getInvoices() {
        $sql = "SELECT i.*, v.name as vendor_name,
                COALESCE(SUM(p.amount), 0.00) as paid_amount
                FROM invoices i 
                JOIN vendors v ON i.vendor_id = v.id 
                LEFT JOIN payment_history p ON i.id = p.invoice_id
                GROUP BY i.id, i.vendor_id, i.invoice_number, i.invoice_date, i.due_date, i.total_amount, i.description, i.status, v.name
                ORDER BY i.invoice_date DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVendors() {
        $sql = "SELECT id as vendor_id, name as vendor_name FROM vendors ORDER BY name";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$invoiceProcessing = new InvoiceProcessing();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_invoice':
                $invoiceProcessing->createInvoice($_POST);
                break;
            case 'record_payment':
                $invoiceProcessing->recordPayment($_POST);
                break;
        }
    }
}

$invoices = $invoiceProcessing->getInvoices();
$vendors = $invoiceProcessing->getVendors();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Processing</title>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Invoice Processing</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                <i class="fas fa-plus"></i> Create New Invoice
            </button>
        </div>

        <!-- Invoices Table -->
        <div class="card">
            <div class="card-body">
                <table id="invoices-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Vendor</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?= htmlspecialchars($invoice['invoice_number']) ?></td>
                            <td><?= htmlspecialchars($invoice['vendor_name']) ?></td>
                            <td><?= htmlspecialchars($invoice['invoice_date']) ?></td>
                            <td><?= htmlspecialchars($invoice['due_date']) ?></td>
                            <td>₱<?= number_format($invoice['total_amount'], 2) ?></td>
                            <td>₱<?= number_format($invoice['paid_amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $invoice['status'] === 'paid' ? 'success' : 
                                    ($invoice['status'] === 'partially_paid' ? 'warning' : 
                                    ($invoice['status'] === 'overdue' ? 'danger' : 'info')) 
                                ?>">
                                    <?= ucfirst(str_replace('_', ' ', $invoice['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($invoice['status'] !== 'paid'): ?>
                                <button class="btn btn-sm btn-success record-payment" 
                                        data-invoice-id="<?= $invoice['id'] ?>"
                                        data-remaining="<?= $invoice['total_amount'] - $invoice['paid_amount'] ?>">
                                    <i class="fas fa-dollar-sign"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Invoice Modal -->
        <div class="modal fade" id="addInvoiceModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Invoice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="create_invoice">
                            <div class="mb-3">
                                <label class="form-label">Vendor</label>
                                <select class="form-control" name="vendor_id" required>
                                    <option value="">Select Vendor</option>
                                    <?php foreach ($vendors as $vendor): ?>
                                    <option value="<?= $vendor['vendor_id'] ?>">
                                        <?= htmlspecialchars($vendor['vendor_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Invoice Number</label>
                                <input type="text" class="form-control" name="invoice_number" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Invoice Date</label>
                                <input type="date" class="form-control" name="invoice_date" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Amount</label>
                                <input type="number" step="0.01" class="form-control" name="total_amount" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Invoice</button>
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
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="record_payment">
                            <input type="hidden" name="invoice_id" id="payment_invoice_id">
                            <div class="mb-3">
                                <label class="form-label">Payment Date</label>
                                <input type="date" class="form-control" name="payment_date" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Amount</label>
                                <input type="number" step="0.01" class="form-control" name="payment_amount" 
                                       id="payment_amount" required>
                                <small class="text-muted">Remaining: $<span id="remaining_amount"></span></small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select class="form-control" name="payment_method" required>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="cash">Cash</option>
                                    <option value="credit_card">Credit Card</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reference Number</label>
                                <input type="text" class="form-control" name="reference_number">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Record Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#invoices-table').DataTable({
                order: [[2, 'desc']]
            });

            $('.record-payment').click(function() {
                const invoiceId = $(this).data('invoice-id');
                const remaining = $(this).data('remaining');
                
                $('#payment_invoice_id').val(invoiceId);
                $('#payment_amount').attr('max', remaining);
                $('#remaining_amount').text(remaining.toFixed(2));
                
                $('#recordPaymentModal').modal('show');
            });

            // Set default dates
            const today = new Date().toISOString().split('T')[0];
            $('input[name="invoice_date"]').val(today);
            $('input[name="payment_date"]').val(today);
        });
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>