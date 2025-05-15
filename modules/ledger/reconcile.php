<?php
require_once('../../classes/LedgerManagement.php');

$ledger = new LedgerManagement();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $recordData = [
                    'account_id' => $_POST['account_id'],
                    'statement_date' => $_POST['statement_date'],
                    'statement_balance' => $_POST['statement_balance'],
                    'book_balance' => $_POST['book_balance'],
                    'notes' => $_POST['notes']
                ];
                $ledger->createReconciliationRecord($recordData);
                break;

            case 'complete':
                $ledger->completeReconciliation(
                    $_POST['record_id'],
                    1 // Replace with actual user ID from session
                );
                break;

            case 'add_item':
                $itemData = [
                    'record_id' => $_POST['record_id'],
                    'transaction_date' => $_POST['transaction_date'],
                    'description' => $_POST['description'],
                    'amount' => $_POST['amount'],
                    'type' => $_POST['type'],
                    'reference_number' => $_POST['reference_number']
                ];
                $ledger->addReconciliationItem($itemData);
                break;
        }
    }
}

// Get reconciliation records and accounts
$records = $ledger->getReconciliationRecords();
$accounts = $ledger->getChartOfAccounts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger Reconciliation</title>
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
                <h2 class="mb-3">Ledger Reconciliation</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRecordModal">
                    Start New Reconciliation
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="reconciliationTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Statement Date</th>
                            <th>Statement Balance</th>
                            <th>Book Balance</th>
                            <th>Difference</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record): ?>
                        <tr>
                            <td>
                                <?php
                                foreach ($accounts as $account) {
                                    if ($account['account_id'] == $record['account_id']) {
                                        echo htmlspecialchars($account['account_number'] . ' - ' . $account['account_name']);
                                        break;
                                    }
                                }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($record['statement_date']) ?></td>
                            <td class="text-end"><?= number_format($record['statement_balance'], 2) ?></td>
                            <td class="text-end"><?= number_format($record['book_balance'], 2) ?></td>
                            <td class="text-end <?= $record['difference'] != 0 ? 'text-danger' : '' ?>">
                                <?= number_format($record['difference'], 2) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $record['status'] === 'completed' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($record['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($record['status'] === 'in_progress'): ?>
                                <button class="btn btn-sm btn-primary add-item"
                                        data-record-id="<?= $record['record_id'] ?>">
                                    Add Item
                                </button>
                                <button class="btn btn-sm btn-success complete-reconciliation"
                                        data-record-id="<?= $record['record_id'] ?>">
                                    Complete
                                </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-info view-items"
                                        data-record-id="<?= $record['record_id'] ?>">
                                    View Items
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Record Modal -->
    <div class="modal fade" id="createRecordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Start New Reconciliation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Account</label>
                            <select name="account_id" class="form-control" required>
                                <option value="">Select Account</option>
                                <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account['account_id'] ?>">
                                    <?= htmlspecialchars($account['account_number'] . ' - ' . $account['account_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Statement Date</label>
                            <input type="date" name="statement_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Statement Balance</label>
                            <input type="number" name="statement_balance" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Book Balance</label>
                            <input type="number" name="book_balance" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Start Reconciliation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Reconciliation Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_item">
                        <input type="hidden" name="record_id" id="item_record_id">
                        <div class="mb-3">
                            <label class="form-label">Transaction Date</label>
                            <input type="date" name="transaction_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-control" required>
                                <option value="outstanding">Outstanding</option>
                                <option value="cleared">Cleared</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Item</button>
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
            $('#reconciliationTable').DataTable({
                order: [[1, 'desc']]
            });

            // Add reconciliation item
            document.querySelectorAll('.add-item').forEach(button => {
                button.addEventListener('click', function() {
                    const modal = new bootstrap.Modal(document.getElementById('addItemModal'));
                    document.getElementById('item_record_id').value = this.dataset.recordId;
                    modal.show();
                });
            });

            // Complete reconciliation
            document.querySelectorAll('.complete-reconciliation').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to complete this reconciliation?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.innerHTML = `
                            <input type="hidden" name="action" value="complete">
                            <input type="hidden" name="record_id" value="${this.dataset.recordId}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            // View reconciliation items
            document.querySelectorAll('.view-items').forEach(button => {
                button.addEventListener('click', function() {
                    // Implement items view
                    alert('Reconciliation items view feature coming soon!');
                });
            });
        });
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>