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

    public function search(array $params, $all = false, $perPage = 10)
    {
        $collections = $this->repo
                            ->getModelWithRelations()
                            ->when(!empty($params['category']), function($q) use ($params) {
                                $q->where('category_id', $params['category']);
                            })
                            ->when(!empty($params['name']), function($q) use ($params) {
                                $q->where('name', 'like', '%'.$params['name'].'%');
                            })
                            ->when(!empty($params['status']), function($q) use ($params) {
                                $q->where('status', $params['status']);
                            });

        return $all ?  $collections->get() : $collections->paginate($perPage);
    }

    public function getFormData()
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
        $item = $this->repo->getById($id);

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