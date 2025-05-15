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
            case 'update_attendance':
                $employee_id = $_POST['employee_id'];
                $attendance_date = $_POST['attendance_date'];
                $status = $_POST['status'];
                $remarks = $_POST['remarks'];
                
                $sql = "UPDATE attendance SET status = ?, remarks = ? 
                        WHERE employee_id = ? AND attendance_date = ?";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$status, $remarks, $employee_id, $attendance_date]);
                    $msg = "Attendance record updated successfully.";
                }
                break;

            case 'bulk_import':
                // Handle bulk import of attendance records
                if (isset($_FILES['attendance_file'])) {
                    $file = $_FILES['attendance_file']['tmp_name'];
                    if (($handle = fopen($file, "r")) !== FALSE) {
                        $conn->beginTransaction();
                        try {
                            while (($data = fgetcsv($handle)) !== FALSE) {
                                $sql = "INSERT INTO attendance (employee_id, attendance_date, status, remarks) 
                                        VALUES (?, ?, ?, ?)";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute($data);
                            }
                            $conn->commit();
                            $msg = "Attendance records imported successfully.";
                        } catch (Exception $e) {
                            $conn->rollBack();
                            $msg = "Error importing attendance records: " . $e->getMessage();
                        }
                        fclose($handle);
                    }
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

// Fetch recent attendance records
$attendance_sql = "SELECT a.*, e.employee_name 
                  FROM attendance a 
                  JOIN employees e ON a.employee_id = e.employee_id 
                  ORDER BY a.attendance_date DESC 
                  LIMIT 50";
$attendance_result = $conn->query($attendance_sql);
$attendance_records = $attendance_result->fetchAll(PDO::FETCH_ASSOC);

// Calculate attendance statistics
$stats_sql = "SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count
             FROM attendance 
             WHERE attendance_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
$stats_result = $conn->query($stats_sql);
$attendance_stats = $stats_result->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Integration - Finance System</title>
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
        <h2>Attendance Integration</h2>
        
        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Attendance Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>Total Records</h5>
                        <h3><?php echo $attendance_stats['total_records']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Present</h5>
                        <h3><?php echo $attendance_stats['present_count']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5>Absent</h5>
                        <h3><?php echo $attendance_stats['absent_count']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5>Late</h5>
                        <h3><?php echo $attendance_stats['late_count']; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Import Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4>Bulk Import Attendance</h4>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="row g-3">
                    <input type="hidden" name="action" value="bulk_import">
                    
                    <div class="col-md-6">
                        <label class="form-label">Upload CSV File</label>
                        <input type="file" name="attendance_file" class="form-control" accept=".csv" required>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Import Records
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Attendance Records -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4>Recent Attendance Records</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_records as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['employee_name']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($record['attendance_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $record['status'] === 'present' ? 'success' : 
                                             ($record['status'] === 'absent' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($record['remarks']); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="editAttendance(<?php echo $record['id']; ?>)">
                                        <i class="fas fa-edit"></i>
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
    <script>
        function editAttendance(recordId) {
            // Implement edit functionality
            alert('Edit attendance record ID: ' + recordId);
        }
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>