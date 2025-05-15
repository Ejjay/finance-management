<?php
session_start();
require_once '../../config/database.php';

// Initialize database connections
$db = new Database();
$conn = $db->getConnection('payroll'); // Use payroll_db for payroll table
$hr_conn = $db->getConnection('hr'); // Use hr_db for employee data

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Initialize message variable
$msg = '';

// Calculate 13th month pay based on total basic salary earned in a year
function calculate_13th_month($employee_id) {
    global $conn;
    $year = date('Y');
    $sql = "SELECT SUM(basic_salary) as total_basic_salary FROM payroll 
           WHERE employee_id = ? AND YEAR(payroll_date) = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bindParam(1, $employee_id);
        $stmt->bindParam(2, $year);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_basic_salary = $row['total_basic_salary'] ?: 0;
        
        // Calculate 13th month pay (total basic salary / 12)
        return $total_basic_salary / 12;
    }
    return 0; // Return 0 if query fails
}

// Calculate retirement contribution (assuming 5% of basic salary)
function calculate_retirement($basic_salary) {
    return $basic_salary * 0.05;
}

// Handle AJAX requests for 13th month calculation
if (isset($_GET['action']) && $_GET['action'] === 'calculate_13th_month') {
    header('Content-Type: application/json');
    $employee_id = $_GET['employee_id'];
    $thirteenth_month = calculate_13th_month($employee_id);
    echo json_encode(['thirteenth_month' => $thirteenth_month]);
    exit;
}

// Automatically generate payroll records from HR data
function generate_payroll_records() {
    global $conn, $hr_conn;
    $current_date = date('Y-m-d');
    
    // Get only today's employee records from HR that haven't been processed
    $hr_sql = "SELECT e.employee_id, e.employee_name, e.basic_salary, e.absents 
               FROM employees e 
               WHERE DATE(e.date) = CURRENT_DATE() 
               AND NOT EXISTS (
                   SELECT 1 FROM payroll p 
                   WHERE p.employee_id = e.employee_id 
                   AND DATE(p.payroll_date) = CURRENT_DATE()
               )";
    $hr_result = $hr_conn->query($hr_sql);

    if ($hr_result) {
        while ($employee = $hr_result->fetch(PDO::FETCH_ASSOC)) {
            // Begin transaction to prevent race conditions
            $conn->beginTransaction();
            try {
                // Calculate attendance (assuming 22 working days per month)
                $attendance = 22 - ($employee['absents'] ?? 0);
                
                // Calculate deductions based on absents
                $daily_rate = $employee['basic_salary'] / 22;
                $deductions = ($employee['absents'] ?? 0) * $daily_rate;
                
                $retirement_contribution = calculate_retirement($employee['basic_salary']);
                $thirteenth_month = calculate_13th_month($employee['employee_id']);
                $total = $employee['basic_salary'] - $deductions - $retirement_contribution;
                
                $sql = "INSERT INTO payroll (employee_id, employee_name, attendance, basic_salary, deductions, 
                        retirement_contribution, thirteenth_month, total_salary, payroll_date, payment_status) 
                        VALUES (:employee_id, :employee_name, :attendance, :basic_salary, :deductions, 
                        :retirement_contribution, :thirteenth_month, :total_salary, :payroll_date, 'unpaid')";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bindParam(':employee_id', $employee['employee_id']);
                    $stmt->bindParam(':employee_name', $employee['employee_name']);
                    $stmt->bindParam(':attendance', $attendance);
                    $stmt->bindParam(':basic_salary', $employee['basic_salary']);
                    $stmt->bindParam(':deductions', $deductions);
                    $stmt->bindParam(':retirement_contribution', $retirement_contribution);
                    $stmt->bindParam(':thirteenth_month', $thirteenth_month);
                    $stmt->bindParam(':total_salary', $total);
                    $stmt->bindParam(':payroll_date', $current_date);
                    
                    if ($stmt->execute()) {
                        $conn->commit();
                    } else {
                        $conn->rollBack();
                    }
                }
            } catch (Exception $e) {
                $conn->rollBack();
            }
        }
    }
}

// Generate payroll records automatically
generate_payroll_records();


// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {

            case 'update':
                $id = $_POST['id'];
                $employee_id = $_POST['employee_id'];
                $deductions = $_POST['deductions'];
                $basic_salary = $_POST['basic_salary'];
                $retirement_contribution = calculate_retirement($basic_salary);
                $thirteenth_month = calculate_13th_month($employee_id);
                $total = $basic_salary - $deductions - $retirement_contribution;

                $sql = "UPDATE payroll SET deductions = :deductions, basic_salary = :basic_salary, 
                        retirement_contribution = :retirement_contribution, thirteenth_month = :thirteenth_month, 
                        total_salary = :total_salary WHERE id = :id";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bindParam(':deductions', $deductions);
                    $stmt->bindParam(':basic_salary', $basic_salary);
                    $stmt->bindParam(':retirement_contribution', $retirement_contribution);
                    $stmt->bindParam(':thirteenth_month', $thirteenth_month);
                    $stmt->bindParam(':total_salary', $total);
                    $stmt->bindParam(':id', $id);
                    
                    if ($stmt->execute()) {
                        $msg = "Payroll record updated successfully.";
                    } else {
                        $msg = "Error updating payroll record.";
                    }
                }
                break;

            case 'archive':
                $id = $_POST['id'];
                $archive_date = date('Y-m-d H:i:s');

                $sql = "UPDATE payroll SET is_archived = TRUE, archive_date = :archive_date WHERE id = :id";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bindParam(':archive_date', $archive_date);
                    $stmt->bindParam(':id', $id);
                    
                    if ($stmt->execute()) {
                        $msg = "Payroll record archived successfully.";
                    } else {
                        $msg = "Error archiving payroll record.";
                    }
                }
                break;

            case 'unarchive':
                $id = $_POST['id'];
                $sql = "UPDATE payroll SET is_archived = FALSE, archive_date = NULL WHERE id = :id";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bindParam(':id', $id);
                    
                    if ($stmt->execute()) {
                        $msg = "Payroll record unarchived successfully.";
                    } else {
                        $msg = "Error unarchiving payroll record.";
                    }
                }
                break;
        }
    }
}

// Fetch employee data from HR database
$employees = [];
$hr_sql = "SELECT employee_id, employee_name, basic_salary, absents FROM employees ORDER BY date DESC";
$hr_result = $hr_conn->query($hr_sql);

if ($hr_result) {
    while ($row = $hr_result->fetch(PDO::FETCH_ASSOC)) {
        $employees[$row['employee_id']] = $row;
    }
}

// Fetch active payroll records
$payroll_records = [];
$sql = "SELECT * FROM payroll WHERE is_archived = FALSE ORDER BY id DESC";
$result = $conn->query($sql);

// Fetch archived payroll records
$archived_records = [];
$archive_sql = "SELECT * FROM payroll WHERE is_archived = TRUE ORDER BY archive_date DESC";
$archive_result = $conn->query($archive_sql);

if ($result) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $payroll_records[] = $row;
    }
}

