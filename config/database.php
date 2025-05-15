<?php
class Database {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $connections = [];
    
    private $databases = [
        'finance' => 'finance_db',
        'collection' => 'collection_db',
        'payroll' => 'payroll_db',
        'payable' => 'payable_db',
        'budget' => 'budget_db',
        'receivable' => 'receivable_db',
        'ledger' => 'ledger_db',
        'hr' => 'hr_db'
    ];

    public function __construct() {
        foreach ($this->databases as $key => $dbName) {
            try {
                $this->connections[$key] = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $dbName,
                    $this->username,
                    $this->password
                );
                $this->connections[$key]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e) {
                die("Connection to {$dbName} failed: " . $e->getMessage());
            }
        }
    }

    public function getConnection($database = 'finance') {
        if (!isset($this->connections[$database])) {
            throw new Exception("Database connection '{$database}' not found.");
        }
        return $this->connections[$database];
    }
}
?>