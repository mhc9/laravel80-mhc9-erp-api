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

    /**
     * Create many rows of loan_budgets data function
     *
     * @param array $data
     * @return void
     */
    public function createMany(array $data): void
    {
        foreach($data as $item) {
            $this->repo->getModel()->create(formatCurrency($item, ['total']));
        }
    }

    /**
     * Update many rows of loan_budgets data function
     *
     * @param array $data
     * @param Collection|null $courses
     * @param string $checkField
     * @return void
     */
    public function updateMany(array $data, string $checkField): void
    {
        foreach($data as $item) {
            /** ถ้า element ของ $data ไม่มี $checkField (รายการใหม่) */
            if (!array_key_exists($checkField, $item)) {
                $this->repo->getModel()->create(formatCurrency($item, ['total']));
            } else {
                /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property removed หรือไม่ */
                if (array_key_exists('removed', $item) && $item['removed']) {
                    $this->repo->destroy($item['id']);
                }
            }
        }
    }
}