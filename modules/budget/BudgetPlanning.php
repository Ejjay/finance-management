<?php
require_once '../../config/database.php';

class BudgetPlanning {
    private $db;
    private $budgetConn;

    public function __construct() {
        $this->db = new Database();
        $this->budgetConn = $this->db->getConnection('budget');
    }

    // Annual Budget Planning Methods
    public function createAnnualBudgetPlan($fiscalYear, $totalBudget) {
        $sql = "INSERT INTO annual_budget_plans (fiscal_year, total_budget) VALUES (?, ?)";
        $stmt = $this->budgetConn->prepare($sql);
        return $stmt->execute([$fiscalYear, $totalBudget]);
    }

    public function updateBudgetPlanStatus($planId, $status, $approvedBy = null) {
        $sql = "UPDATE annual_budget_plans SET status = ?, approved_by = ?, approved_at = CURRENT_TIMESTAMP WHERE plan_id = ?";
        $stmt = $this->budgetConn->prepare($sql);
        return $stmt->execute([$status, $approvedBy, $planId]);
    }

    public function getAnnualBudgetPlan($planId) {
        $sql = "SELECT * FROM annual_budget_plans WHERE plan_id = ?";
        $stmt = $this->budgetConn->prepare($sql);
        $stmt->execute([$planId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Department Budget Allocation Methods
    public function allocateDepartmentBudget($planId, $departmentId, $amount) {
        $sql = "INSERT INTO department_budget_allocations (plan_id, department_id, allocated_amount) VALUES (?, ?, ?)";
        $stmt = $this->budgetConn->prepare($sql);
        return $stmt->execute([$planId, $departmentId, $amount]);
    }

    public function updateAllocationStatus($allocationId, $status) {
        $sql = "UPDATE department_budget_allocations SET status = ? WHERE allocation_id = ?";
        $stmt = $this->budgetConn->prepare($sql);
        return $stmt->execute([$status, $allocationId]);
    }

    public function getDepartmentAllocations($planId) {
        $sql = "SELECT * FROM department_budget_allocations WHERE plan_id = ?";
        $stmt = $this->budgetConn->prepare($sql);
        $stmt->execute([$planId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Budget Revision Methods
    public function createBudgetRevision($planId, $allocationId, $previousAmount, $revisedAmount, $reason) {
        $sql = "INSERT INTO budget_revisions (plan_id, allocation_id, previous_amount, revised_amount, reason) 
               VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->budgetConn->prepare($sql);
        return $stmt->execute([$planId, $allocationId, $previousAmount, $revisedAmount, $reason]);
    }

    public function approveRevision($revisionId, $approvedBy) {
        $sql = "UPDATE budget_revisions SET status = 'approved', approved_by = ? WHERE revision_id = ?";
        $stmt = $this->budgetConn->prepare($sql);
        return $stmt->execute([$approvedBy, $revisionId]);
    }

    public function getRevisionHistory($planId) {
        $sql = "SELECT * FROM budget_revisions WHERE plan_id = ? ORDER BY revision_date DESC";
        $stmt = $this->budgetConn->prepare($sql);
        $stmt->execute([$planId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Multi-Year Budget Forecasting Methods
    public function createBudgetForecast($startYear, $endYear, $departmentId, $projectedAmount, $growthRate, $assumptions) {
        $sql = "INSERT INTO budget_forecasts (start_year, end_year, department_id, projected_amount, growth_rate, assumptions) 
               VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->budgetConn->prepare($sql);
        return $stmt->execute([$startYear, $endYear, $departmentId, $projectedAmount, $growthRate, $assumptions]);
    }

    public function addForecastFactor($forecastId, $factorName, $impactPercentage, $description) {
        $sql = "INSERT INTO forecast_factors (forecast_id, factor_name, impact_percentage, description) 
               VALUES (?, ?, ?, ?)";
        $stmt = $this->budgetConn->prepare($sql);
        return $stmt->execute([$forecastId, $factorName, $impactPercentage, $description]);
    }

    public function getForecastDetails($forecastId) {
        $sql = "SELECT f.*, ff.factor_name, ff.impact_percentage, ff.description 
               FROM budget_forecasts f 
               LEFT JOIN forecast_factors ff ON f.forecast_id = ff.forecast_id 
               WHERE f.forecast_id = ?";
        $stmt = $this->budgetConn->prepare($sql);
        $stmt->execute([$forecastId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}