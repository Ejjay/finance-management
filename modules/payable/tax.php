<?php
require_once '../../config/database.php';
session_start();

class TaxCompliance {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getTaxRecords() {
        $sql = "SELECT t.*, i.invoice_number, v.name as vendor_name 
                FROM tax_records t 
                JOIN invoices i ON t.invoice_id = i.id 
                JOIN vendors v ON i.vendor_id = v.id 
                ORDER BY t.tax_period_end DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTaxRecord($data) {
        $sql = "INSERT INTO tax_records (invoice_id, tax_type, tax_rate, tax_amount, 
                                       tax_period_start, tax_period_end, filing_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $data['invoice_id'], PDO::PARAM_INT);
        $stmt->bindParam(2, $data['tax_type']);
        $stmt->bindParam(3, $data['tax_rate']);
        $stmt->bindParam(4, $data['tax_amount']);
        $stmt->bindParam(5, $data['tax_period_start']);
        $stmt->bindParam(6, $data['tax_period_end']);
        $stmt->bindParam(7, $data['filing_status']);
        return $stmt->execute();
    }

    public function updateTaxStatus($tax_record_id, $status) {
        $sql = "UPDATE tax_records SET filing_status = ? WHERE tax_record_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $status);
        $stmt->bindParam(2, $tax_record_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getPendingInvoices() {
        $sql = "SELECT i.id as invoice_id, i.invoice_number, v.name as vendor_name, i.total_amount 
                FROM invoices i 
                JOIN vendors v ON i.vendor_id = v.id 
                LEFT JOIN tax_records t ON i.id = t.invoice_id 
                WHERE t.tax_record_id IS NULL 
                ORDER BY i.invoice_date DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$taxCompliance = new TaxCompliance();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_tax_record':
                $taxCompliance->addTaxRecord($_POST);
                break;
            case 'update_status':
                $taxCompliance->updateTaxStatus($_POST['tax_record_id'], $_POST['filing_status']);
                break;
        }
    }
}

$taxRecords = $taxCompliance->getTaxRecords();
$pendingInvoices = $taxCompliance->getPendingInvoices();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax & Compliance Checks</title>
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
            <h2>Tax & Compliance Checks</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaxRecordModal">
                <i class="fas fa-plus"></i> Add Tax Record
            </button>
        </div>

        <!-- Tax Records Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Tax Records</h5>
            </div>
            <div class="card-body">
                <table id="tax-records-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Vendor</th>
                            <th>Tax Type</th>
                            <th>Tax Rate</th>
                            <th>Tax Amount</th>
                            <th>Period</th>
                            <th>Filing Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($taxRecords as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['invoice_number']) ?></td>
                            <td><?= htmlspecialchars($record['vendor_name']) ?></td>
                            <td><?= htmlspecialchars($record['tax_type']) ?></td>
                            <td><?= number_format($record['tax_rate'], 2) ?>%</td>
                            <td>$<?= number_format($record['tax_amount'], 2) ?></td>
                            <td>
                                <?= date('M Y', strtotime($record['tax_period_start'])) ?> - 
                                <?= date('M Y', strtotime($record['tax_period_end'])) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= 
                                    $record['filing_status'] === 'approved' ? 'success' : 
                                    ($record['filing_status'] === 'filed' ? 'warning' : 'info') 
                                ?>">
                                    <?= ucfirst($record['filing_status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($record['filing_status'] !== 'approved'): ?>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" 
                                            data-bs-toggle="dropdown">
                                        Update Status
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="tax_record_id" value="<?= $record['tax_record_id'] ?>">
                                                <input type="hidden" name="filing_status" value="filed">
                                                <button type="submit" class="dropdown-item">Mark as Filed</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="tax_record_id" value="<?= $record['tax_record_id'] ?>">
                                                <input type="hidden" name="filing_status" value="approved">
                                                <button type="submit" class="dropdown-item">Mark as Approved</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Tax Record Modal -->
        <div class="modal fade" id="addTaxRecordModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Tax Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="add_tax_record">
                            <div class="mb-3">
                                <label class="form-label">Invoice</label>
                                <select class="form-control" name="invoice_id" required>
                                    <option value="">Select Invoice</option>
                                    <?php foreach ($pendingInvoices as $invoice): ?>
                                    <option value="<?= $invoice['invoice_id'] ?>">
                                        <?= htmlspecialchars($invoice['invoice_number']) ?> - 
                                        <?= htmlspecialchars($invoice['vendor_name']) ?> 
                                        ($<?= number_format($invoice['total_amount'], 2) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tax Type</label>
                                <select class="form-control" name="tax_type" required>
                                    <option value="sales_tax">Sales Tax</option>
                                    <option value="vat">VAT</option>
                                    <option value="income_tax">Income Tax</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tax Rate (%)</label>
                                <input type="number" step="0.01" class="form-control" name="tax_rate" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tax Amount</label>
                                <input type="number" step="0.01" class="form-control" name="tax_amount" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tax Period Start</label>
                                <input type="date" class="form-control" name="tax_period_start" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tax Period End</label>
                                <input type="date" class="form-control" name="tax_period_end" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Filing Status</label>
                                <select class="form-control" name="filing_status" required>
                                    <option value="pending">Pending</option>
                                    <option value="filed">Filed</option>
                                    <option value="approved">Approved</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Tax Record</button>
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
            $('#tax-records-table').DataTable({
                order: [[5, 'desc']]
            });

            // Set default dates
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            $('input[name="tax_period_start"]').val(firstDay.toISOString().split('T')[0]);
            $('input[name="tax_period_end"]').val(lastDay.toISOString().split('T')[0]);
        });
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>