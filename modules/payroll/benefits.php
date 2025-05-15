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
            case 'add_benefit':
                $employee_id = $_POST['employee_id'];
                $benefit_type = $_POST['benefit_type'];
                $amount = $_POST['amount'];
                $effective_date = $_POST['effective_date'];
                
                $sql = "INSERT INTO employee_benefits (employee_id, benefit_type, amount, effective_date) 
                        VALUES (?, ?, ?, ?)";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$employee_id, $benefit_type, $amount, $effective_date]);
                    $msg = "Benefit added successfully.";
                }
                break;

            case 'update_benefit':
                $benefit_id = $_POST['benefit_id'];
                $amount = $_POST['amount'];
                $status = $_POST['status'];
                
                $sql = "UPDATE employee_benefits SET amount = ?, status = ? WHERE id = ?";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$amount, $status, $benefit_id]);
                    $msg = "Benefit updated successfully.";
                }
                break;

            case 'delete_benefit':
                $benefit_id = $_POST['benefit_id'];
                
                $sql = "DELETE FROM employee_benefits WHERE id = ?";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$benefit_id]);
                    $msg = "Benefit deleted successfully.";
                }
                break;
        }
    }
}

// Fetch employees
$employees = [];
$employee_sql = "SELECT employee_id, employee_name FROM employees ORDER BY employee_name";
$employee_result = $hr_conn->query($employee_sql);
while ($row = $employee_result->fetch(PDO::FETCH_ASSOC)) {
    $employees[$row['employee_id']] = $row['employee_name'];
}

// Fetch active benefits
$benefits_sql = "SELECT eb.*, e.employee_name 
                FROM employee_benefits eb 
                JOIN employees e ON eb.employee_id = e.employee_id 
                WHERE eb.status = 'active' 
                ORDER BY eb.effective_date DESC";
$benefits_result = $conn->query($benefits_sql);
$active_benefits = $benefits_result->fetchAll(PDO::FETCH_ASSOC);

// Fetch benefit types
$benefit_types = ['Health Insurance', 'Life Insurance', 'Transportation', 'Housing', 'Meal Allowance', 'Performance Bonus'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Benefits Management - Finance System</title>
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
        <h2>Staff Benefits Management</h2>
        
        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Add New Benefit Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4>Add New Benefit</h4>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <input type="hidden" name="action" value="add_benefit">
                    
                    <div class="col-md-4">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $id => $name): ?>
                                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Benefit Type</label>
                        <select name="benefit_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <?php foreach ($benefit_types as $type): ?>
                                <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Effective Date</label>
                        <input type="date" name="effective_date" class="form-control" required>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Benefit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Active Benefits Table -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4>Active Benefits</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Benefit Type</th>
                                <th>Amount</th>
                                <th>Effective Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($active_benefits as $benefit): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($benefit['employee_name']); ?></td>
                                <td><?php echo htmlspecialchars($benefit['benefit_type']); ?></td>
                                <td>₱<?php echo number_format($benefit['amount'], 2); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($benefit['effective_date'])); ?></td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="editBenefit(<?php echo $benefit['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this benefit?');">
                                        <input type="hidden" name="action" value="delete_benefit">
                                        <input type="hidden" name="benefit_id" value="<?php echo $benefit['id']; ?>">
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
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editBenefit(benefitId) {
            // Implement edit functionality
            alert('Edit benefit ID: ' + benefitId);
        }
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>