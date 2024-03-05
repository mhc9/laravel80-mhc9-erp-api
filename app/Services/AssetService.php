<?php

namespace App\Services;

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

class AssetService
{
    use SaveImage;

    /**
     * @var $assetRepo
     */
    protected $assetRepo;

    public function __construct(AssetRepository $assetRepo)
    {
        $this->assetRepo = $assetRepo;
    }

    public function find($id)
    {
        return $this->assetRepo->getAsset($id);
    }

    public function findAll($params = [])
    {
        return $this->assetRepo->getAssets();
    }

    public function findById($id)
    {
        return $this->assetRepo->getAssetById($id);
    }

    public function initForm()
    {
        $statuses = [
            ['id' => 1, 'name'  => 'ใช้งานอยู่'],
            ['id' => 2, 'name'  => 'สำรอง'],
            ['id' => 3, 'name'  => 'ถูกยืม'],
            ['id' => 9, 'name'  => 'รอจำหน่าย'],
            ['id' => 99, 'name'  => 'จำหน่าย	'],
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

    public function delete($id)
    {
        return $this->assertRepo->delete($id);
    }

    public function updateImage($id, $image)
    {
        $asset = $this->assetRepo->getAsset($id);
        $destPath = 'uploads/assets/';

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