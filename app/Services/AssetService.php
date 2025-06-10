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
        $collections = $this->repo->getModelWithRelations()
                            ->when(!empty($params['category']), function($q) use ($params) {
                                $q->where('asset_category_id', $params['category']);
                            })
                            ->when(!empty($params['group']), function($q) use ($params) {
                                $q->where('asset_group_id', $params['group']);
                            })
                            ->when(!empty($params['no']), function($q) use ($params) {
                                $q->where('asset_no', 'like', '%'.$params['no'].'%');
                            })
                            ->when(!empty($params['name']), function($q) use ($params) {
                                $q->where('name', 'like', '%'.$params['name'].'%');
                            })
                            ->when(!empty($params['status']), function($q) use ($params) {
                                $q->where('status', $params['status']);
                            })
                            ->when(!empty($params['owner']), function($q) use ($params) {
                                $q->whereHas('currentOwner', function($subquery) use ($params) {
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