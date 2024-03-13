<?php

namespace App\Services;

use App\Repositories\ItemRepository;
use App\Models\Item;
use App\Models\AssetType;
use App\Models\AssetCategory;
use App\Models\Unit;
use App\Traits\SaveImage;

class ItemService
{
    use SaveImage;

    /**
     * @var $itemRepo
     */
    protected $itemRepo;

    /**
     * @var $destPath
     */
    protected $destPath = 'products';

    public function __construct(ItemRepository $itemRepo)
    {
        $this->itemRepo = $itemRepo;
    }

    public function find($id)
    {
        return $this->itemRepo->getItem($id);
    }

    public function findAll($params = [])
    {
        return $this->itemRepo->getItems();
    }

    public function findById($id)
    {
        return $this->itemRepo->getItemById($id);
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

    public function store($req)
    {
        $data = [
            // 'name'          => $req['name'],
            // 'category_id'   => $req['category_id'],
            'description'   => $req['description'],
            'cost'          => $req['cost'],
            'price'         => $req['price'],
            'unit_id'       => $req['unit_id'],
            // 'status'        => $req['status'] ? 1 : 0,
            'img_url'       => $this->saveImage($req->file('img_url'), $this->destPath),
        ];

        return $this->itemRepo->store($data);
    }

    public function delete($id)
    {
        return $this->itemRepo->delete($id);
    }

    public function updateImage($id, $image)
    {
        $item = $this->itemRepo->getItem($id);

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