if ($archive_result) {
    while ($row = $archive_result->fetch(PDO::FETCH_ASSOC)) {
        $archived_records[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management - Finance System</title>
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
        <h2 class="mb-4">Payroll Management</h2>
        
        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>



        <!-- Payroll Status Card -->
        <div class="card mb-4">
            <div class="card-body">
                <h4>Payroll Status</h4>
                <p class="text-muted">Payroll records are automatically generated from HR data daily.</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> The system automatically calculates:
                    <ul class="mb-0">
                        <li>Attendance based on HR records</li>
                        <li>Deductions for absences</li>
                        <li>Retirement contributions (5% of basic salary)</li>
                        <li>13th month pay allocation</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Active Payroll Records Table -->
        <div class="table-responsive">
            <h3>Active Payroll Records</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Attendance</th>
                        <th>Basic Salary</th>
                        <th>Deductions</th>
                        <th>Retirement</th>
                        <th>13th Month</th>
                        <th>Total Salary</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payroll_records as $record): ?>
                    <tr data-id="<?php echo $record['id']; ?>">
                        <td><?php echo htmlspecialchars($record['employee_id']); ?></td>
                        <td><?php echo htmlspecialchars($record['employee_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['attendance']); ?></td>
                        <td>₱<?php echo number_format($record['basic_salary'], 2); ?></td>
                        <td>₱<?php echo number_format($record['deductions'], 2); ?></td>
                        <td>₱<?php echo number_format($record['retirement_contribution'], 2); ?></td>
                        <td>₱<?php echo number_format($record['thirteenth_month'], 2); ?></td>
                        <td>₱<?php echo number_format($record['total_salary'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $record['payment_status'] === 'paid' ? 'success' : ($record['payment_status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                <?php echo ucfirst($record['payment_status']); ?>
                            </span>
                        </td>
                        <td class="action-buttons">
                            <?php if ($record['payment_status'] === 'unpaid'): ?>
                            <button class="btn btn-sm btn-success" onclick="processPayment(<?php echo $record['id']; ?>)">
                                <i class="fas fa-money-bill"></i>
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-primary" onclick="editPayroll(<?php echo $record['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="archivePayroll(<?php echo $record['id']; ?>)">
                                <i class="fas fa-archive"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Archived Payroll Records Table -->
        <div class="table-responsive mt-4">
            <h3>Archived Payroll Records</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Attendance</th>
                        <th>Basic Salary</th>
                        <th>Deductions</th>
                        <th>Retirement</th>
                        <th>13th Month</th>
                        <th>Total Salary</th>
                        <th>Archive Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archived_records as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['employee_id']); ?></td>
                        <td><?php echo htmlspecialchars($record['employee_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['attendance']); ?></td>
                        <td>₱<?php echo number_format($record['basic_salary'], 2); ?></td>
                        <td>₱<?php echo number_format($record['deductions'], 2); ?></td>
                        <td>₱<?php echo number_format($record['retirement_contribution'], 2); ?></td>
                        <td>₱<?php echo number_format($record['thirteenth_month'], 2); ?></td>
                        <td>₱<?php echo number_format($record['total_salary'], 2); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($record['archive_date'])); ?></td>
                        <td class="action-buttons">
                            <button class="btn btn-sm btn-success" onclick="unarchivePayroll(<?php echo $record['id']; ?>)">
                                <i class="fas fa-box-open"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Payroll Modal -->
        <div class="modal fade" id="editPayrollModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Payroll Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" id="edit_id">
                            <div class="mb-3">
                                <label class="form-label">Basic Salary</label>
                                <input type="number" step="0.01" class="form-control" name="basic_salary" id="edit_basic_salary" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deductions</label>
                                <input type="number" step="0.01" class="form-control" name="deductions" id="edit_deductions" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">13th Month Pay</label>
                                <input type="number" step="0.01" class="form-control" name="thirteenth_month" id="edit_thirteenth_month" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Retirement Contribution</label>
                                <input type="number" step="0.01" class="form-control" name="retirement_contribution" id="edit_retirement" readonly>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function processPayment(payrollId) {
            Swal.fire({
                title: 'Process Payment',
                text: 'Are you sure you want to process this payment?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, process it!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('payroll_id', payrollId);

                    fetch('process_payment.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Success', data.message, 'success')
                            .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'Failed to process payment', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Failed to process payment', 'error');
                    });
                }
            });
        }

        function updateEmployeeInfo(employeeId) {
            const selectElement = document.querySelector('select[name="employee_id"]');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            
            if (selectedOption.value) {
                document.getElementById('employee_name').value = selectedOption.dataset.name;
                document.getElementById('basic_salary').value = selectedOption.dataset.salary;
                document.getElementById('attendance').value = 30 - parseInt(selectedOption.dataset.absents);
            } else {
                document.getElementById('employee_name').value = '';
                document.getElementById('basic_salary').value = '';
                document.getElementById('attendance').value = '';
            }
        }
    </script>
    <script>
        function editPayroll(id) {
            document.getElementById('edit_id').value = id;
            // Get the row data
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                const basicSalary = row.querySelector('td:nth-child(4)').textContent.replace('₱', '').replace(',', '');
                const deductions = row.querySelector('td:nth-child(5)').textContent.replace('₱', '').replace(',', '');
                
                document.getElementById('edit_basic_salary').value = parseFloat(basicSalary);
                document.getElementById('edit_deductions').value = parseFloat(deductions);
                
                // Calculate and set 13th month and retirement
                updateBenefits(parseFloat(basicSalary));
            }
            var modal = new bootstrap.Modal(document.getElementById('editPayrollModal'));
            modal.show();
        }

        async function updateBenefits(basicSalary) {
            // Get employee ID from the current row
            const id = document.getElementById('edit_id').value;
            const row = document.querySelector(`tr[data-id="${id}"]`);
            const employeeId = row.querySelector('td:nth-child(1)').textContent;

            try {
                // Fetch 13th month pay calculation from server
                const response = await fetch(`?action=calculate_13th_month&employee_id=${employeeId}`);
                const data = await response.json();
                document.getElementById('edit_thirteenth_month').value = data.thirteenth_month;
            } catch (error) {
                console.error('Error calculating 13th month pay:', error);
                document.getElementById('edit_thirteenth_month').value = 0;
            }
            
            // Calculate retirement (5% of basic salary)
            document.getElementById('edit_retirement').value = basicSalary * 0.05;
        }

        // Add event listener to basic salary input
        document.getElementById('edit_basic_salary').addEventListener('input', function() {
            updateBenefits(parseFloat(this.value) || 0);
        });

        function archivePayroll(id) {
            if (confirm('Are you sure you want to archive this payroll record?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="archive">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function unarchivePayroll(id) {
            if (confirm('Are you sure you want to unarchive this payroll record?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="unarchive">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>