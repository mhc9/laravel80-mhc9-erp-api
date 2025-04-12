<?php

namespace App\Services;

use Illuminate\Support\Arr;
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
            /** Swap value of id to guuid and remove id */
            $item['guuid'] = $item['id'];
            unset($item['id']);

            $this->repo->getModel()->create($item);
        }
    }

    /**
     * Update many rows function
     *
     * @param array $data
     * @param string $checkField
     * @return void
     */
    public function updateMany(array $data, string $checkField, array $additions = null): void
    {
        foreach($data as $item) {
            /** ถ้า element ของ $data ไม่มี $checkField (รายการใหม่) */
            if (!array_key_exists($checkField, $item) || empty($item[$checkField])) {
                /** Swap value of id to guuid and remove id */
                $item['guuid'] = $item['id'];
                unset($item['id']);

                $this->repo->getModel()->create(Arr::except($additions ? addMultipleInputs($item, $additions) : $item, 'id'));
            } else {
                /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property removed หรือไม่ */
                if (array_key_exists('removed', $item) && $item['removed']) {
                    $this->repo->getModel()->find($item['id'])->delete();
                }
            }
        }
    }
}