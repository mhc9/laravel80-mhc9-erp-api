<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\LoanRepository;
use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\LoanBudget;
use App\Models\LoanContract;
use App\Models\ProjectCourse;
use App\Models\Expense;
use App\Models\Department;
use App\Models\Budget;
use App\Interfaces\INotify;
use Carbon\Carbon;

class LoanService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    /**
     * @var $destPath
     */
    protected $destPath = 'products';

    public function __construct(LoanRepository $repo)
    {
        $this->repo = $repo;

        $this->repo->setSortBy('doc_date');
        // $this->repo->setSortOrder('desc');
        $this->repo->setRelations([
            'details','details.expense','department','division',
            'employee','employee.prefix','employee.position','employee.level',
            'budgets','budgets.budget','budgets.budget.activity','budgets.budget.type',
            'lbudgets.budget.activity.project','budgets.budget.activity.project.plan',
            'courses','courses.place','courses.place.changwat'
        ]);
    }

    public function find($id)
    {
        return $this->repo->getPlace($id);
    }

    public function findAll($params = [])
    {
        return $this->repo->getPlaces($params)->get();
    }
}