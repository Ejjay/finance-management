<?php
require_once('../../classes/LedgerManagement.php');

$ledger = new LedgerManagement();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $accountData = [
                    'account_number' => $_POST['account_number'],
                    'account_name' => $_POST['account_name'],
                    'account_type' => $_POST['account_type'],
                    'parent_account_id' => $_POST['parent_account_id'] ?: null,
                    'description' => $_POST['description']
                ];
                $ledger->addAccount($accountData);
                break;

            case 'update':
                $accountData = [
                    'account_id' => $_POST['account_id'],
                    'account_name' => $_POST['account_name'],
                    'account_type' => $_POST['account_type'],
                    'description' => $_POST['description'],
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];
                $ledger->updateAccount($accountData);
                break;
        }
    }
}

// Get all accounts
$accounts = $ledger->getChartOfAccounts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart of Accounts</title>
    <link rel="stylesheet" href="../../">
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/plugins/datatables/datatables.min.css">
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
        <div class="row mb-4">
            <div class="col">
                <h2 class="mb-3">Chart of Accounts</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                    Create New Account
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="accountsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Account Number</th>
                            <th>Account Name</th>
                            <th>Type</th>
                            <th>Parent Account</th>
                            <th>Current Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td><?= htmlspecialchars($account['account_number']) ?></td>
                            <td><?= htmlspecialchars($account['account_name']) ?></td>
                            <td><?= ucfirst(htmlspecialchars($account['account_type'])) ?></td>
                            <td>
                                <?php
                                if ($account['parent_account_id']) {
                                    foreach ($accounts as $parent) {
                                        if ($parent['account_id'] == $account['parent_account_id']) {
                                            echo htmlspecialchars($parent['account_number'] . ' - ' . $parent['account_name']);
                                            break;
                                        }
                                    }
                                }
                                ?>
                            </td>
                            <td class="text-end"><?= number_format($account['current_balance'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $account['is_active'] ? 'success' : 'danger' ?>">
                                    <?= $account['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info edit-account"
                                        data-account-id="<?= $account['account_id'] ?>"
                                        data-account-number="<?= htmlspecialchars($account['account_number']) ?>"
                                        data-account-name="<?= htmlspecialchars($account['account_name']) ?>"
                                        data-account-type="<?= htmlspecialchars($account['account_type']) ?>"
                                        data-description="<?= htmlspecialchars($account['description']) ?>"
                                        data-is-active="<?= $account['is_active'] ?>">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-secondary view-transactions"
                                        data-account-id="<?= $account['account_id'] ?>">
                                    Transactions
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Account Modal -->
    <div class="modal fade" id="createAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="account_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Name</label>
                            <input type="text" name="account_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Type</label>
                            <select name="account_type" class="form-control" required>
                                <option value="asset">Asset</option>
                                <option value="liability">Liability</option>
                                <option value="equity">Equity</option>
                                <option value="revenue">Revenue</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Parent Account</label>
                            <select name="parent_account_id" class="form-control">
                                <option value="">None</option>
                                <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account['account_id'] ?>">
                                    <?= htmlspecialchars($account['account_number'] . ' - ' . $account['account_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div class="modal fade" id="editAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="account_id" id="edit_account_id">
                        <div class="mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" id="edit_account_number" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Name</label>
                            <input type="text" name="account_name" id="edit_account_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Type</label>
                            <select name="account_type" id="edit_account_type" class="form-control" required>
                                <option value="asset">Asset</option>
                                <option value="liability">Liability</option>
                                <option value="equity">Equity</option>
                                <option value="revenue">Revenue</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="edit_is_active" class="form-check-input">
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/plugins/datatables/datatables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            $('#accountsTable').DataTable({
                order: [[0, 'asc']]
            });

            // Edit account modal
            document.querySelectorAll('.edit-account').forEach(button => {
                button.addEventListener('click', function() {
                    const modal = new bootstrap.Modal(document.getElementById('editAccountModal'));
                    document.getElementById('edit_account_id').value = this.dataset.accountId;
                    document.getElementById('edit_account_number').value = this.dataset.accountNumber;
                    document.getElementById('edit_account_name').value = this.dataset.accountName;
                    document.getElementById('edit_account_type').value = this.dataset.accountType;
                    document.getElementById('edit_description').value = this.dataset.description;
                    document.getElementById('edit_is_active').checked = this.dataset.isActive === '1';
                    modal.show();
                });
            });

            // View transactions
            document.querySelectorAll('.view-transactions').forEach(button => {
                button.addEventListener('click', function() {
                    // Implement transaction history view
                    alert('Transaction history feature coming soon!');
                });
            });
        });
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>