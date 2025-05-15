<?php
require_once '../../config/database.php';
session_start();

// Initialize BudgetManagement class
$budgetManagement = new BudgetManagement();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Management System</title>
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
                <li><a href="modules/payroll/Disbursement.php" class="nav-link">Disbursement</a></li>
                <li><a href="modules/payroll/index.php" class="nav-link">Payroll</a></li>
                <li><a href="modules/payroll/reports.php" class="nav-link">Staff Benefits Management</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-chart-pie"></i> Budget Management</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="modules/budget/planning.php" class="nav-link">Budget Planning</a></li>
                <li><a href="modules/budget/allocation.php" class="nav-link">Allocation</a></li>
                <li><a href="modules/budget/tracking.php" class="nav-link">Tracking</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-hand-holding-usd"></i> Collection</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="modules/collection/fees.php" class="nav-link">Fee Collection</a></li>
                <li><a href="modules/collection/dues.php" class="nav-link">Due Management</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-book"></i> General Ledger</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="modules/ledger/entries.php" class="nav-link">Journal Entries</a></li>
                <li><a href="modules/ledger/accounts.php" class="nav-link">Chart of Accounts</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-file-invoice-dollar"></i> Accounts Payable</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="modules/ledger/entries.php" class="nav-link">Journal Entries</a></li>
                <li><a href="modules/ledger/accounts.php" class="nav-link">Chart of Accounts</a></li>
            </ul>

            <div class="nav-link">
                <div><i class="fas fa-file-invoice"></i> Accounts Receivable</div>
                <i class="fas fa-chevron-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="modules/ledger/entries.php" class="nav-link">Journal Entries</a></li>
                <li><a href="modules/ledger/accounts.php" class="nav-link">Chart of Accounts</a></li>
            </ul>
            <a href="logout.php" class="nav-link mt-4">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>
<body class="bg-light">
<div class="main-content">
    <h1 class="text-center mb-4">Budget Management System</h1>

    <!-- Add Distribution Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add Budget Distribution</h5>
        </div>
        <div class="card-body">
            <form action="" method="POST" id="distributionForm">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="department" class="form-label">Department</label>
                        <select class="form-select" id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="Academic Affairs">Academic Affairs</option>
                            <option value="Student Services">Student Services</option>
                            <option value="Administration">Administration</option>
                            <option value="Research">Research</option>
                            <option value="Facilities">Facilities</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="percentage" class="form-label">Budget Percentage</label>
                        <input type="number" class="form-control" id="percentage" name="percentage" min="0" max="100" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="amount" class="form-label">Allocated Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" required>
                    </div>
                </div>
                <button type="submit" name="add_distribution" class="btn btn-primary">Add Distribution</button>
            </form>
        </div>
    </div>

    <!-- Add Expense Form -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add Expense</h5>
        </div>
        <div class="card-body">
            <form action="" method="POST" id="expenseForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="expense_department" class="form-label">Department</label>
                        <select class="form-select" id="expense_department" name="expense_department" required>
                            <option value="">Select Department</option>
                            <option value="Academic Affairs">Academic Affairs</option>
                            <option value="Student Services">Student Services</option>
                            <option value="Administration">Administration</option>
                            <option value="Research">Research</option>
                            <option value="Facilities">Facilities</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="expense_category" class="form-label">Category</label>
                        <select class="form-select" id="expense_category" name="expense_category" required>
                            <option value="">Select Category</option>
                            <option value="Supplies">Supplies</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Services">Services</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="expense_amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="expense_amount" name="expense_amount" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" required>
                    </div>
                </div>
                <button type="submit" name="add_expense" class="btn btn-success">Add Expense</button>
            </form>
        </div>
    </div>
