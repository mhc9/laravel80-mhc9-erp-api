<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\ItemRepository;
use App\Models\AssetType;
use App\Models\AssetCategory;
use App\Models\Unit;
use App\Traits\SaveImage;

class ItemService extends BaseService
{
    use SaveImage;

    /**
     * @var $repo
     */
    protected $repo;

    /**
     * @var $destPath
     */
    protected $destPath = 'products';

    public function __construct(ItemRepository $repo)
    {
        $this->repo = $repo;

        // $this->repo->setSortBy('date_in');
        // $this->repo->setSortOrder('desc');

        $this->repo->setRelations(['category','unit']);
    }

    public function initForm()
    {
        $types = AssetType::with('categories')->get();

        return [
            'types'         => $types,
            'categories'    => AssetCategory::all(),
            'units'         => Unit::all(),
        ];
    }

    public function updateImage($id, $image)
    {
        $item = $this->repo->getItem($id);

        /** Remove old file */
        if (\File::exists($this->destPath . $item->img_url)) {
            \File::delete($this->destPath . $item->img_url);
        }

        $item->img_url = $this->saveImage($image, $this->destPath);

        if (!empty($item->img_url) && $item->save()) {
            return $item;
        } else {
            return false;
        }
    }
}