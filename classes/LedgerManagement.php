<?php
require_once('Database.php');

class LedgerManagement {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection('ledger');
    }

    public function createJournalEntry($entryData, $items) {
        try {
            $this->conn->beginTransaction();

            // Insert journal entry header
            $sql = "INSERT INTO journal_entries (entry_date, description, reference_number, created_by, status) 
                    VALUES (:entry_date, :description, :reference_number, :created_by, 'draft')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':entry_date' => $entryData['entry_date'],
                ':description' => $entryData['description'],
                ':reference_number' => $entryData['reference_number'],
                ':created_by' => $entryData['created_by']
            ]);
            
            $entryId = $this->conn->lastInsertId();

            // Insert journal entry items
            $sql = "INSERT INTO journal_entry_items (entry_id, account_id, debit_amount, credit_amount, description) 
                    VALUES (:entry_id, :account_id, :debit_amount, :credit_amount, :description)";
            $stmt = $this->conn->prepare($sql);

            foreach ($items as $item) {
                $stmt->execute([
                    ':entry_id' => $entryId,
                    ':account_id' => $item['account_id'],
                    ':debit_amount' => $item['debit_amount'],
                    ':credit_amount' => $item['credit_amount'],
                    ':description' => $item['description']
                ]);
            }

            $this->conn->commit();
            return $entryId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw new Exception("Failed to create journal entry: " . $e->getMessage());
        }
    }

    public function postJournalEntry($entryId) {
        try {
            $this->conn->beginTransaction();

            // Verify entry exists and is in draft status
            $sql = "SELECT status FROM journal_entries WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$entryId]);
            $entry = $stmt->fetch();

            if (!$entry) {
                throw new Exception("Journal entry not found");
            }

            if ($entry['status'] !== 'draft') {
                throw new Exception("Journal entry is not in draft status");
            }

            // Update entry status to posted
            $sql = "UPDATE journal_entries SET status = 'posted', posted_date = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$entryId]);

            // Update account balances
            $sql = "SELECT account_id, debit_amount, credit_amount FROM journal_entry_items WHERE entry_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$entryId]);
            $items = $stmt->fetchAll();

            foreach ($items as $item) {
                $sql = "UPDATE accounts 
                        SET current_balance = current_balance + (:debit_amount - :credit_amount) 
                        WHERE id = :account_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    ':account_id' => $item['account_id'],
                    ':debit_amount' => $item['debit_amount'],
                    ':credit_amount' => $item['credit_amount']
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw new Exception("Failed to post journal entry: " . $e->getMessage());
        }
    }

    public function getReconciliationRecords($accountId = null, $status = null) {
        try {
            $params = [];
            $conditions = [];
            
            $sql = "SELECT r.*, a.account_name 
                    FROM reconciliation_records r 
                    JOIN chart_of_accounts a ON r.account_id = a.account_id";

            if ($accountId) {
                $conditions[] = "r.account_id = :account_id";
                $params[':account_id'] = $accountId;
            }

            if ($status) {
                $conditions[] = "r.status = :status";
                $params[':status'] = $status;
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY r.statement_date DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch reconciliation items for each record
            foreach ($records as &$record) {
                $sql = "SELECT * FROM reconciliation_items 
                        WHERE record_id = :record_id 
                        ORDER BY transaction_date";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':record_id' => $record['record_id']]);
                $record['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $records;
        } catch (Exception $e) {
            throw new Exception("Failed to fetch reconciliation records: " . $e->getMessage());
        }
    }

    public function getJournalEntry($entryId) {
        $sql = "SELECT e.*, u.username as created_by_name 
                FROM journal_entries e 
                LEFT JOIN users u ON e.created_by = u.id 
                WHERE e.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$entryId]);
        $entry = $stmt->fetch();

        if ($entry) {
            $sql = "SELECT i.*, a.account_name 
                    FROM journal_entry_items i 
                    LEFT JOIN accounts a ON i.account_id = a.id 
                    WHERE i.entry_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$entryId]);
            $entry['items'] = $stmt->fetchAll();
        }

        return $entry;
    }

    public function listJournalEntries($filters = []) {
        $sql = "SELECT e.*, u.username as created_by_name 
                FROM journal_entries e 
                LEFT JOIN users u ON e.created_by = u.id 
                WHERE 1=1";
        $params = [];

        if (isset($filters['status'])) {
            $sql .= " AND e.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['date_from'])) {
            $sql .= " AND e.entry_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (isset($filters['date_to'])) {
            $sql .= " AND e.entry_date <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY e.entry_date DESC, e.entry_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getChartOfAccounts() {
        $sql = "SELECT * FROM accounts ORDER BY account_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getJournalEntries($filters = []) {
        return $this->listJournalEntries($filters);
    }

    public function getFundTransfers($filters = []) {
        $sql = "SELECT t.*, 
                       fa.account_name as from_account_name,
                       ta.account_name as to_account_name,
                       u.username as created_by_name
                FROM fund_transfers t
                LEFT JOIN accounts fa ON t.from_account_id = fa.id
                LEFT JOIN accounts ta ON t.to_account_id = ta.id
                LEFT JOIN users u ON t.created_by = u.id
                WHERE 1=1";
        $params = [];

        if (isset($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['date_from'])) {
            $sql .= " AND t.transfer_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (isset($filters['date_to'])) {
            $sql .= " AND t.transfer_date <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY t.transfer_date DESC, t.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}