<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\LoanBudgetRepository;
use App\Models\LoanBudget;
use Carbon\Carbon;

class LoanBudgetService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    public function __construct(LoanBudgetRepository $repo)
    {
        $this->repo = $repo;

        // $this->repo->setSortBy('doc_date');
        // $this->repo->setSortOrder('desc');

        // $this->repo->setRelations([]);
    }

    public function createMany(array $data)
    {
        foreach($data as $item) {
            $this->repo->getModel()->create(formatCurrency($item, ['total']));
        }
    }
}