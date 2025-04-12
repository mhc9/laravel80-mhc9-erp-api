<?php

namespace App\Services;

use Illuminate\Support\Arr;
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
            $this->repo->create(formatCurrency($item, ['total']));
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
    public function updateMany(array $data, string $checkField, array $additions = null): void
    {
        foreach($data as $item) {
            /** ถ้า element ของ $data ไม่มี $checkField (รายการใหม่) */
            if (!array_key_exists($checkField, $item) || empty($item[$checkField])) {
                $this->repo->create(
                    formatCurrency(
                        Arr::except($additions ? addMultipleInputs($item, $additions) : $item, 'id'),
                        ['total']
                    )
                );
            } else {
                /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property removed หรือไม่ */
                if (array_key_exists('removed', $item) && $item['removed']) {
                    $this->repo->destroy($item['id']);
                }
            }
        }
    }
}