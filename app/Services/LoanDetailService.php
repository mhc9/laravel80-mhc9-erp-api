<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
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

            $this->repo->getModel()->create(formatCurrency($item, ['total']));
        }
    }
}