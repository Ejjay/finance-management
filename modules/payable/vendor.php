<?php
require_once '../../config/database.php';
session_start();

class VendorManagement {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function addVendor($data) {
        $sql = "INSERT INTO vendors (name, contact_person, email, phone, address, tax_id, payment_terms) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $data['vendor_name']);
        $stmt->bindParam(2, $data['contact_person']);
        $stmt->bindParam(3, $data['email']);
        $stmt->bindParam(4, $data['phone']);
        $stmt->bindParam(5, $data['address']);
        $stmt->bindParam(6, $data['tax_id']);
        $stmt->bindParam(7, $data['payment_terms'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateVendor($vendor_id, $data) {
        $sql = "UPDATE vendors 
                SET name = ?, contact_person = ?, email = ?, phone = ?, 
                    address = ?, tax_id = ?, payment_terms = ?, status = ? 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssssssisi', 
            $data['vendor_name'],
            $data['contact_person'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['tax_id'],
            $data['payment_terms'],
            $data['status'],
            $vendor_id
        );
        return $stmt->execute();
    }

    public function getVendors() {
        $sql = "SELECT id as vendor_id, name as vendor_name, contact_person, email, phone, status FROM vendors ORDER BY name ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVendorById($vendor_id) {
        $sql = "SELECT * FROM vendors WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $vendor_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$vendorManagement = new VendorManagement();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $vendorManagement->addVendor($_POST);
                break;
            case 'update':
                $vendorManagement->updateVendor($_POST['vendor_id'], $_POST);
                break;
        }
    }
}

$vendors = $vendorManagement->getVendors();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Management</title>
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
            <h2>Vendor Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                <i class="fas fa-plus"></i> Add New Vendor
            </button>
        </div>

        <!-- Vendors Table -->
        <div class="card">
            <div class="card-body">
                <table id="vendors-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Vendor Name</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Payment Terms</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendors as $vendor): ?>
                        <tr>
                            <td><?= htmlspecialchars($vendor['vendor_name']) ?></td>
                            <td><?= htmlspecialchars($vendor['contact_person']) ?></td>
                            <td><?= htmlspecialchars($vendor['email']) ?></td>
                            <td><?= htmlspecialchars($vendor['phone']) ?></td>
                            <td>N/A</td>
                            <td>
                                <span class="badge bg-<?= $vendor['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($vendor['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-vendor" data-vendor-id="<?= $vendor['vendor_id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Vendor Modal -->
        <div class="modal fade" id="addVendorModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Vendor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label class="form-label">Vendor Name</label>
                                <input type="text" class="form-control" name="vendor_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" class="form-control" name="contact_person">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tax ID</label>
                                <input type="text" class="form-control" name="tax_id">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Terms (days)</label>
                                <input type="number" class="form-control" name="payment_terms" value="30">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Vendor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Vendor Modal -->
        <div class="modal fade" id="editVendorModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Vendor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" id="editVendorForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="vendor_id" id="edit_vendor_id">
                            <div class="mb-3">
                                <label class="form-label">Vendor Name</label>
                                <input type="text" class="form-control" name="vendor_name" id="edit_vendor_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" class="form-control" name="contact_person" id="edit_contact_person">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="edit_email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" id="edit_phone">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" id="edit_address"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tax ID</label>
                                <input type="text" class="form-control" name="tax_id" id="edit_tax_id">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Terms (days)</label>
                                <input type="number" class="form-control" name="payment_terms" id="edit_payment_terms">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status" id="edit_status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Vendor</button>
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
            $('#vendors-table').DataTable();

            $('.edit-vendor').click(function() {
                const vendorId = $(this).data('vendor-id');
                
                // Fetch vendor data and populate modal
                $.get('get_vendor.php', { vendor_id: vendorId }, function(vendor) {
                    $('#edit_vendor_id').val(vendor.vendor_id);
                    $('#edit_vendor_name').val(vendor.vendor_name);
                    $('#edit_contact_person').val(vendor.contact_person);
                    $('#edit_email').val(vendor.email);
                    $('#edit_phone').val(vendor.phone);
                    $('#edit_address').val(vendor.address);
                    $('#edit_tax_id').val(vendor.tax_id);
                    $('#edit_payment_terms').val(vendor.payment_terms);
                    $('#edit_status').val(vendor.status);
                    
                    $('#editVendorModal').modal('show');
                });
            });
        });
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>