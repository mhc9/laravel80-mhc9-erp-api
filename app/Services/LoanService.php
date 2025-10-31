<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
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
use Carbon\Carbon;

class LoanService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    public function __construct(LoanRepository $repo)
    {
        $this->repo = $repo;

        $this->repo->setSortBy('doc_date');
        $this->repo->setSortOrder('desc');

        $this->repo->setRelations([
            'details','details.expense','department','division',
            'employee','employee.prefix','employee.position','employee.level',
            'budgets','budgets.budget','budgets.budget.activity','budgets.budget.type',
            'budgets.budget.activity.project','budgets.budget.activity.project.plan',
            'courses','courses.place','courses.place.changwat','contract'
        ]);
    }

    /**
     * Get Loan data with condition function
     *
     * @param array $params
     * @param boolean $all
     * @param integer $perPage
     * @return LengthAwarePaginator | Collection
     */
    public function search(array $params, $all = false, $perPage = 10): LengthAwarePaginator | Collection
    {
        $collections = $this->repo->getModelWithRelations()
                            ->when((!auth()->user()->isAdmin() && !auth()->user()->isFinancial()), function($qurey) {
                                $qurey->where('employee_id', auth()->user()->employee_id);
                            })
                            ->when(!empty($params['year']), function($qurey) use ($params) {
                                $qurey->where('year', $params['year']);
                            })
                            ->when(!empty($params['status']), function($qurey) use ($params) {
                                $qurey->where('status', $params['status']);
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
        $loanTypes = [
            ['id' => 1, 'name' => 'ยืมเงินโครงการ'],
            ['id' => 2, 'name' => 'ยืมเงินเดินทางไปราชการ'],
            ['id' => 3, 'name' => 'ยืมเงินค่าลงทะเบียน'],
        ];

        $moneyTypes = [
            ['id' => 1, 'name' => 'เงินทดลองราชการ'],
            ['id' => 2, 'name' => 'เงินยืมนอกงบประมาณ'],
            ['id' => 3, 'name' => 'เงินยืมราชการ'],
        ];

        $statuses = [
            ['id' => 1, 'name' => 'รอดำเนินการ'],
            ['id' => 2, 'name' => 'ส่งสัญญาแล้ว'],
            ['id' => 3, 'name' => 'อนุมัติแล้ว'],
            ['id' => 4, 'name' => 'เงินเข้าแล้ว'],
            ['id' => 5, 'name' => 'เคลียร์แล้ว'],
            ['id' => 9, 'name' => 'ยกเลิก'],
        ];

        return [
            'departments'   => Department::with('divisions')->get(),
            'expenses'      => Expense::all(),
            'budgets'       => Budget::with('activity','type')->get(),
            'loanTypes'     => $loanTypes,
            'moneyTypes'    => $moneyTypes,
            'statuses'      => $statuses
        ];
    }

    /**
     * Create Loan data function
     *
     * @param array $input
     * @return Loan
     */
    public function create(array $input): Loan
    {
        return $this->repo->create(formatCurrency($input, ['budget_total','item_total','order_total','net_total']));
    }
}