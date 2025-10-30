<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Services\BaseService;
use App\Repositories\LoanRefundRepository;
use App\Models\Loan;
use App\Models\LoanContract;
use App\Models\LoanContractDetail;
use App\Models\LoanRefund;
use App\Models\LoanRefundDetail;
use App\Models\LoanRefundBudget;
use App\Models\Expense;
use App\Models\Department;
use App\Models\BudgetYear;
use Carbon\Carbon;

class LoanRefundService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    public function __construct(LoanRefundRepository $repo)
    {
        $this->repo = $repo;

        $this->repo->setSortBy('doc_date');
        $this->repo->setSortOrder('desc');

        $this->repo->setRelations([
            'details','details.contractDetail.expense','details.contractDetail.loanDetail',
            'contract','contract.details','contract.details.expense','contract.details.loanDetail',
            'contract.loan','contract.loan.budgets','contract.loan.budgets.budget','contract.loan.budgets.budget.type',
            'contract.loan.budgets.budget.activity.project','contract.loan.budgets.budget.activity.project.plan',
            'contract.loan.courses','contract.loan.courses.place','contract.loan.courses.place.changwat',
            'contract.loan.employee','contract.loan.employee.prefix','contract.loan.employee.position','contract.loan.employee.level',
            'budgets','budgets.budget','budgets.budget.activity','budgets.budget.type','budgets.budget.activity.project',
            'budgets.budget.activity.project.plan','contract.loan.division','contract.loan.department'
        ]);
    }

    /**
     * Get LoanRefund data with condition function
     *
     * @param array $params
     * @param boolean $all
     * @param integer $perPage
     * @return LengthAwarePaginator | Collection
     */
    public function search(array $params, $all = false, $perPage = 10): LengthAwarePaginator | Collection
    {
        $collections = $this->repo->getModelWithRelations()
                            ->when((!auth()->user()->isAdmin() && !auth()->user()->isFinancial()), function($query) {
                                $query->where('employee_id', auth()->user()->employee_id);
                            })
                            ->when(!empty($params['type']), function($query) use ($params) {
                                $query->where('refund_type_id', $params['type']);
                            })
                            ->when(!empty($params['year']), function($query) use ($params) {
                                $query->where('year', $params['year']);
                            })
                            ->when(!empty($params['status']), function($query) use ($params) {
                                $query->where('status', $params['status']);
                            })
                            ->orderBy('doc_date', 'desc');

        return $all ?  $collections->get() : $collections->paginate($perPage);
    }

    /**
     * Get data for initialize form inputs function
     *
     * @return array
     */
    public function getFormData(): array
    {
        $statuses = [
            ['id' => 'N', 'name' => 'ยังไม่เคลียร์'],
            ['id' => 'Y', 'name' => 'เคลียร์แล้ว'],
        ];

        return [
            'statuses'   => $statuses,
        ];
    }

    /**
     * Get latest bill no function
     *
     * @return string
     */
    public function getLatestBillNo()
    {
        $currentYear = BudgetYear::where('actived', '1')->first();

        $latestNo = $this->repo->getModel()
                        ->select('bill_no')
                        ->orderBy('bill_no', 'desc')
                        ->where('year', $currentYear->year)
                        ->first();

        return [
            $latestNo ? $latestNo->bill_no : '0/' . substr(Carbon::parse($currentYear->year . '-10-01')->format('Y') + 543, -2)
        ];
    }

    /**
     * Create LoanRefund data function
     *
     * @param array $input
     * @return LoanRefund
     */
    public function create(array $input): LoanRefund
    {
        return $this->repo->create(formatCurrency($input, ['budget_total','item_total','order_total','net_total','balance']));
    }
}