<?php

    // Process form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_distribution'])) {
            $department = $_POST['department'];
            $percentage = $_POST['percentage'];
            $amount = $_POST['amount'];
            
            try {
                $budgetManagement->createBudgetAllocation($fiscalYear, $amount);
                echo '<div class="alert alert-success" role="alert">Budget distribution added successfully!</div>';
            } catch (Exception $e) {
                echo '<div class="alert alert-danger" role="alert">Error: ' . $e->getMessage() . '</div>';
            }
        }
        
        if (isset($_POST['add_expense'])) {
            $department = $_POST['expense_department'];
            $category = $_POST['expense_category'];
            $amount = $_POST['expense_amount'];
            $description = $_POST['description'];
            
            try {
                $budgetManagement->trackExpenses(1, 1, $amount, $description);
                echo '<div class="alert alert-success" role="alert">Expense added successfully!</div>';
            } catch (Exception $e) {
                echo '<div class="alert alert-danger" role="alert">Error: ' . $e->getMessage() . '</div>';
            }
        }
    }


class BudgetManagement {
    private $db;
    private $financeConn;
    private $enrollmentConn;
    private $budgetConn;

    public function __construct() {
        $this->db = new Database();
        $this->financeConn = $this->db->getConnection('finance');
        $this->enrollmentConn = $this->db->getConnection('enrollment');
        $this->budgetConn = $this->db->getConnection('budget');
    }

    public function getTotalRevenue($fiscalYear) {
        // For demonstration, using the provided example revenue
        return 10000000.00;
    }

