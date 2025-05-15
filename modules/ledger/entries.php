<?php
require_once('../../classes/LedgerManagement.php');

$ledger = new LedgerManagement();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $entryData = [
                    'entry_date' => $_POST['entry_date'],
                    'description' => $_POST['description'],
                    'reference_number' => $_POST['reference_number'],
                    'created_by' => 1 // Replace with actual user ID from session
                ];
                $items = [];
                foreach ($_POST['account_id'] as $key => $account_id) {
                    $items[] = [
                        'account_id' => $account_id,
                        'debit_amount' => $_POST['debit_amount'][$key] ?: 0,
                        'credit_amount' => $_POST['credit_amount'][$key] ?: 0,
                        'description' => $_POST['item_description'][$key]
                    ];
                }
                $ledger->createJournalEntry($entryData, $items);
                break;

            case 'post':
                $ledger->postJournalEntry($_POST['entry_id']);
                break;

            case 'void':
                $ledger->voidJournalEntry($_POST['entry_id']);
                break;
        }
    }
}

// Get list of journal entries
$entries = $ledger->getJournalEntries();
$accounts = $ledger->getChartOfAccounts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Entry Management</title>
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
                <h2 class="mb-3">Journal Entry Management</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEntryModal">
                    Create New Entry
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="entriesTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Entry Number</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td><?= htmlspecialchars($entry['entry_number']) ?></td>
                            <td><?= htmlspecialchars($entry['entry_date']) ?></td>
                            <td><?= htmlspecialchars($entry['description']) ?></td>
                            <td><?= htmlspecialchars($entry['reference_number']) ?></td>
                            <td><?= number_format($entry['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $entry['status'] === 'posted' ? 'success' : ($entry['status'] === 'voided' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($entry['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($entry['status'] === 'draft'): ?>
                                <button class="btn btn-sm btn-success post-entry" data-entry-id="<?= $entry['entry_id'] ?>">
                                    Post
                                </button>
                                <button class="btn btn-sm btn-danger void-entry" data-entry-id="<?= $entry['entry_id'] ?>">
                                    Void
                                </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-info view-entry" data-entry-id="<?= $entry['entry_id'] ?>">
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

    <!-- Create Entry Modal -->
    <div class="modal fade" id="createEntryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Journal Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="journalEntryForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Entry Date</label>
                                <input type="date" name="entry_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reference Number</label>
                                <input type="text" name="reference_number" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>
                        <div id="entryItems">
                            <div class="entry-item row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Account</label>
                                    <select name="account_id[]" class="form-control" required>
                                        <option value="">Select Account</option>
                                        <?php foreach ($accounts as $account): ?>
                                        <option value="<?= $account['account_id'] ?>">
                                            <?= htmlspecialchars($account['account_number'] . ' - ' . $account['account_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Debit</label>
                                    <input type="number" name="debit_amount[]" class="form-control debit-amount" step="0.01" min="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Credit</label>
                                    <input type="number" name="credit_amount[]" class="form-control credit-amount" step="0.01" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="item_description[]" class="form-control">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-danger remove-item">×</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="addItem" class="btn btn-secondary">Add Line</button>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p>Total Debit: <span id="totalDebit">0.00</span></p>
                            </div>
                            <div class="col-md-6">
                                <p>Total Credit: <span id="totalCredit">0.00</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Entry</button>
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
            $('#entriesTable').DataTable({
                order: [[0, 'desc']]
            });

            // Add new item line
            document.getElementById('addItem').addEventListener('click', function() {
                const template = document.querySelector('.entry-item').cloneNode(true);
                template.querySelector('select').value = '';
                template.querySelectorAll('input[type="number"]').forEach(input => input.value = '');
                template.querySelector('input[type="text"]').value = '';
                document.getElementById('entryItems').appendChild(template);
            });

            // Remove item line
            document.getElementById('entryItems').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-item')) {
                    const items = document.querySelectorAll('.entry-item');
                    if (items.length > 1) {
                        e.target.closest('.entry-item').remove();
                    }
                }
            });

            // Calculate totals
            document.getElementById('entryItems').addEventListener('input', function(e) {
                if (e.target.classList.contains('debit-amount') || e.target.classList.contains('credit-amount')) {
                    let totalDebit = 0;
                    let totalCredit = 0;

                    document.querySelectorAll('.debit-amount').forEach(input => {
                        totalDebit += parseFloat(input.value || 0);
                    });

                    document.querySelectorAll('.credit-amount').forEach(input => {
                        totalCredit += parseFloat(input.value || 0);
                    });

                    document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
                    document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);
                }
            });

            // Form validation
            document.getElementById('journalEntryForm').addEventListener('submit', function(e) {
                const totalDebit = parseFloat(document.getElementById('totalDebit').textContent);
                const totalCredit = parseFloat(document.getElementById('totalCredit').textContent);

                if (totalDebit !== totalCredit) {
                    e.preventDefault();
                    alert('Total debit must equal total credit!');
                }
            });

            // Post entry
            document.querySelectorAll('.post-entry').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to post this entry?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.innerHTML = `
                            <input type="hidden" name="action" value="post">
                            <input type="hidden" name="entry_id" value="${this.dataset.entryId}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            // Void entry
            document.querySelectorAll('.void-entry').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to void this entry?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.innerHTML = `
                            <input type="hidden" name="action" value="void">
                            <input type="hidden" name="entry_id" value="${this.dataset.entryId}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
    <script src="../../js/navigation.js"></script>
</body>
</html>