<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use App\Services\BaseService;
use App\Repositories\LoanDetailRepository;
use App\Models\LoanDetail;
use Carbon\Carbon;

class LoanDetailService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    public function __construct(LoanDetailRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Create many rows of loan_details data function
     *
     * @param array $data
     * @param Collection|null $courses
     * @return void
     */
    public function createMany(array $data, Collection $courses = null): void
    {
        foreach($data as $item) {
            $item['course_id'] = $item['expense_group'] == '1' ? $courses->firstWhere('guuid', $item['course_id'])->id : null;

            $this->repo->create(formatCurrency($item, ['total']));
        }
    }

    /**
     * Update many rows of loan_details data function
     *
     * @param array $data
     * @param string $checkField
     * @param Collection|null $courses
     * @return void
     */
    public function updateMany(array $data, string $checkField, array $additions = null, Collection $courses = null): void
    {
        foreach($data as $item) {
            $item['course_id'] = $item['expense_group'] == '1'
                                    ? ($courses->firstWhere('guuid', $item['course_id'])
                                        ? $courses->firstWhere('guuid', $item['course_id'])->id : null)
                                    : null;

            /** ถ้า element ของ $data ไม่มี $checkField (รายการใหม่) */
            if (!array_key_exists($checkField, $item) || empty($item[$checkField])) {
                $this->repo->create(
                    formatCurrency(
                        Arr::except($additions ? addMultipleInputs($item, $additions) : $item, 'id'),
                        ['total']
                    )
                );
            } else {
                /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property updated หรือไม่ (รายการที่ต้องแก้ไข) */
                if (array_key_exists('updated', $item) && $item['updated']) {
                    $this->repo->update(
                        $item['id'],
                        formatCurrency(
                            Arr::except($item, 'updated'),
                            ['total']
                        )
                    );
                }

                /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property removed หรือไม่ (รายการที่ต้องลบ) */
                if (array_key_exists('removed', $item) && $item['removed']) {
                    $this->repo->destroy($item['id']);
                }
            }
        }
    }
}