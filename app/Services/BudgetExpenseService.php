<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\BaseService;
use App\Repositories\BudgetExpenseRepository;
use App\Models\Employee;
use App\Models\Prefix;
use App\Models\Position;
use App\Models\Level;
use App\Models\Department;
use App\Models\Division;
use App\Models\Member;
use App\Models\BudgetExpense;
use App\Models\BudgetExpenseDetail;


class BudgetExpenseService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    public function __construct(BudgetExpenseRepository $repo)
    {
        $this->repo = $repo;

        // $this->repo->setSortBy('doc_date');
        // $this->repo->setSortOrder('desc');

        $this->repo->setRelations([
            'budget','budget.type','budget.activity','budget.activity.project','budget.activity.project.plan',
            'expenseType','project','details','details.supplier'
        ]);
    }

    public function search(array $params, $all = false, $perPage = 10): LengthAwarePaginator | Collection
    {
        $collections = $this->repo->getModelWithRelations()
                            ->when(!empty($params['type']), function($q) use ($params) {
                                $q->where('expense_type_id', $params['type']);
                            })
                            ->when(!empty($params['plan']), function($q) use ($params) {
                                $q->whereRelation('budget.activity.project', 'plan_id', $params['plan']);
                            })
                            ->when(!empty($params['status']), function($q) use ($params) {
                                $q->where('status', $params['status']);
                            });

        return $all ?  $collections->get() : $collections->paginate($perPage);
    }

    public function getFormData()
    {
        return [
            
        ];
    }

    public function storeDetails($id, array $data)
    {
        if (!$budgetExpense = $this->repo->findOne($id)) {
            return null;
        }

        return $budgetExpense->details()->create($data);
    }

    public function updateDetails($id, $detailId, array $data)
    {
        if (!$budgetExpense = $this->repo->findOne($id)) {
            return null;
        }

        $detail = $budgetExpense->details()->find($detailId);
        if (!$detail) {
            return null;
        }

        $detail->update($data);
        return $detail;
    }
}