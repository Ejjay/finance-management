<?php
require_once('../../classes/LedgerManagement.php');

$ledger = new LedgerManagement();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $transferData = [
                    'from_account_id' => $_POST['from_account_id'],
                    'to_account_id' => $_POST['to_account_id'],
                    'amount' => $_POST['amount'],
                    'transfer_date' => $_POST['transfer_date'],
                    'description' => $_POST['description'],
                    'reference_number' => $_POST['reference_number'],
                    'created_by' => 1 // Replace with actual user ID from session
                ];
                $ledger->createFundTransfer($transferData);
                break;

            case 'complete':
                $ledger->completeFundTransfer($_POST['transfer_id']);
                break;

            case 'cancel':
                $ledger->cancelFundTransfer($_POST['transfer_id']);
                break;
        }
    }
}

// Get all fund transfers and accounts
$transfers = $ledger->getFundTransfers();
$accounts = $ledger->getChartOfAccounts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Transfer Tracking</title>
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
                <h2 class="mb-3">Fund Transfer Tracking</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTransferModal">
                    Create New Transfer
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="transfersTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Transfer Number</th>
                            <th>Date</th>
                            <th>From Account</th>
                            <th>To Account</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Reference</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transfers as $transfer): ?>
                        <tr>
                            <td><?= htmlspecialchars($transfer['transfer_number']) ?></td>
                            <td><?= htmlspecialchars($transfer['transfer_date']) ?></td>
                            <td>
                                <?php
                                foreach ($accounts as $account) {
                                    if ($account['account_id'] == $transfer['from_account_id']) {
                                        echo htmlspecialchars($account['account_number'] . ' - ' . $account['account_name']);
                                        break;
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                foreach ($accounts as $account) {
                                    if ($account['account_id'] == $transfer['to_account_id']) {
                                        echo htmlspecialchars($account['account_number'] . ' - ' . $account['account_name']);
                                        break;
                                    }
                                }
                                ?>
                            </td>
                            <td class="text-end"><?= number_format($transfer['amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $transfer['status'] === 'completed' ? 'success' : 
                                    ($transfer['status'] === 'cancelled' ? 'danger' : 'warning')
                                ?>">
                                    <?= ucfirst($transfer['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($transfer['reference_number']) ?></td>
                            <td>
                                <?php if ($transfer['status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-success complete-transfer"
                                        data-transfer-id="<?= $transfer['transfer_id'] ?>">
                                    Complete
                                </button>
                                <button class="btn btn-sm btn-danger cancel-transfer"
                                        data-transfer-id="<?= $transfer['transfer_id'] ?>">
                                    Cancel
                                </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-info view-transfer"
                                        data-transfer-id="<?= $transfer['transfer_id'] ?>">
                                    View
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Transfer Modal -->
    <div class="modal fade" id="createTransferModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Fund Transfer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="transferForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">From Account</label>
                            <select name="from_account_id" class="form-control" required>
                                <option value="">Select Account</option>
                                <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account['account_id'] ?>">
                                    <?= htmlspecialchars($account['account_number'] . ' - ' . $account['account_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">To Account</label>
                            <select name="to_account_id" class="form-control" required>
                                <option value="">Select Account</option>
                                <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account['account_id'] ?>">
                                    <?= htmlspecialchars($account['account_number'] . ' - ' . $account['account_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Transfer Date</label>
                            <input type="date" name="transfer_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Transfer</button>
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
            $('#transfersTable').DataTable({
                order: [[0, 'desc']]
            });

            // Form validation
            document.getElementById('transferForm').addEventListener('submit', function(e) {
                const fromAccount = this.querySelector('[name="from_account_id"]').value;
                const toAccount = this.querySelector('[name="to_account_id"]').value;

                if (fromAccount === toAccount) {
                    e.preventDefault();
                    alert('From and To accounts cannot be the same!');
                }
            });

            // Complete transfer
            document.querySelectorAll('.complete-transfer').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to complete this transfer?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.innerHTML = `
                            <input type="hidden" name="action" value="complete">
                            <input type="hidden" name="transfer_id" value="${this.dataset.transferId}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            // Cancel transfer
            document.querySelectorAll('.cancel-transfer').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to cancel this transfer?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.innerHTML = `
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="transfer_id" value="${this.dataset.transferId}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            // View transfer details
            document.querySelectorAll('.view-transfer').forEach(button => {
                button.addEventListener('click', function() {
                    // Implement transfer details view
                    alert('Transfer details feature coming soon!');
                });
            });
        });
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>