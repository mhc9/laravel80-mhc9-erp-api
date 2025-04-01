<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\AssetRepository;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\AssetCategory;
use App\Models\AssetGroup;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\Budget;
use App\Models\ObtainingType;
use App\Models\Employee;
use App\Models\Room;
use App\Traits\SaveImage;

class AssetService extends BaseService
{
    use SaveImage;

    /**
     * @var $repo
     */
    protected $repo;

    public function __construct(AssetRepository $repo)
    {
        $this->repo = $repo;

        $this->repo->setSortBy('date_in');
        // $this->repo->setSortOrder('desc');

        $this->repo->setRelations([
            'group','group.category','brand','budget','obtaining','unit','room',
            'currentOwner','currentOwner.owner','currentOwner.owner.prefix'
        ]);
    }

    public function search(array $params, $all = false, $perPage = 10)
    {
        $collections;
        $conditions = [];
        if (!empty($params['category'])) {
            array_push($conditions, ['asset_category_id', '=', $params['category']]);
        }

        //             ->when(!empty($group), function($q) use ($group) {
        //                 $q->where('asset_group_id', $group);
        //             })

        //             ->when(!empty($no), function($q) use ($no) {
        //                 $q->where('asset_no', 'like', '%'.$no.'%');
        //             })

        if (!empty($params['name'])) {
            array_push($conditions, ['name', 'like', '%'.$params['name'].'%']);
        }

        //             ->when(!empty($status), function($q) use ($status) {
        //                 $q->where('status', $status);
        //             })

        $collections = $this->repo->getModelWithRelations()
                                ->where($conditions)
                                ->when(!empty($params['owner']), function($query) use ($params) {
                                    $query->whereHas('currentOwner', function($subquery) use ($params) {
                                        $subquery->where('owner_id', $params['owner']);
                                    });
                                });

        return $all ?  $collections->get() : $collections->paginate($perPage);
    }

    public function initForm()
    {
        $statuses = [
            ['id' => 1, 'name'  => 'ใช้งานอยู่'],
            ['id' => 2, 'name'  => 'สำรอง'],
            ['id' => 3, 'name'  => 'ถูกยืม'],
            ['id' => 9, 'name'  => 'รอจำหน่าย'],
            ['id' => 99, 'name' => 'จำหน่าย	'],
        ];

        return [
            'types'         => AssetType::all(),
            'categories'    => AssetCategory::all(),
            'groups'        => AssetGroup::all(),
            'units'         => Unit::all(),
            'brands'        => Brand::all(),
            'budgets'       => Budget::all(),
            'obtainingTypes' => ObtainingType::all(),
            'employees'     => Employee::whereIn('status', [1,2])->get(),
            'rooms'         => Room::where('status', 1)->get(),
            'statuses'      => $statuses
        ];
    }

    public function updateImage($id, $image)
    {
        $destPath = 'assets';
        $asset = $this->repo->findOne($id);

        /** Remove old uploaded file */
        if (\File::exists($destPath . $asset->img_url)) {
            \File::delete($destPath . $asset->img_url);
        }

        $asset->img_url = $this->saveImage($image, $destPath);

        if (!empty($asset->img_url) && $asset->save()) {
            return $asset;
        } else {
            return false;
        }
    }
}