<?php

namespace App\Services;

use Carbon\Carbon;
use App\Services\BaseService;
use App\Repositories\LoanContractDetailRepository;
use App\Models\LoanContractDetail;

class LoanContractDetailService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    public function __construct(LoanContractDetailRepository $repo)
    {
        $this->repo = $repo;
    }
}