    public function getBudgetCategories() {
        $sql = "SELECT * FROM budget_categories";
        $stmt = $this->budgetConn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBudgetAllocation($fiscalYear, $totalRevenue) {
        try {
            $this->budgetConn->beginTransaction();

            // Create budget allocation record
            $sql = "INSERT INTO budget_allocation (fiscal_year, total_budget) VALUES (?, ?)";
            $stmt = $this->budgetConn->prepare($sql);
            $stmt->execute([$fiscalYear, $totalRevenue]);
            $allocationId = $this->budgetConn->lastInsertId();

            // Get budget categories
            $categories = $this->getBudgetCategories();
            
            // Allocate budget to departments
            $departments = [
                'Academic Affairs' => 30,
                'Student Services' => 20,
                'Administration' => 25,
                'Research' => 15,
                'Facilities' => 10
            ];

            foreach ($departments as $dept => $percentage) {
                $allocatedAmount = ($totalRevenue * $percentage) / 100;
                $sql = "INSERT INTO department_budget 
                        (department_name, allocation_id, budget_percentage, allocated_amount) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $this->budgetConn->prepare($sql);
                $stmt->execute([$dept, $allocationId, $percentage, $allocatedAmount]);
            }

            $this->budgetConn->commit();
            return true;
        } catch (Exception $e) {
            $this->budgetConn->rollBack();
            throw $e;
        }
    }

    public function getStudentFeesData() {
        $sql = "SELECT s.student_id, s.first_name, s.last_name, 
                       f.fee_name, sf.amount_paid, sf.balance, sf.status
                FROM students s
                JOIN student_fees sf ON s.student_id = sf.student_id
                JOIN fees f ON sf.fee_id = f.fee_id";
        $stmt = $this->budgetConn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trackExpenses($categoryId, $deptBudgetId, $amount, $description) {
        $sql = "INSERT INTO expenses (category_id, dept_budget_id, amount, description, expense_date) 
                VALUES (?, ?, ?, ?, CURDATE())";
        $stmt = $this->budgetConn->prepare($sql);
        return $stmt->execute([$categoryId, $deptBudgetId, $amount, $description]);
    }

    public function getBudgetSummary($fiscalYear) {
        $sql = "SELECT 
                    ba.total_budget,
                    SUM(e.amount) as total_expenses,
                    (ba.total_budget - SUM(COALESCE(e.amount, 0))) as remaining_budget
                FROM budget_allocation ba
                LEFT JOIN department_budget db ON ba.allocation_id = db.allocation_id
                LEFT JOIN expenses e ON db.dept_budget_id = e.dept_budget_id
                WHERE ba.fiscal_year = ?
                GROUP BY ba.allocation_id";
        $stmt = $this->budgetConn->prepare($sql);
        $stmt->execute([$fiscalYear]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Initialize the budget management system
$budgetManagement = new BudgetManagement();

// Example usage:
try {
    // Create budget allocation for 2024
    $fiscalYear = 2024;
    $totalRevenue = $budgetManagement->getTotalRevenue($fiscalYear);
    $budgetManagement->createBudgetAllocation($fiscalYear, $totalRevenue);

    // Get budget summary
    $summary = $budgetManagement->getBudgetSummary($fiscalYear);
    
    // Get student fees data
    $studentFees = $budgetManagement->getStudentFeesData();

    // Display Budget Summary
    echo '<div class="row mb-4">';
    if ($summary) {
        echo '<div class="col-md-4">
                <div class="card dashboard-card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-money-bill-wave"></i> Total Budget</h5>
                        <h3>₱' . number_format($summary['total_budget'], 2) . '</h3>
                    </div>
                </div>
            </div>';
        echo '<div class="col-md-4">
                <div class="card dashboard-card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-pie"></i> Total Expenses</h5>
                        <h3>₱' . number_format($summary['total_expenses'] ?? 0, 2) . '</h3>
                    </div>
                </div>
            </div>';
        echo '<div class="col-md-4">
                <div class="card dashboard-card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-wallet"></i> Remaining Budget</h5>
                        <h3>₱' . number_format($summary['remaining_budget'] ?? 0, 2) . '</h3>
                    </div>
                </div>
            </div>';
    }
    echo '</div>';

    // Display Department Budget Distribution
    echo '<div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-building"></i> Department Budget Distribution</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Percentage</th>
                                <th>Allocated Amount</th>
                            </tr>
                        </thead>
                        <tbody>';
    
    $departments = [
        'Academic Affairs' => 30,
        'Student Services' => 20,
        'Administration' => 25,
        'Research' => 15,
        'Facilities' => 10
    ];

    foreach ($departments as $dept => $percentage) {
        $allocatedAmount = ($totalRevenue * $percentage) / 100;
        echo "<tr>
                <td>$dept</td>
                <td>$percentage%</td>
                <td>₱" . number_format($allocatedAmount, 2) . "</td>
            </tr>";
    }

    echo '</tbody></table></div></div></div>';

    // Display Student Fees Table
    if ($studentFees) {
        echo '<div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-user-graduate"></i> Student Fees Status</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Fee Name</th>
                                    <th>Amount Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>';

        foreach ($studentFees as $fee) {
            $statusClass = $fee['status'] === 'Paid' ? 'success' : ($fee['status'] === 'Partial' ? 'warning' : 'danger');
            echo "<tr>
                    <td>{$fee['first_name']} {$fee['last_name']}</td>
                    <td>{$fee['fee_name']}</td>
                    <td>₱" . number_format($fee['amount_paid'], 2) . "</td>
                    <td>₱" . number_format($fee['balance'], 2) . "</td>
                    <td><span class='badge bg-{$statusClass}'>{$fee['status']}</span></td>
                </tr>";
        }

        echo '</tbody></table></div></div></div>';
    }

} catch (Exception $e) {
    echo '<div class="alert alert-danger" role="alert">';
    echo "Error: " . $e->getMessage();
    echo '</div>';
}
?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calculate allocated amount based on percentage
        document.getElementById('percentage').addEventListener('input', function() {
            const totalBudget = <?php echo $summary['total_budget'] ?? 0; ?>;
            const percentage = parseFloat(this.value) || 0;
            const allocatedAmount = (totalBudget * percentage) / 100;
            document.getElementById('amount').value = allocatedAmount.toFixed(2);
        });

        // Form validation
        document.getElementById('distributionForm').addEventListener('submit', function(e) {
            const percentage = parseFloat(document.getElementById('percentage').value);
            if (percentage < 0 || percentage > 100) {
                e.preventDefault();
                alert('Percentage must be between 0 and 100');
            }
        });

        document.getElementById('expenseForm').addEventListener('submit', function(e) {
            const amount = parseFloat(document.getElementById('expense_amount').value);
            if (amount <= 0) {
                e.preventDefault();
                alert('Expense amount must be greater than 0');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.nav-link:not(:last-child)');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    this.classList.toggle('active');
                    const submenu = this.nextElementSibling;
                    if (submenu && submenu.classList.contains('submenu')) {
                        submenu.classList.toggle('show');
                    }
                });
            });
        });
    </script>
</body>
</html>