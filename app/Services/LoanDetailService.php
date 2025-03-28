<?php

namespace App\Services;

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

        // $this->repo->setSortBy('doc_date');
        // $this->repo->setSortOrder('desc');

        // $this->repo->setRelations([]);
    }
}