<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\ProjectCourseRepository;
use App\Models\ProjectCourse;
use Carbon\Carbon;

class ProjectCourseService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    public function __construct(ProjectCourseRepository $repo)
    {
        $this->repo = $repo;

        // $this->repo->setSortBy('doc_date');
        // $this->repo->setSortOrder('desc');

        // $this->repo->setRelations([]);
    }
}