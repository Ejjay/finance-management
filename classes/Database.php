<?php
class Database {
    private $connections = [];
    private $config = [
        'ledger' => [
            'host' => 'localhost',
            'dbname' => 'finance_system',
            'username' => 'root',
            'password' => ''
        ],
        'budget' => [
            'host' => 'localhost',
            'dbname' => 'finance_system',
            'username' => 'root',
            'password' => ''
        ],
        'payroll' => [
            'host' => 'localhost',
            'dbname' => 'finance_system',
            'username' => 'root',
            'password' => ''
        ],
        'collections' => [
            'host' => 'localhost',
            'dbname' => 'finance_system',
            'username' => 'root',
            'password' => ''
        ]
    ];

    public function getConnection($module) {
        if (!isset($this->connections[$module])) {
            if (!isset($this->config[$module])) {
                throw new Exception("Unknown module: {$module}");
            }

            $config = $this->config[$module];
            try {
                $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
                $this->connections[$module] = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                throw new Exception("Connection failed for module {$module}: " . $e->getMessage());
            }
        }

        return $this->connections[$module];
    }
}