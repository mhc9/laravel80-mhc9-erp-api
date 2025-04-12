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

    public function createMany(array $data)
    {
        foreach($data as $item) {
            $item['guuid'] = $item['id'];
            unset($item['id']);

            $this->repo->create($item);
        }
    }
}